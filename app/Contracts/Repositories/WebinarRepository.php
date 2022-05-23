<?php


namespace App\Contracts\Repositories;


use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Interface WebinarRepository
 * @package App\Contracts\Repositories
 */
interface WebinarRepository extends BaseRepository {

    /**
     * @param int $roomId
     * @return mixed
     */
    public function findByRoomId(int $roomId);

    /**
     * @return mixed
     */
    public function getVisitors();

    /**
     * @param array $idList
     * @return mixed
     */
    public function getVisitorsByIdList(array $idList);

    /**
     * @return mixed
     */
    public function getActiveVisitors();

    /**
     * @return mixed
     */
    public function getBlockedVisitors();

    /**
     * @return mixed
     */
    public function getBannedVisitors();

    /**
     * @param string $intendedUrl
     * @return mixed
     */
    public function findByIntendedUrl(string $intendedUrl);

    /**
     * @return mixed
     */
    public function addChat();

    /**
     * @return bool
     */
    public function isStarted(): bool;

    public function isFinishTimePastDue(): bool;

    public function isFinishTimeWithPastDue(): bool;

    /**
     * Is Webinar finished
     *
     * Webinar is finished if start time + duration is less than now and no one online
     *
     * @return bool
     */
    public function isFinished(): bool;

    /**
     * Is Webinar broadcasting now
     *
     * @return bool
     */
    public function isBroadcasting(): bool;

    /**
     * @return mixed
     */
    public function getAccount();

    /**
     * @return Collection|null
     */
    public function getUnpaidVisitors();

    public function getUnpaidVisitorsCount(): int;

    public function unfinished();

    public function finished();

    public function finishedWithUnpaidVisitors();

    public function setStarted();

    public function setFinished();

    public function setAllVisitorsOffline();

    public function setCurrentSlide(Media $slide);

    public function setLayout(string $layout);

    public function setTab(string $tab);

    public function getAllRoomWebinars(int $roomId);

    public function getCurrentWebinar(int $roomId);

    public function getCurrentAutoWebinars();

    public function createScheduled($id, Carbon $scheduled_at, string $schedule_interval);

    public function blockChat();

    public function unblockChat();

    public function setRecordable(): void;
};
