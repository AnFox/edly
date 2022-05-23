<?php


namespace App\Contracts\Repositories;


use App\Http\Requests\Admin\RoomDuplicateRequest;

/**
 * Interface RoomRepository
 * @package App\Contracts\Repositories
 */
interface RoomRepository extends BaseRepository {
    /**
     * @param int $authorId
     * @return mixed
     */
    public function findByAuthor(int $authorId);

    public function duplicate($source, RoomDuplicateRequest $request);

    public function hasFinishedWebinars(): bool;

    public function hasBroadcastingWebinars(): bool;

    public function getAccount();

    public function setWaitingText($waitingText);

    public function slides();

    public function getSlide(int $id);

    public function getByType(int $typeId);

    public function extendDurationIfNeeded(int $durationMilliseconds);
};
