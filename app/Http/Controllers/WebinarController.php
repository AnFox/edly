<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\CurrencyRepository;
use App\Contracts\Repositories\UserRepository;
use App\Contracts\Repositories\WebinarRepository;
use App\Http\Requests\WebinarGetPublicInfoRequest;
use App\Http\Resources\WebinarPublicResource;
use App\Http\Resources\WebinarResource;
use App\Jobs\ChargeAccountForUnpaidWebinarVisitors;
use App\Models\Account;
use App\Models\Setting;
use App\Models\Webinar;
use App\Notifications\UsersLimitReached;
use App\Services\AccountService;
use Bouncer;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class WebinarController
 * @package App\Http\Controllers
 */
class WebinarController extends Controller
{
    private WebinarRepository $webinarRepository;
    private UserRepository $userRepository;
    private AccountService $accountService;

    /**
     * WebinarController constructor.
     * @param WebinarRepository $webinarRepository
     * @param UserRepository $userRepository
     * @param AccountService $accountService
     */
    public function __construct(WebinarRepository $webinarRepository,
                                UserRepository $userRepository,
                                AccountService $accountService)
    {
        $this->webinarRepository = $webinarRepository;
        $this->userRepository = $userRepository;
        $this->accountService = $accountService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('view-webinar', $this->webinarRepository->getClass());
        $webinars = $this->userRepository->setModel(request()->user())
            ->getWebinarsVisitedExcludedOwnedPaginated();

        return WebinarResource::collection($webinars);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return WebinarResource
     * @throws AuthorizationException
     */
    public function show(int $id): WebinarResource
    {
        $user = request()->user();

        \Log::debug('USER', [$user]);
        /** @var Webinar $webinar */
        $webinar = $this->webinarRepository->find($id)->getModel();
        \Log::debug('WEBINAR', [$webinar]);
        if (
            // There is nothing to join to
            !$webinar

            // Visitors must not join to the draft webinar
            || (!$webinar->starts_at
                && !$user->can('moderate', $webinar))

            // Visitors must not join to the finished webinar
            || ($this->webinarRepository->isFinished()
                && !$user->can('moderate', $webinar))
        ) {
            throw new NotFoundHttpException();
        }

        $this->authorize('view-webinar', $webinar);

        if ($webinar && !$user->can('moderate', $webinar)) {
            if (Setting::isTrialTypeTime()) {
                $this->userRepository->setModel($user);
                $userIsPaid = $this->userRepository->isPaidInWebinar($webinar);
                if (!$userIsPaid) {
                    $this->accountService->processTrialUser($webinar, $user);
                }
            } else {
                $this->userRepository->setWebinarVisited($webinar);
                $unpaidVisitorsCount = $this->webinarRepository->getUnpaidVisitorsCount();
                $unpaidUserThreshold = config('app.unpaid_users_threshold');
                if ($this->webinarRepository->isStarted() && $unpaidVisitorsCount >= $unpaidUserThreshold) {
                    $this->dispatch(new ChargeAccountForUnpaidWebinarVisitors($webinar));
                }
            }
        }

        return new WebinarResource($webinar);
    }

    /**
     * Leave the specified resource.
     *
     * @param int $id
     * @return void
     * @throws AuthorizationException
     */
    public function leave(int $id): void
    {
        $webinar = $this->webinarRepository->find($id)->getModel();
        $this->authorize('view-webinar', $webinar);
        if ($webinar) {
            $this->userRepository->setModel(request()->user())
                ->setWebinarLeaved($webinar);
        }
    }

    /**
     * @param WebinarGetPublicInfoRequest $request
     * @return JsonResource
     */
    public function getPublicInfo(WebinarGetPublicInfoRequest $request)
    {
        $intendedUrl = $request->get('intendedUrl');
        $webinar = $this->webinarRepository->findByIntendedUrl($intendedUrl);
        $this->webinarRepository->setModel($webinar);

        if (
            // There is nothing to join to
            !$webinar

            // Visitors must not join to the draft webinar
            || (!$webinar->starts_at)

            // Visitors must not join to the finished webinar
            || ($this->webinarRepository->isFinished())
        ) {
            throw new NotFoundHttpException();
        }

        return $webinar ? new WebinarPublicResource($webinar) : new JsonResource(null);
    }

    public function isVisited(int $id)
    {
        $webinar = $this->webinarRepository->find($id)->getModel();

        // Webinar owner and moderators should always have webinar as visited
        if (request()->user()->can('moderate', $webinar)) {
            return response()->json(true);
        }

        if (
            // There is nothing to join to
            !$webinar

            // Visitors must not join to the draft webinar
            || (!$webinar->starts_at
                && !request()->user()->can('moderate', $webinar))

            // Visitors must not join to the finished webinar
            || ($this->webinarRepository->isFinished()
                && !request()->user()->can('moderate', $webinar))
        ) {
            throw new NotFoundHttpException();
        }

        $this->userRepository->setModel(request()->user());

        $result = (bool)$this->userRepository->getVisitedWebinars()->filter(function ($item) use ($webinar) {
            return $item->id === $webinar->id;
        })->count();

        return response()->json($result);
    }

}
