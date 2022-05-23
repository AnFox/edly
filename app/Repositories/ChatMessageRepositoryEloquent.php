<?php

namespace App\Repositories;

use App\Contracts\Repositories\ChatMessageRepository;
use App\Models\ChatMessage;

/**
 * Class WebinarRepositoryEloquent
 * @package App\Repositories
 */
class ChatMessageRepositoryEloquent extends BaseRepositoryEloquent implements ChatMessageRepository
{
    protected $model;

    /**
     * ChatMessageRepositoryEloquent constructor.
     * @param ChatMessage $model
     */
    public function __construct(ChatMessage $model)
    {
        $this->setModel($model);
    }

    /**
     * @param int $chatId
     * @return mixed
     */
    public function getMessagesByChatId(int $chatId)
    {
        $this->setModel($this->model->whereChatId($chatId));

        return $this->paginate(100);
    }

    /**
     * @param array $idList
     * @param int $chatId
     * @return mixed
     */
    public function deleteMessages(array $idList, int $chatId): void
    {
        $this->getByIdList($idList)->each(function ($message) use ($chatId) {
            if ($message->chat_id === $chatId) {
                $message->delete();
            }
        });
    }

    /**
     * Get collection of messages by the list of ID
     *
     * @param array $idList
     * @return mixed
     */
    public function getByIdList(array $idList)
    {
        return $this->model->whereIn('id', $idList)->get();
    }

    /**
     * Get chat message by banner ID
     *
     * @param $banner
     * @return mixed
     */
    public function getBannerMessages($banner)
    {
        return $this->model->where('banner_id', $banner->id)->get();
    }
}
