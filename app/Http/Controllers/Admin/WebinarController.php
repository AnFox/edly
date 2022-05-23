<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\RoomRepository;
use App\Contracts\Repositories\ScriptRepository;
use App\Contracts\Repositories\UserRepository;
use App\Contracts\Repositories\WebinarRepository;
use App\Events\NewRecordableAction;
use App\Events\WebinarLayoutChange;
use App\Events\WebinarTabChange;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WebinarCreateRequest;
use App\Http\Requests\Admin\WebinarLayoutRequest;
use App\Http\Requests\Admin\WebinarStartRequest;
use App\Http\Requests\Admin\WebinarTabRequest;
use App\Http\Requests\Admin\WebinarUpdateRequest;
use App\Http\Resources\Admin\ExportUserEmailPhoneResource;
use App\Http\Resources\Admin\ExportUserEmailResource;
use App\Http\Resources\Admin\ExportUserPhoneResource;
use App\Http\Resources\Admin\WebinarResource;
use App\Models\Script;
use App\Models\Webinar;
use App\Services\UserService;
use Bouncer;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Class WebinarController
 * @package App\Http\Controllers
 */
class WebinarController extends Controller
{
    /**
     * @var WebinarRepository
     */
    private $webinarRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var UserService
     */
    private $userService;
    /**
     * @var RoomRepository
     */
    private $roomRepository;
    /**
     * @var ScriptRepository
     */
    private $scriptRepository;

    /**
     * WebinarController constructor.
     * @param WebinarRepository $webinarRepository
     * @param RoomRepository $roomRepository
     * @param UserRepository $userRepository
     * @param UserService $userService
     * @param ScriptRepository $scriptRepository
     */
    public function __construct(
        WebinarRepository $webinarRepository,
        RoomRepository $roomRepository,
        UserRepository $userRepository,
        UserService $userService,
        ScriptRepository $scriptRepository)
    {
        $this->webinarRepository = $webinarRepository;
        $this->userRepository = $userRepository;
        $this->userService = $userService;
        $this->roomRepository = $roomRepository;
        $this->scriptRepository = $scriptRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param int $roomId
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     */
    public function index(int $roomId): AnonymousResourceCollection
    {
        $room = $this->roomRepository->find($roomId)->getModel();
        $this->authorize('view-room', $room);

        $webinars = $this->webinarRepository
            ->findByRoomId($roomId)
            ->paginate(null, 'id', 'desc');

        return WebinarResource::collection($webinars);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param int $roomId
     * @param WebinarCreateRequest $request
     * @return WebinarResource
     * @throws AuthorizationException
     */
    public function store(int $roomId, WebinarCreateRequest $request): WebinarResource
    {
        $user = $request->user();
        // @todo: refactor this
        if (!$this->userRepository->setModel($user)->getFirstLinkedAccount()) {
            // User has no account so we need to create account for him
            $this->userService->assignRoleToUser('owner', $user);
        }

        $this->authorize('create', $this->webinarRepository->getClass());

        $attributes = $request->validated();
        $attributes['room_id'] = $roomId;
        $webinar = $this->webinarRepository->create($attributes);

        // @todo: refactor this
        // We assume that user becomes owner on webinar create
        $this->userService->assignRoleToUser('owner', $user);

        return new WebinarResource($webinar);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return WebinarResource
     * @throws AuthorizationException
     */
    public function show(int $id): WebinarResource
    {
        $webinar = $this->webinarRepository->find($id)->getModel();
        $this->authorize('view-webinar', $webinar);

        return new WebinarResource($webinar);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param WebinarUpdateRequest $request
     * @param int $id
     * @return WebinarResource
     * @throws AuthorizationException
     */
    public function update(WebinarUpdateRequest $request, $id): WebinarResource
    {
        $webinar = $this->webinarRepository->find($id)->getModel();
        $this->authorize('update', $webinar->room);
        $this->webinarRepository->fill($request->validated());
        $this->webinarRepository->save();
        $webinar = $this->webinarRepository->getModel();

        return new WebinarResource($webinar);
    }

    /**
     * @param int $id
     * @param WebinarStartRequest $request
     * @return WebinarResource
     * @throws AuthorizationException
     */
    public function start(int $id, WebinarStartRequest $request): WebinarResource
    {
        $webinar = $this->webinarRepository->find($id)->getModel();
        $this->authorize('start', $webinar->room);
        $this->webinarRepository->setStarted();

        if ($webinar->is_recordable || $request->boolean('is_recordable')) {
            $this->webinarRepository->setRecordable();
            // Delete previous record
            $this->scriptRepository->deleteRoomCommands($webinar->room->id);
            event(new NewRecordableAction($this->webinarRepository->getModel(), Script::ACTION_START_RECORD));
        }

        return new WebinarResource($webinar, 0, false);
    }

    /**
     * @param int $id
     * @return WebinarResource
     * @throws AuthorizationException
     */
    public function finish(int $id): WebinarResource
    {
        $webinar = $this->webinarRepository->find($id)->getModel();
        $this->authorize('stop', $webinar->room);
        $this->webinarRepository->setFinished();
        $this->webinarRepository->setAllVisitorsOffline();
        event(new NewRecordableAction($webinar, Script::ACTION_STOP_RECORD));

        return new WebinarResource($webinar, 0, false);
    }

    /**
     * @param int $id
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     */
    public function exportVisitorsEmailToCSV(int $id)
    {
        $webinar = $this->webinarRepository->find($id)->getModel();
        $this->authorize('update', $webinar->room);
        $visitors = $this->webinarRepository->getActiveVisitors();

        return ExportUserEmailResource::collection($visitors);
    }

    /**
     * @param int $id
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     */
    public function exportVisitorsPhoneToCSV(int $id)
    {
        $webinar = $this->webinarRepository->find($id)->getModel();
        $this->authorize('update', $webinar->room);
        $visitors = $this->webinarRepository->getActiveVisitors()->filter(function ($visitor) {
            return $visitor->phone;
        });

        return ExportUserPhoneResource::collection($visitors);
    }

    public function exportVisitorsEmailAndPhoneToCSV(int $id)
    {
        $webinar = $this->webinarRepository->find($id)->getModel();
        $this->authorize('update', $webinar->room);
        $visitors = $this->webinarRepository->getActiveVisitors();

        return ExportUserEmailPhoneResource::collection($visitors);
    }

    /**
     * @param int $webinarId
     * @param WebinarLayoutRequest $request
     * @throws AuthorizationException
     */
    public function layout(int $webinarId, WebinarLayoutRequest $request): void
    {
        /** @var Webinar $webinar */
        $webinar = $this->webinarRepository->find($webinarId)->getModel();
        $this->authorize('update', $webinar->room);

        $layout = $request->get('layout');
        $this->webinarRepository->setLayout($layout);

        event(new WebinarLayoutChange($webinar, $layout));
        event(new NewRecordableAction($webinar, Script::ACTION_WEBINAR_LAYOUT, compact('layout')));
    }

    /**
     * @param int $webinarId
     * @param WebinarTabRequest $request
     * @throws AuthorizationException
     */
    public function tab(int $webinarId, WebinarTabRequest $request): void
    {
        /** @var Webinar $webinar */
        $webinar = $this->webinarRepository->find($webinarId)->getModel();
        $this->authorize('update', $webinar->room);

        $tab = $request->get('tab');
        $this->webinarRepository->setTab($tab);

        event(new WebinarTabChange($webinar, $tab));
        event(new NewRecordableAction($webinar, Script::ACTION_WEBINAR_TAB, compact('tab')));
    }
}
