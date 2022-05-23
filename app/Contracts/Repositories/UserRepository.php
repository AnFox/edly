<?php


namespace App\Contracts\Repositories;


use App\Models\Webinar;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

/**
 * Interface UserRepository
 * @package App\Contracts\Repositories
 */
interface UserRepository extends BaseRepository
{

    public function getByEmail(string $email);

    /**
     * Creates account for the user
     *
     * @return void
     */
    public function createAccount(): void;

    /**
     * Get user linked accounts
     *
     * @return Collection
     */
    public function getLinkedAccounts(): Collection;

    /**
     * Link user to account
     *
     * @param $account
     */
    public function setLinkedAccount($account): void;

    /**
     * Get first linked account
     *
     * @return mixed
     */
    public function getFirstLinkedAccount();

    /**
     * Get collection of visited webinars
     *
     * @return Collection
     */
    public function getVisitedWebinars(): Collection;

    /**
     * @return Paginator
     */
    public function getWebinarsVisitedExcludedOwnedPaginated(): Paginator;

    /**
     * Set webinar visited by user
     *
     * @param $webinar
     */
    public function setWebinarVisited($webinar): void;

    /**
     * Set webinar leaved by user
     *
     * @param $webinar
     */
    public function setWebinarLeaved($webinar): void;

    /**
     * Set user in process of charging
     *
     * @param $webinar
     */
    public function setWebinarChargeProcessing($webinar): void;

    /**
     * Unset user in process of charging
     *
     * @param $webinar
     */
    public function setWebinarChargedSuccessfully($webinar): void;

    /**
     * Set user offline to all visited webinars by user
     *
     * @return void
     */
    public function setUserOfflineToVisitedWebinars(): void;

    /**
     * Block chat for User on webinar
     *
     * @param $webinar
     */
    public function setChatBlockedOnWebinar($webinar): void;

    /**
     * Ban User on webinar
     *
     * @param $webinar
     */
    public function setBannedOnWebinar($webinar): void;

    /**
     * Unban User on Webinar
     *
     * @param $webinar
     */
    public function setActiveOnWebinar($webinar): void;

    /**
     * @param Webinar $webinar
     * @return mixed
     */
    public function isPaidInWebinar(Webinar $webinar);
}
