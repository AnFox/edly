<?php

namespace App\Repositories;

use App\Contracts\Repositories\ScriptRepository;
use App\Models\Script;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ScriptRepositoryEloquent
 * @package App\Repositories
 */
class ScriptRepositoryEloquent extends BaseRepositoryEloquent implements ScriptRepository
{
    protected $model;

    public function __construct(Script $model)
    {
        $this->setModel($model);
    }

    public function createRoomCommand(int $roomId, int $timeShift, string $action, array $payload): Script
    {
        $model = $this->model->create([
            'room_id' => $roomId,
            'timeshift' => $timeShift,
            'action' => $action,
            'payload' => json_encode($payload),
        ]);

        return $model;
    }

    public function getRoomCommands(int $roomId): Collection
    {
        return $this->model->where('room_id', $roomId)->orderBy('timeshift', 'asc')->get();
    }

    public function deleteRoomCommands(int $roomId): void
    {
        $this->model->where('room_id', $roomId)->delete();
    }

    public function deleteRoomCommand(int $commandId): void
    {
        $this->model->find($commandId)->delete();
    }
}
