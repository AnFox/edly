<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\RoomRepository;
use App\Contracts\Repositories\ScriptRepository;
use App\Contracts\Repositories\UserRepository;
use App\Contracts\Repositories\WebinarRepository;
use App\Contracts\Services\ScriptImportService;
use App\Events\RoomUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoomCreateRequest;
use App\Http\Requests\Admin\RoomDuplicateRequest;
use App\Http\Requests\Admin\RoomImportScriptRequest;
use App\Http\Requests\Admin\RoomOtherSettingsRequest;
use App\Http\Requests\Admin\RoomScriptCommandRequest;
use App\Http\Requests\Admin\RoomSetCoverRequest;
use App\Http\Requests\Admin\RoomUpdateRequest;
use App\Http\Requests\Admin\RoomUploadPresentationRequest;
use App\Http\Resources\Admin\ConversionResource;
use App\Http\Resources\Admin\ExportUserEmailPhoneResource;
use App\Http\Resources\Admin\ExportUserEmailResource;
use App\Http\Resources\Admin\ExportUserPhoneResource;
use App\Http\Resources\Admin\RoomResource;
use App\Http\Resources\Admin\ScriptCommandResource;
use App\Jobs\ProcessRoomPdf;
use App\Models\Conversion;
use App\Models\Room;
use App\Models\Script;
use App\Services\UserService;
use Bouncer;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

/**
 * Class RoomController
 * @package App\Http\Controllers
 */
class RoomController extends Controller
{
    /**
     * @var RoomRepository
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
     * @var ScriptImportService
     */
    private $scriptImportService;
    /**
     * @var ScriptRepository
     */
    private $scriptRepository;

