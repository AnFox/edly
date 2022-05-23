<?php


namespace App\Contracts\Repositories;


/**
 * Interface ChatRepository
 * @package App\Contracts\Repositories
 */
interface ChatRepository extends BaseRepository
{
    /**
     * @param int $webinarId
     * @return mixed
     */
    public function findByWebinarId(int $webinarId);

    /**
     * Get chat webinar
     *
     * @return mixed
     */
    public function getWebinar();

    public function block(): void;

    public function unblock(): void;
}