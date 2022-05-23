<?php


namespace App\Contracts\Repositories;


/**
 * Interface AccountRepository
 * @package App\Contracts\Repositories
 */
interface AccountRepository extends BaseRepository {
    /**
     * @return bool
     */
    public function canCreateRoom(): bool;

    /**
     * @return bool
     */
    public function canCreateWebinar(): bool;

    public function deleteCard(): void;
};
