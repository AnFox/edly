<?php


namespace App\Contracts\Repositories;


use Illuminate\Support\Collection;

/**
 * Interface ChatMessageRepository
 * @package App\Contracts\Repositories
 */
interface ChatMessageRepository extends BaseRepository {
    /**
     * Get collection of messages by chat ID
     *
     * @param int $chatId
     * @return mixed
     */
    public function getMessagesByChatId(int $chatId);

    /**
     * @param array $idList
     * @param int $chatId
     * @return mixed
     */
    public function deleteMessages(array $idList, int $chatId): void;

    /**
     * Get collection of messages by the list of ID
     *
     * @param array $idList
     * @return Collection
     */
    public function getByIdList(array $idList);

    /**
     * Get chat message by banner ID
     *
     * @param $banner
     * @return mixed
     */
    public function getBannerMessages($banner);
}
