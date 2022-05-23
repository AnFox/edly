<?php

namespace App\Repositories;

use App\Contracts\Repositories\SettingRepository;
use App\Contracts\Repositories\UserRepository;
use App\Contracts\Repositories\WebinarRepository;
use App\Models\Account;
use App\Models\Room;
use App\Models\Setting;
use App\Models\User;
use App\Models\Webinar;
use App\Notifications\UsersLimitIsReaching;
use App\Notifications\UsersLimitReached;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Class UserRepositoryEloquent
 * @package App\Repositories
 */
class UserRepositoryEloquent extends BaseRepositoryEloquent implements UserRepository
{
    /** @var User */
    protected $model;
    /**
     * @var SettingRepository
     */
    private $settingRepository;

    /**
     * UserRepositoryEloquent constructor.
     * @param User $model
     * @param SettingRepository $settingRepository
     */
    public function __construct(User $model, SettingRepository $settingRepository)
    {
        $this->setModel($model);
        $this->model = $model;
        $this->settingRepository = $settingRepository;
    }

    /**
     * @param int $externalId
     * @return User|null
     */
    public function findByExternalId(int $externalId): ?User
    {
        return $this->model->where(['external_id' => $externalId])->first();
    }

    public function createAccount(): void
    {
        /** @var Account $account */
        $account = Account::create([
            'name' => $this->model->name,
        ]);

        $account->status = Account::STATUS_ACTIVE;

        if (Setting::isTrialTypeTime()) {
            $account->trial_ends_at = Carbon::now()->addDays(Setting::getTrialDays());
        } else {
            if ($trialAmount = $this->settingRepository->getValueByName(Account::SETTING_NAME_TRIAL_AMOUNT)) {
                $account->balance = $trialAmount;
            }
        }

        $account->save();

        $this->setLinkedAccount($account);
    }

    /**
     * Get user linked accounts
     *
     * @return Collection
     */
    public function getLinkedAccounts(): Collection
    {
        return $this->model->linkedAccounts;
    }

    /**
     * Link user to account
     *
     * @param $account
     */
    public function setLinkedAccount($account): void
    {
        $this->model->linkedAccounts()->syncWithoutDetaching([$account->id]);
        $this->model->linkedAccounts()->updateExistingPivot($account->id, ['role_id' => $this->model->roles->pluck('id')->first()]);
    }

    /**
     * Get first linked account
     *
     * @return mixed
     */
    public function getFirstLinkedAccount()
    {
        return $this->model->linkedAccounts()->first();
    }

    /**
     * Get collection of visited webinars
     *
     * @return Collection
     */
    public function getVisitedWebinars(): Collection
    {
        return $this->model->webinarsVisited;
    }

    /**
     * Get collection of visited webinars
     *
     * @return Paginator
     */
    public function getWebinarsVisitedExcludedOwnedPaginated(): Paginator
    {
        $listOfRoomsOwnedId = Room::whereUserId(request()->user()->id)->pluck('id')->toArray();
        $listOfWebinarsOwnedId = Webinar::whereIn('room_id', $listOfRoomsOwnedId)->pluck('id')->toArray();

        $builder = QueryBuilder::for(
            $this->model
                ->webinarsVisited()->getQuery()
                ->whereNotIn('webinar_id', $listOfWebinarsOwnedId))
            ->select('webinars.*')
            ->allowedFilters([
                AllowedFilter::scope('type'),
            ]);
        $this->setBuilder($builder);

        return $this->paginate(null, 'webinar_id', 'desc');
    }

    /**
     * Set webinar visited by user
     *
     * @param Webinar $webinar
     */
    public function setWebinarVisited($webinar): void
    {
        $this->model->webinarsVisited()->syncWithoutDetaching([$webinar->id]);
        $this->model->webinarsVisited()->updateExistingPivot($webinar->id, [
            'is_user_online' => true,
            'joined_at' => now(),
        ]);

        if ($webinar->isTrialUsersLimitAlmostReached()) {
            $owner = $webinar->getOwner();
            $owner->notify(new UsersLimitIsReaching($webinar));
        }
    }

    /**
     * Set webinar leaved by user
     *
     * @param $webinar
     */
    public function setWebinarLeaved($webinar): void
    {
        $this->model->webinarsVisited()->updateExistingPivot($webinar->id, [
            'is_user_online' => false,
            'left_at' => now(),
        ]);

        $webinarRepository = app(WebinarRepository::class);
        $webinarRepository->setModel($webinar);
        if ($webinarRepository->isStarted() && !$webinarRepository->isBroadcasting()) {
            $webinarRepository->setFinished();
        }
    }

    /**
     * Set webinar leaved by user
     *
     * @param $webinar
     */
    public function setWebinarChargeProcessing($webinar): void
    {
        $this->model->webinarsVisited()->updateExistingPivot($webinar->id, ['is_charging' => true]);
    }

    /**
     * Unset webinar leaved by user
     *
     * @param $webinar
     */
    public function setWebinarChargedSuccessfully($webinar): void
    {
        $this->model->webinarsVisited()->updateExistingPivot($webinar->id, ['is_charging' => false, 'is_paid' => true]);
    }

    /**
     * Set user offline to all visited webinars by user
     *
     * @return void
     */
    public function setUserOfflineToVisitedWebinars(): void
    {
        $this->model->webinarsVisited()->pluck('id')->each(function ($id) {
            $this->model->webinarsVisited()->updateExistingPivot($id, ['is_user_online' => false]);
        });
    }

    /**
     * Block chat for User on webinar
     *
     * @param $webinar
     */
    public function setChatBlockedOnWebinar($webinar): void
    {
        $this->model->webinarsVisited()->updateExistingPivot($webinar->id, ['user_status' => User::STATUS_CHAT_BLOCKED]);
    }

    /**
     * @param $webinar
     */
    public function setBannedOnWebinar($webinar): void
    {
        $this->model->webinarsVisited()->updateExistingPivot($webinar->id, ['user_status' => User::STATUS_BANNED]);
    }

    /**
     * @param $webinar
     */
    public function setActiveOnWebinar($webinar): void
    {
        $this->model->webinarsVisited()->updateExistingPivot($webinar->id, ['user_status' => User::STATUS_ACTIVE]);
    }

    public function getByEmail(string $email)
    {
        return $this->model->whereEmail($email)->first();
    }

    public function isPaidInWebinar(Webinar $webinar)
    {
        return $this->model->webinarsVisited()->where('webinar_id', $webinar->id)
            ->where('is_paid', 1)
            ->count();
    }
}
