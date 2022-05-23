<?php

namespace App\Listeners;

use App\Services\UserService;
use Illion\UserSync\Events\UserSyncDataReceived;


/**
 * Class SynchronizeUser
 * @package App\Listeners
 */
class SynchronizeUser
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * SynchronizeUser constructor.
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @param UserSyncDataReceived $event
     * @throws \Throwable
     */
    public function handle(UserSyncDataReceived $event): void
    {
        $data = $event->data;
        // During registration flow we first submit gender then name
        // as the result first sync request will not have name attribute
        // which is not nullable in the DB so we need to skip such request
        if (!empty($data['name'])) {
            $user = $this->userService->findByExternalId($event->user['external_id']);
            $this->mapDataAttributes($data);
            $this->userService->update($user, $data);
        }
    }

    /**
     * @param $data
     */
    protected function mapDataAttributes(&$data)
    {
        $data['phone_verified_at'] = array_key_exists('phone_confirmed_at', $data) ? $data['phone_confirmed_at'] : null;
        $data['email_verified_at'] = array_key_exists('email_confirmed_at', $data) ? $data['email_confirmed_at'] : null;
    }
}
