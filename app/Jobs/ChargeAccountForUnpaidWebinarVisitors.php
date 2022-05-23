<?php

namespace App\Jobs;

use App\Contracts\Repositories\CurrencyRepository;
use App\Contracts\Repositories\UserRepository;
use App\Contracts\Repositories\WebinarRepository;
use App\Models\Account;
use App\Models\Webinar;
use App\Services\AccountService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class ChargeAccountForUnpaidWebinarVisitors
 * @package App\Jobs
 */
class ChargeAccountForUnpaidWebinarVisitors implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Webinar
     */
    private $webinar;
    /**
     * @var int
     */
    private $unpaidVisitorsCount;
    /**
     * @var AccountService
     */
    private $accountService;
    /**
     * @var CurrencyRepository
     */
    private $currencyRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var WebinarRepository
     */
    private $webinarRepository;

    /**
     * Create a new job instance.
     *
     * @param Webinar $webinar
     */
    public function __construct(Webinar $webinar)
    {
        $this->webinar = $webinar;
        $this->accountService = app(AccountService::class);
        $this->currencyRepository = app(CurrencyRepository::class);
        $this->userRepository = app(UserRepository::class);
        $this->webinarRepository = app(WebinarRepository::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $account = $this->webinar->room->owner->linkedAccounts()->first();

        $this->webinarRepository->setModel($this->webinar);
        $unpaidVisitors = $this->webinarRepository->getUnpaidVisitors();

        \Log::debug(__CLASS__, [
            'webinar ID' => $this->webinar->id,
            'unpaid visitors count' => $unpaidVisitors->count(),
            'unpaid visitors id' => $unpaidVisitors->pluck('id'),
        ]);

        $userIdList = [];
        foreach ($unpaidVisitors as $unpaidVisitor) {
            $this->userRepository->setModel($unpaidVisitor);
            $this->userRepository->setWebinarChargeProcessing($this->webinar);
            $userIdList[] = $unpaidVisitor->id;
        }

        if ($unpaidVisitorsCount = $unpaidVisitors->count()) {
            $amount = $unpaidVisitorsCount * Account::COST_PER_USER;
            $currencyCode = $this->currencyRepository->getDefaultCurrencyCode();
            $this->accountService->chargeAccount($this->webinar, $account, $amount, $currencyCode, $userIdList);
        }
    }

    /**
     * The job failed to process.
     *
     * @param Exception $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        if (app()->bound('sentry')) {
            app('sentry')->captureException($exception);
        }
    }
}
