<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\BannerRepository;
use App\Contracts\Repositories\RoomRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BannerRequest;
use App\Http\Requests\Admin\BannerSetImageRequest;
use App\Http\Requests\Admin\BannerToggleVisibilityRequest;
use App\Http\Requests\Admin\BannerUploadImageRequest;
use App\Http\Resources\Admin\BannerResource;
use App\Http\Resources\Admin\MediaResource;
use App\Models\Room;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * Class BannerController
 * @package App\Http\Controllers
 */
class BannerController extends Controller
{
    /**
     * @var BannerRepository
     */
    private $bannerRepository;
    /**
     * @var RoomRepository
     */
    private $roomRepository;

    /**
     * BannerController constructor.
     * @param BannerRepository $bannerRepository
     * @param RoomRepository $roomRepository
     */
    public function __construct(BannerRepository $bannerRepository, RoomRepository $roomRepository)
    {
        $this->bannerRepository = $bannerRepository;
        $this->roomRepository = $roomRepository;

        // @todo: Uncomment when fixed https://github.com/laravel/framework/issues/32409
        // $this->authorizeResource(Banner::class, 'banner');
    }

    /**
     * Display a listing of the resource.
     *
     * @param int $roomId
     * @return AnonymousResourceCollection
     */
    public function index(int $roomId): AnonymousResourceCollection
    {
        $banners = $this->bannerRepository->getListByRoomId($roomId);

        return BannerResource::collection($banners);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param BannerRequest $request
     * @return BannerResource
     * @throws AuthorizationException
     */
    public function store(BannerRequest $request): BannerResource
    {
        $room = $this->roomRepository->find($request->get('room_id'))->getModel();
        $account = $this->roomRepository->getAccount();

        $this->authorize('create-banner', $room);

        $attributes = $request->validated();
        $attributes['account_id'] = $account->id;
        $banner = $this->bannerRepository->create($attributes);

        return new BannerResource($banner);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return BannerResource
     * @throws AuthorizationException
     */
    public function show($id): BannerResource
    {
        $banner = $this->bannerRepository->findOrFail($id)->getModel();
        $this->authorize('view-banner', $banner);

        return new BannerResource($banner);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param BannerRequest $request
     * @param int $id
     * @return BannerResource
     * @throws AuthorizationException
     */
    public function update(BannerRequest $request, $id)
    {
        $room = $this->bannerRepository->findOrFail($id)->getRoom();
        $this->authorize('update-banner', $room);

        $attributes = $request->validated();
        if (!empty($attributes['image'])) {
            $attributes['media_id'] = null;
        }
        if (!empty($attributes['media_id'])) {
            $attributes['image'] = null;
        }
        $this->bannerRepository->fill($attributes);
        $this->bannerRepository->save();
        $banner = $this->bannerRepository->getModel();

        return new BannerResource($banner);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     * @throws AuthorizationException
     */
    public function destroy($id)
    {
        $room = $this->bannerRepository->find($id)->getRoom();
        $this->authorize('delete-banner', $room);

        $this->bannerRepository->delete();

        return response()->json(['result' => 'success']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param BannerToggleVisibilityRequest $request
     * @param int $id
     * @return BannerResource
     * @throws AuthorizationException
     */
    public function toggleVisibility(BannerToggleVisibilityRequest $request, $id)
    {
        $room = $this->bannerRepository->find($id)->getRoom();
        $this->authorize('update-banner', $room);

        $this->bannerRepository->fill($request->validated());
        $this->bannerRepository->save();
        $banner = $this->bannerRepository->getModel();

        return new BannerResource($banner);
    }

    public function imageIndex(int $roomId)
    {
        $room = $this->roomRepository->find($roomId)->getModel();

        return MediaResource::collection($room->images);
    }

    public function setImage(BannerSetImageRequest $request, int $id): BannerResource
    {
        $banner = $this->bannerRepository->find($id)->getModel();
        $attributes = $request->validated();
        if (!empty($attributes['image'])) {
            $attributes['media_id'] = null;
        }
        if (!empty($attributes['media_id'])) {
            $attributes['image'] = null;
        }
        $this->bannerRepository->fill($attributes);
        $this->bannerRepository->save();

        return new BannerResource($banner);
    }

    public function uploadImage(BannerUploadImageRequest $request, int $roomId): MediaResource
    {
        $room = $this->roomRepository->find($roomId)->getModel();
        $file = $request->file('image');
        $media = $room->addMedia($file)
            ->usingFileName($file->getClientOriginalName())
            ->toMediaCollection(Room::MEDIA_COLLECTION_BANNER_IMAGE);

        return new MediaResource($media);
    }
}
