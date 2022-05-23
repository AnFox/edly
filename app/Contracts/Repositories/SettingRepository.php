<?php


namespace App\Contracts\Repositories;


/**
 * Interface SettingRepository
 * @package App\Contracts\Repositories
 */
interface SettingRepository extends BaseRepository {

    /**
     * @param string $name
     * @return mixed
     */
    public function getValueByName(string $name);
};
