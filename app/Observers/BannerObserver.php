<?php

namespace App\Observers;

use App\Contracts\Repositories\ChatMessageRepository;
use App\Contracts\Repositories\WebinarRepository;
use App\Events\ChatMessageDeleted;
use App\Events\ChatMessageUpdated;
use App\Events\WebinarUpdated;
use App\Models\Banner;

/**
 * Class BannerObserver
 * @package App\Observers
 */
class BannerObserver
{
    /**
     * @var ChatMessageRepository
     */
    private $chatMessageRepository;
    /**
     * @var WebinarRepository
     */
    private $webinarRepository;

    /**
     * BannerObserver constructor.
     * @param ChatMessageRepository $chatMessageRepository
     * @param WebinarRepository $webinarRepository
     */
    public function __construct(ChatMessageRepository $chatMessageRepository, WebinarRepository $webinarRepository)
    {
        $this->chatMessageRepository = $chatMessageRepository;
        $this->webinarRepository = $webinarRepository;
    }

    /**
     * @param Banner $banner
     */
    public function created(Banner $banner)
    {
        if ($webinars = $this->webinarRepository->getAllRoomWebinars($banner->room_id)) {
            foreach ($webinars as $webinar) {
                event(new WebinarUpdated($webinar));
            }
        }
    }

    /**
     * @param Banner $banner
     */
    public function updated(Banner $banner)
    {
        if ($webinar = $this->webinarRepository->getCurrentWebinar($banner->room_id)) {
            $this->chatMessageRepository->getBannerMessages($banner)->each(function ($message) {
                event(new ChatMessageUpdated($message));
            });
        }

        if ($webinars = $this->webinarRepository->getAllRoomWebinars($banner->room_id)) {
            foreach ($webinars as $webinar) {
                event(new WebinarUpdated($webinar));
            }
        }
    }

    /**
     * @param Banner $banner
     */
    public function deleted(Banner $banner)
    {
        $this->chatMessageRepository->getBannerMessages($banner)->each(function ($message) {
            event(new ChatMessageDeleted($message));
        });

        if ($webinars = $this->webinarRepository->getAllRoomWebinars($banner->room_id)) {
            foreach ($webinars as $webinar) {
                event(new WebinarUpdated($webinar));
            }
        }
    }
}
