<?php

namespace App\Console\Commands;

use App\Contracts\Repositories\CurrencyRepository;
use App\Contracts\Repositories\UserRepository;
use App\Contracts\Repositories\WebinarRepository;
use App\Models\Account;
use App\Models\Webinar;
use App\Services\AccountService;
use Illuminate\Console\Command;

/**
 * Class ChargeForUnpaidVisitors
 * @package App\Console\Commands
 */
class ChargeForUnpaidVisitors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webinar:charge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Charge finished webinars accounts for unpaid visitors.';
    /**
     * @var AccountService
     */
    private $accountService;
    /**
     * @var WebinarRepository
     */
    private $webinarRepository;
    /**
     * @var CurrencyRepository
     */
    private $currencyRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * Create a new command instance.
     *
     * @param AccountService $accountService
     * @param WebinarRepository $webinarRepository
     * @param CurrencyRepository $currencyRepository
     * @param UserRepository $userRepository
     */
    public function __construct(AccountService $accountService,
                                WebinarRepository $webinarRepository,
                                CurrencyRepository $currencyRepository,
                                UserRepository $userRepository)
    {
        parent::__construct();
        $this->accountService = $accountService;
        $this->webinarRepository = $webinarRepository;
        $this->currencyRepository = $currencyRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws \Exception
     */
    public function handle(): void
    {
        $webinars = $this->webinarRepository->finishedWithUnpaidVisitors();

        /** @var Webinar $webinar */
        foreach ($webinars as $webinar) {
            $this->webinarRepository->setModel($webinar);
            $unpaidVisitors = $this->webinarRepository->getUnpaidVisitors();

            $userIdList = [];
            foreach ($unpaidVisitors as $unpaidVisitor) {
                $this->userRepository->setModel($unpaidVisitor);
                $this->userRepository->setWebinarChargeProcessing($webinar);
                $userIdList[] = $unpaidVisitor->id;
            }

            $unpaidVisitorsCount = $unpaidVisitors->count();

            if (!$unpaidVisitorsCount) {
                continue;
            }

            $account = $webinar->getOwner()->linkedAccounts()->first();
            if (!$account) {
                continue;
            }

            $amount = $unpaidVisitorsCount * Account::COST_PER_USER;
            $currencyCode = $this->currencyRepository->getDefaultCurrencyCode();
            $this->accountService->chargeAccount($webinar, $account, $amount, $currencyCode, $userIdList);
            \Log::debug('ChargeForUnpaidVisitors', [
                'webinar ID' => $webinar->id,
                'amount charged' => $amount,
                'unpaid visitors count' => $unpaidVisitorsCount,
                'unpaid visitors id' => $userIdList,
            ]);
        }
    }
}
