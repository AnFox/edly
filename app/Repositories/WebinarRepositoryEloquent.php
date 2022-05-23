<?php

namespace App\Repositories;

use App\Contracts\Repositories\ChatRepository;
use App\Contracts\Repositories\UserRepository;
use App\Contracts\Repositories\WebinarRepository;
use App\Models\Room;
use App\Models\User;
use App\Models\Webinar;
use Carbon\Carbon;
use DateInterval;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class WebinarRepositoryEloquent
 * @package App\Repositories
 */
class WebinarRepositoryEloquent extends BaseRepositoryEloquent implements WebinarRepository
{
    /** @var Webinar */
    protected $model;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var ChatRepository
     */
    private $chatRepository;

    /**
     * WebinarRepositoryEloquent constructor.
     * @param Webinar $model
     * @param UserRepository $userRepository
     * @param ChatRepository $chatRepository
     */
    public function __construct(Webinar $model, UserRepository $userRepository, ChatRepository $chatRepository)
    {
        $this->setModel($model);
        $this->userRepository = $userRepository;
        $this->model = $model;
        $this->chatRepository = $chatRepository;
    }

    /**
     * @param array $attributes
     * @return $this|mixed
     */
    public function create(array $attributes)
    {
        $model = $this->model->create($attributes);

        if ($model->room->type_id === Room::TYPE_AUTO) {
            $model->chat()->create(['is_active' => true]);
        } else {
            $model->chat()->create();
        }

        return $model->fresh();
    }

    /**
     * @param int $roomId
     * @return mixed
     */
    public function findByRoomId(int $roomId)
    {
        $builder = QueryBuilder::for($this->model->where('room_id', $roomId))
            ->allowedFilters([
                AllowedFilter::scope('type'),
            ]);
        $this->setBuilder($builder);

        return $this;
    }

    /**
     * @return mixed
     */
    public function addChat()
    {
        return $this->model->chat()->create();
    }

    /**
     * @return mixed
     */
    public function getVisitors()
    {
        return $this->model->visitors;
    }

    /**
     * @param array $idList
     * @return mixed
     */
    public function getVisitorsByIdList(array $idList)
    {
        return $this->model->visitorsByIdList($idList)->get();
    }

    /**
     * @return mixed
     */
    public function getActiveVisitors()
    {
        return $this->model->visitorsActive;
    }

    /**
     * @return bool
     */
    public function isStarted(): bool
    {
        /** @var Webinar $webinar */
        $webinar = $this->getModel();

        $genericCondition = $webinar->starts_at && $webinar->starts_at->lte(now());

        if ($webinar->room->type_id === Room::TYPE_LIVE) {
            return $genericCondition && $webinar->is_started;
        }

        return $genericCondition;
    }

    /**
     * @return bool
     */
    public function isFinishTimePastDue(): bool
    {
        /** @var Webinar $webinar */
        $webinar = $this->getModel();
        $finishTime = $webinar->starts_at->addMinutes($webinar->room->duration_minutes);

        return $finishTime->lte(now());
    }

    /**
     * @return bool
     */
    public function isFinishTimeWithPastDue(): bool
    {
        /** @var Webinar $webinar */
        $webinar = $this->getModel();
        switch ($webinar->room->type_id) {
            case Room::TYPE_LIVE:
                $finishTime = $webinar->starts_at->addMinutes($webinar->room->duration_minutes)->addHour();
                break;
            case Room::TYPE_AUTO:
                $finishTime = $webinar->starts_at->addMinutes($webinar->room->duration_minutes)->addMinutes(10);
                break;
            default:
                $finishTime = $webinar->starts_at->addMinutes($webinar->room->duration_minutes)->addHour();
                break;
        }

        return $finishTime->lte(now());
    }

    /**
     * @return bool
     */
    public function isFinished(): bool
    {
        /** @var Webinar $webinar */
        $webinar = $this->getModel();

        return $webinar->finished_at || ($this->isFinishTimePastDue() && !$webinar->visitorsOnline);
    }

