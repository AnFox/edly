<?php

namespace App\Repositories;

use App\Contracts\Repositories\ChatRepository;
use App\Models\Chat;

/**
 * Class WebinarRepositoryEloquent
 * @package App\Repositories
 */
class ChatRepositoryEloquent extends BaseRepositoryEloquent implements ChatRepository
{
    protected $model;

    /**
     * ChatRepositoryEloquent constructor.
     * @param Chat $model
     */
    public function __construct(Chat $model)
    {
        $this->setModel($model);
    }

    /**
     * @param int $webinarId
     * @return Chat
     */
    public function findByWebinarId(int $webinarId): Chat
    {
        return $this->model->whereWebinarId($webinarId)->firstOrFail();
    }

    /**
     * Get chat webinar
     *
     * @return mixed
     */
    public function getWebinar()
    {
        return $this->model->webinar;
    }

    public function block(): void
    {
        $this->model->is_active = false;
        $this->model->save();
    }

    public function unblock(): void
    {
        $this->model->is_active = true;
        $this->model->save();
    }
}