    /**
     * RoomController constructor.
     * @param RoomRepository $roomRepository
     * @param WebinarRepository $webinarRepository
     * @param UserRepository $userRepository
     * @param UserService $userService
     * @param ScriptImportService $scriptImportService
     * @param ScriptRepository $scriptRepository
     */
    public function __construct(
        RoomRepository $roomRepository,
        WebinarRepository $webinarRepository,
        UserRepository $userRepository,
        UserService $userService,
        ScriptImportService $scriptImportService,
        ScriptRepository $scriptRepository
    )
    {
        $this->roomRepository = $roomRepository;
        $this->webinarRepository = $webinarRepository;
        $this->userRepository = $userRepository;
        $this->userService = $userService;
        $this->scriptImportService = $scriptImportService;
        $this->scriptRepository = $scriptRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('view-rooms', $this->roomRepository->getClass());
        $rooms = $this->roomRepository
            ->findByAuthor(request()->user()->id)
            ->paginate(null, 'id', 'desc');

        return RoomResource::collection($rooms);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param RoomCreateRequest $request
     * @return RoomResource
     * @throws AuthorizationException
     */
    public function store(RoomCreateRequest $request): RoomResource
    {
        $user = $request->user();
        if (!$this->userRepository->setModel($user)->getFirstLinkedAccount()) {
            // User has no account so we need to create account for him
            $this->userService->assignRoleToUser('owner', $user);
        }

        $this->authorize('create', $this->roomRepository->getClass());

        $attributes = $request->validated();
        $attributes['user_id'] = $user->id;
        $attributes['is_published'] = $request->get('is_published', true);
        $room = $this->roomRepository->create($attributes);

        // We assume that user becomes owner on webinar create
        $this->userService->assignRoleToUser('owner', $user);

        return new RoomResource($room);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return RoomResource
     * @throws AuthorizationException
     */
    public function show(int $id): RoomResource
    {
        $room = $this->roomRepository->find($id)->getModel();
        $this->authorize('view-room', $room);

        return new RoomResource($room);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param RoomUpdateRequest $request
     * @param int $id
     * @return RoomResource
     * @throws AuthorizationException
     */
    public function update(RoomUpdateRequest $request, $id): RoomResource
    {
        $room = $this->roomRepository->find($id)->getModel();
        $this->authorize('update', $room);
        $this->roomRepository->fill($request->validated());
        $this->roomRepository->save();
        $room = $this->roomRepository->getModel();

        return new RoomResource($room);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return void
     * @throws AuthorizationException
     */
    public function destroy($id)
    {
        $room = $this->roomRepository->find($id)->getModel();
        $this->authorize('delete', $room);
        $this->roomRepository->delete();
    }

    /**
     * @return string
     */
    public function generateSlug(): string
    {
        $slug = Str::random();

        return $slug;
    }

    /**
     * Duplicate the specified resource in storage.
     *
     * @param RoomDuplicateRequest $request
     * @param int $id
     * @return RoomResource
     * @throws AuthorizationException
     */
    public function duplicate(RoomDuplicateRequest $request, int $id): RoomResource
    {
        $source = $this->roomRepository->find($id)->getModel();
        $this->authorize('duplicate', $source);

        $room = $this->roomRepository->duplicate($source, $request);

        return new RoomResource($room);
    }

    public function setOtherSettings(RoomOtherSettingsRequest $request, int $id)
    {
        /** @var Room $room */
        $room = $this->roomRepository->find($id)->getModel();
        $this->authorize('update', $room);

        $waitingText = $request->get('waitingText');
        $this->roomRepository->setWaitingText($waitingText);

        return new RoomResource($room);
    }

    public function setCover(RoomSetCoverRequest $request, int $id)
    {
        /** @var Room $room */
        $room = $this->roomRepository->find($id)->getModel();
        $this->authorize('update', $room);

        $room->clearMediaCollection(Room::MEDIA_COLLECTION_THUMBNAIL);

        $file = $request->file('cover');
        $room->addMedia($file)
            ->usingFileName($file->getClientOriginalName())
            ->toMediaCollection(Room::MEDIA_COLLECTION_THUMBNAIL);

        event(new RoomUpdated($room));

        return new RoomResource($room);
    }

    public function unsetCover(int $id)
    {
        /** @var Room $room */
        $room = $this->roomRepository->find($id)->getModel();
        $this->authorize('update', $room);

        $room->clearMediaCollection(Room::MEDIA_COLLECTION_THUMBNAIL);

        event(new RoomUpdated($room));

        return new RoomResource($room);
    }

    public function exportVisitorsEmailToCSV(int $id)
    {
        $room = $this->roomRepository->find($id)->getModel();
        $this->authorize('update', $room);

        $visitors = collect([]);
        $webinars = $room->webinars;

        foreach ($webinars as $webinar) {
            $this->webinarRepository->setModel($webinar);
            $activeVisitors = $this->webinarRepository->getActiveVisitors();
            foreach ($activeVisitors as $activeVisitor) {
                $visitors->push($activeVisitor);
            }
        }

        return ExportUserEmailResource::collection($visitors);
    }

    public function exportVisitorsPhoneToCSV(int $id)
    {
        $room = $this->roomRepository->find($id)->getModel();
        $this->authorize('update', $room);

        $visitors = collect([]);
        $webinars = $room->webinars;

        foreach ($webinars as $webinar) {
            $this->webinarRepository->setModel($webinar);
            $activeVisitors = $this->webinarRepository
                ->getActiveVisitors()
                ->filter(function ($visitor) {
                    return $visitor->phone;
                });

            foreach ($activeVisitors as $activeVisitor) {
                $visitors->push($activeVisitor);
            }
        }

        return ExportUserPhoneResource::collection($visitors);
    }

    public function exportVisitorsEmailAndPhoneToCSV(int $id)
    {
        $room = $this->roomRepository->find($id)->getModel();
        $this->authorize('update', $room);

        $visitors = collect([]);
        $webinars = $room->webinars;

        foreach ($webinars as $webinar) {
            $this->webinarRepository->setModel($webinar);
            $activeVisitors = $this->webinarRepository->getActiveVisitors();
            foreach ($activeVisitors as $activeVisitor) {
                $visitors->push($activeVisitor);
            }
        }

        return ExportUserEmailPhoneResource::collection($visitors);
    }

    /**
     * @param RoomUploadPresentationRequest $request
     * @param int $id
     * @return ConversionResource
     * @throws AuthorizationException
     */
    public function setPresentation(RoomUploadPresentationRequest $request, int $id): ConversionResource
    {
        /** @var Room $room */
        $room = $this->roomRepository->find($id)->getModel();
        $this->authorize('update', $room);

        $conversion = $room->conversions()->save((new Conversion()));

        $file = $request->file('pdf');
        $quality = $request->get('quality');

        try {
            $room->clearMediaCollection(Room::MEDIA_COLLECTION_PDF);

            $pdfMedia = $room->addMedia($file)
                ->usingFileName($file->getClientOriginalName())
                ->toMediaCollection(Room::MEDIA_COLLECTION_PDF);

            $this->dispatch(new ProcessRoomPdf($room, $pdfMedia, $quality));
        } catch (\Exception $e) {
            $conversion->status = Conversion::STATUS_FAILED;
            $conversion->save();
        }

        return new ConversionResource($conversion);
    }

    /**
     * @param int $id
     * @return RoomResource
     * @throws AuthorizationException
     */
    public function unsetPresentation(int $id): RoomResource
    {
        /** @var Room $room */
        $room = $this->roomRepository->find($id)->getModel();
        $this->authorize('update', $room);

        $room->clearMediaCollection(Room::MEDIA_COLLECTION_PDF);
        $room->clearMediaCollection(Room::MEDIA_COLLECTION_PRESENTATION_SLIDES);

        return new RoomResource($room);
    }

    /**
     * @param int $webinarId
     * @return ConversionResource
     * @throws AuthorizationException
     */
    public function getPresentationConversionStatus(int $webinarId): ConversionResource
    {
        /** @var Room $room */
        $room = $this->roomRepository->find($webinarId)->getModel();
        $this->authorize('update', $room);

        $conversion = $room->conversions->last();

        return new ConversionResource($conversion);
    }

    public function importScript(int $id, RoomImportScriptRequest $request)
    {
        /** @var Room $room */
        $room = $this->roomRepository->find($id)->getModel();
        $this->authorize('update', $room);

        if ($request->file('script')->isValid()) {
            $file = $request->file('script');
            $commands = $this->scriptImportService->import($id, $file);

            return ScriptCommandResource::collection($commands);
        }
    }

    public function scriptCommandIndex(int $id)
    {
        /** @var Room $room */
        $room = $this->roomRepository->find($id)->getModel();
        $this->authorize('update', $room);

        $commands = $this->scriptRepository->getRoomCommands($id);

        return ScriptCommandResource::collection($commands);
    }

    public function scriptCommandAdd(int $id, RoomScriptCommandRequest $request)
    {
        /** @var Room $room */
        $room = $this->roomRepository->find($id)->getModel();
        $this->authorize('update', $room);

        $attributes = $request->validated();
        $attributes['room_id'] = $id;
        $command = $this->scriptRepository->create($attributes);

        if ($request->get('action') === Script::ACTION_STOP_RECORD) {
            $scriptDurationMinutes = $request->get('timeshift') / 1000 / 60;
            if ($scriptDurationMinutes > $room->duration_minutes) {
                $room->duration_minutes = $scriptDurationMinutes;
                $room->save();
            }
        }

        return new ScriptCommandResource($command);
    }

    public function scriptCommandUpdate(int $roomId, int $commandId, RoomScriptCommandRequest $request)
    {
        /** @var Room $room */
        $room = $this->roomRepository->find($roomId)->getModel();
        $this->authorize('update', $room);

        $attributes = $request->validated();
        $command = $this->scriptRepository->find($commandId)->fill($attributes);
        $command->save();

        if ($request->get('action') === Script::ACTION_STOP_RECORD) {
            $this->roomRepository->extendDurationIfNeeded($request->get('timeshift'));
        }

        return new ScriptCommandResource($command);
    }

    public function scriptCommandDelete(int $roomId, int $commandId): void
    {
        /** @var Room $room */
        $room = $this->roomRepository->find($roomId)->getModel();
        $this->authorize('update', $room);

        $this->scriptRepository->deleteRoomCommand($commandId);
    }

    public function scriptCommandsDelete(int $roomId): void
    {
        /** @var Room $room */
        $room = $this->roomRepository->find($roomId)->getModel();
        $this->authorize('update', $room);

        $this->scriptRepository->deleteRoomCommands($roomId);
    }
}