    /**
     * @return bool
     */
    public function isBroadcasting(): bool
    {
        /** @var Webinar $webinar */
        $webinar = $this->getModel();

        return $webinar->starts_at && $webinar->starts_at->lte(now()) && !$this->isFinished();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function save(): bool
    {
        if ($this->model->starts_at && $this->isFinished()) {
            throw new UnprocessableEntityHttpException('Нельзя редактировать завершенный вебинар.');
        }

        return parent::save();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function delete(): bool
    {
        if ($this->isFinished()) {
            throw new UnprocessableEntityHttpException('Нельзя удалять завершенный вебинар.');
        }

        if ($this->isBroadcasting()) {
            throw new UnprocessableEntityHttpException('Нельзя удалять идущий вебинар.');
        }

        return parent::delete();
    }

    /**
     * @return mixed
     */
    public function getAccount()
    {
        return $this->userRepository->setModel($this->model->room->owner)->getFirstLinkedAccount();
    }

    /**
     * @return User[]
     */
    public function getUnpaidVisitors()
    {
        return $this->model->visitorsUnpaid;
    }

    /**
     * @return int
     */
    public function getUnpaidVisitorsCount(): int
    {
        return $this->model->visitorsUnpaid()->count();
    }

    /**
     * @return mixed
     */
    public function unfinished()
    {
        return $this->model->whereNull('finished_at')->get();
    }

    /**
     * @return mixed
     */
    public function finished()
    {
        return $this->model->whereNotNull('finished_at');
    }

    /**
     * @return mixed
     */
    public function finishedWithUnpaidVisitors()
    {
        return $this->model
            ->select('webinars.*')
            ->distinct()
            ->whereNotNull('finished_at')
            ->join(
                'user_webinar',
                'webinars.id', '=', 'user_webinar.webinar_id'
            )
            ->whereRaw('(user_webinar.joined_at >= webinars.starts_at or user_webinar.left_at >= webinars.starts_at)')
            ->get();
    }

    public function setFinished()
    {
        $this->model->finished_at = now();
        $this->model->is_started = false;
        $this->model->save();

        $this->model->chat->is_active = false;
        $this->model->chat->save();
    }

    public function setAllVisitorsOffline()
    {
        $visitors = $this->getVisitors();

        foreach ($visitors as $visitor) {
            $this->userRepository->setModel($visitor);
            $this->userRepository->setWebinarLeaved($this->getModel());
        }

    }

    public function setStarted()
    {
        $this->model->starts_at = now();
        $this->model->is_started = true;
        $this->model->save();
    }

    /**
     * @return User[]|mixed
     */
    public function getBlockedVisitors()
    {
        return $this->model->visitorsBlocked;
    }

    /**
     * @return User[]|mixed
     */
    public function getBannedVisitors()
    {
        return $this->model->visitorsBanned;
    }

    /**
     * @param string $intendedUrl
     * @return mixed
     */
    public function findByIntendedUrl(string $intendedUrl)
    {
        $regExp = '/\/webinar\/(\d+)(\/.+)?/i';

        if (preg_match($regExp, $intendedUrl, $matches, PREG_OFFSET_CAPTURE, 0)) {
            $roomId = $matches[1][0];
            return $this->getCurrentWebinar($roomId);
        }
    }

    /**
     * @param Media $slide
     */
    public function setCurrentSlide(Media $slide)
    {
        $this->model->current_slide()->associate($slide)->save();
    }

    /**
     * @param string $layout
     */
    public function setLayout(string $layout)
    {
        $this->model->layout = $layout;
        $this->model->save();
    }

    /**
     * @param string $tab
     */
    public function setTab(string $tab)
    {
        $this->model->tab = $tab;
        $this->model->save();
    }

    /**
     * @param int $roomId
     * @return mixed
     */
    public function getAllRoomWebinars(int $roomId)
    {
        return $this->model
            ->where('room_id', $roomId)
            ->get();
    }

    /**
     * @param int $roomId
     * @return mixed
     */
    public function getCurrentWebinar(int $roomId)
    {
        // First we attempt to find already started webinar
        $webinar = $this->model
            ->where('room_id', $roomId)
            ->whereNull('finished_at')
            ->where('is_started', true)
            ->orderBy('starts_at', 'desc')
            ->first();

        if (!$webinar) {
            // Then we attempt to find next webinar planned in the future
            $webinar = $this->model
                ->where('room_id', $roomId)
                ->whereNull('finished_at')
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at', 'asc')
                ->first();
        }

        if (!$webinar) {
            // Then we attempt to find last planned webinar
            $webinar = $this->model
                ->where('room_id', $roomId)
                ->whereNull('finished_at')
                ->orderBy('starts_at', 'desc')
                ->first();
        }

        return $webinar;
    }

    public function getCurrentAutoWebinars()
    {
        $webinars = $this->model
            ->whereHas('room', function (Builder $query) {
                $query->where('type_id', Room::TYPE_AUTO);
            })
            ->where('is_playing', false)
            ->whereDate('starts_at', now())
            ->whereTime('starts_at', '>=', Carbon::createFromTime(Carbon::now()->hour,Carbon::now()->minute,00)->format('H:i:s'))
            ->whereTime('starts_at', '<=', Carbon::createFromTime(Carbon::now()->hour,Carbon::now()->minute,59)->format('H:i:s'))
            ->groupBy('room_id')
            ->get();

        return $webinars;
    }

    /**
     * @param $id
     * @param Carbon $scheduled_at
     * @param string $schedule_interval
     * @return WebinarRepositoryEloquent|mixed
     * @throws Exception
     */
    public function createScheduled($id, Carbon $scheduled_at, string $schedule_interval)
    {
        return $this->create([
            'is_scheduled' => true,
            'room_id' => $id,
            'starts_at' => $scheduled_at->add(new DateInterval('P' . $schedule_interval))
        ]);
    }

    public function blockChat()
    {
        $this->chatRepository->setModel($this->getModel()->chat)->block();
    }

    public function unblockChat()
    {
        $this->chatRepository->setModel($this->getModel()->chat)->unblock();
    }

    public function setRecordable(): void
    {
        $this->model->is_recordable = true;
        $this->model->save();
    }
}
