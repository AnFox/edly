<?php


namespace App\Contracts\Repositories;


/**
 * Interface BannerRepository
 * @package App\Contracts\Repositories
 */
interface BannerRepository extends BaseRepository {

    /**
     * @return mixed
     */
    public function getProduct();

    /**
     * @return mixed
     */
    public function getRoom();

    /**
     * @param int $roomId
     * @return mixed
     */
    public function getListByRoomId(int $roomId);
}
