<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\RoomRepository;
use App\Contracts\Repositories\WebinarRepository;
use App\Events\NewRecordableAction;
use App\Events\WebinarSlideOpen;
use App\Http\Controllers\Controller;
use App\Http\Resources\SlideResource;
use App\Models\Room;
use App\Models\Script;
use App\Models\Webinar;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Class PresentationSlidesController
 * @package App\Http\Controllers\Admin
 */
class PresentationSlidesController extends Controller
{
    /**
     * @var WebinarRepository
     */
    private $webinarRepository;
    /**
     * @var RoomRepository
     */
    private $roomRepository;

    /**
     * PresentationSlidesController constructor.
     * @param WebinarRepository $webinarRepository
     * @param RoomRepository $roomRepository
     */
    public function __construct(WebinarRepository $webinarRepository, RoomRepository $roomRepository)
    {
        $this->webinarRepository = $webinarRepository;
        $this->roomRepository = $roomRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param int $id
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     */
    public function index(int $id): AnonymousResourceCollection
    {
        /** @var Room $room */
        $room = $this->roomRepository->find($id)->getModel();
        $this->authorize('update', $room);

        return SlideResource::collection($this->roomRepository->setModel($room)->slides());
    }

    /**
     * Display the specified resource.
     *
     * @param int $webinarId
     * @param int $slideId
     * @return SlideResource
     * @throws AuthorizationException
     */
    public function show(int $webinarId, int $slideId): SlideResource
    {
        /** @var Webinar $webinar */
        $webinar = $this->webinarRepository->find($webinarId)->getModel();
        $this->authorize('update', $webinar->room);

        /** @var Media $slide */
        $slide = $this->roomRepository->setModel($webinar->room)->getSlide($slideId);
        $this->webinarRepository->setCurrentSlide($slide);

        event(new WebinarSlideOpen($webinar, $slide));
        event(new NewRecordableAction($webinar, Script::ACTION_SET_PRESENTATION_PAGE, [
            'page' => $slide->id,
        ]));


        return new SlideResource($slide);
    }
}
