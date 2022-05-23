<?php


namespace App\Contracts\Repositories;


/**
 * Interface ScriptRepository
 * @package App\Contracts\Repositories
 */
interface ScriptRepository extends BaseRepository
{
    public function createRoomCommand(int $roomId, int $timeShift, string $action, array $payload);

    public function getRoomCommands(int $roomId);

    public function deleteRoomCommand(int $commandId);

    public function deleteRoomCommands(int $roomId): void;
}
