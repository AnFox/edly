<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\ChatRepository;
use App\Contracts\Repositories\WebinarRepository;
use App\Http\Resources\ChatResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class ChatController
 * @package App\Http\Controllers
 */
class ChatController extends Controller
{
    /**
     * @var WebinarRepository
     */
    private $webinarRepository;

    /**
     * @var ChatRepository
     */
    private $chatRepository;

    /**
     * ChatController constructor.
     * @param WebinarRepository $webinarRepository
     * @param ChatRepository $chatRepository
     */
    public function __construct(WebinarRepository $webinarRepository, ChatRepository $chatRepository)
    {
        $this->webinarRepository = $webinarRepository;
        $this->chatRepository = $chatRepository;
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return ChatResource
     */
    public function show(int $id): ChatResource
    {
        return new ChatResource($this->chatRepository->findByWebinarId($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
