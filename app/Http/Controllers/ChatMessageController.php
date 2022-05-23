<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\BannerRepository;
use App\Contracts\Repositories\ChatMessageRepository;
use App\Contracts\Repositories\ProductRepository;
use App\Http\Requests\ChatMessageRequest;
use App\Http\Resources\ChatMessageResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

/**
 * Class ChatMessageController
 * @package App\Http\Controllers
 */
class ChatMessageController extends Controller
{
    /**
     * @var ChatMessageRepository
     */
    private $chatMessageRepository;
    /**
     * @var BannerRepository
     */
    private $bannerRepository;
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * ChatMessageController constructor.
     * @param ChatMessageRepository $chatMessageRepository
     * @param BannerRepository $bannerRepository
     * @param ProductRepository $productRepository
     */
    public function __construct(ChatMessageRepository $chatMessageRepository,
                                BannerRepository $bannerRepository,
                                ProductRepository $productRepository)
    {
        $this->chatMessageRepository = $chatMessageRepository;
        $this->bannerRepository = $bannerRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param int $chatId
     * @return AnonymousResourceCollection
     */
    public function index(int $chatId): AnonymousResourceCollection
    {
        return ChatMessageResource::collection($this->chatMessageRepository->getMessagesByChatId($chatId));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param int $chatId
     * @param ChatMessageRequest $request
     * @return ChatMessageResource
     */
    public function store(int $chatId, ChatMessageRequest $request)
    {
        $attributes = $request->validated();
        $attributes['chat_id'] = $chatId;
        $attributes['sender_user_id'] = Auth::user()->id;

        if ($request->banner_id) {
            $banner = $this->bannerRepository->find($request->banner_id)->getModel();
            $attributes['message'] = $banner->title;
        }

        $message = $this->chatMessageRepository->create($attributes);

        return new ChatMessageResource($message);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
