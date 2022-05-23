<?php

namespace App\Repositories;

use App\Contracts\Repositories\RoomRepository;
use App\Contracts\Repositories\UserRepository;
use App\Contracts\Repositories\WebinarRepository;
use App\Helpers\Parser;
use App\Http\Requests\Admin\RoomDuplicateRequest;
use App\Models\Banner;
use App\Models\Room;
use App\Models\Webinar;
use Exception;
use Illuminate\Support\Arr;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class RoomRepositoryEloquent
 * @package App\Repositories
 */
class RoomRepositoryEloquent extends BaseRepositoryEloquent implements RoomRepository
{
    /** @var Room */
    protected $model;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var WebinarRepository
     */
    private $webinarRepository;

    /**
     * RoomRepositoryEloquent constructor.
     * @param Room $model
     * @param UserRepository $userRepository
     * @param WebinarRepository $webinarRepository
     */
    public function __construct(Room $model, UserRepository $userRepository, WebinarRepository $webinarRepository)
    {
        $this->setModel($model);
        $this->userRepository = $userRepository;
        $this->webinarRepository = $webinarRepository;
    }

    /**
     * @param array $attributes
     * @return $this|mixed
     */
    public function create(array $attributes)
    {
        $model = $this->model->create($attributes);

        return $model->fresh();
    }

    /**
     * @param array $attributes
     * @return $this|mixed
     */
    public function fill(array $attributes)
    {
        if ($attributes['video_src']) {
            $attributes['video_id'] = Parser::getYouTubeEmbedVideoId($attributes['video_src']);
        }

        return parent::fill($attributes);
    }

    /**
     * @param int $authorId
     * @return mixed
     */
    public function findByAuthor(int $authorId)
    {
        $builder = QueryBuilder::for($this->model->where('user_id', $authorId))
            ->allowedFilters([
                AllowedFilter::scope('type'),
            ]);
        $this->setBuilder($builder);

        return $this;
    }

    /**
     * @param Room $source
     * @param RoomDuplicateRequest $request
     * @return mixed
     */
    public function duplicate($source, RoomDuplicateRequest $request)
    {
        /** @var Room $source */
        $attributesSrc = $source->toArray();
        $attributesDst = $request->validated();

        $attributes = array_merge(Arr::except($attributesSrc, ['id', 'scheduled_at']), $attributesDst);

        /** @var Room $destination */
        $destination = $this->create($attributes);

        // Duplicate related entities
        $banners = $source->banners;
        foreach ($banners as $banner) {
            $attributes = Arr::except($banner->toArray(), ['id']);
            $attributes['room_id'] = $destination->id;
            Banner::create($attributes);
        }

        // Duplicate thumbnail
        $this->duplicateMediaCollection($source, $destination, Room::MEDIA_COLLECTION_THUMBNAIL);

        // Duplicate presentation
        $this->duplicateMediaCollection($source, $destination, Room::MEDIA_COLLECTION_PDF);

        // Duplicate slides
        $this->duplicateMediaCollection($source, $destination, Room::MEDIA_COLLECTION_PRESENTATION_SLIDES);

        // Duplicate script commands
        foreach ($source->commands as $command) {
            $attributes = Arr::except($command->toArray(), ['id']);

            $destination->commands()->create($attributes);
        }

        return $destination;
    }

    /**
     * @return bool
     */
    public function hasFinishedWebinars(): bool
    {
        /** @var Room $room */
        $room = $this->getModel();
        $webinars = $room->webinars;

        foreach ($webinars as $webinar) {
            $this->webinarRepository->setModel($webinar);
            return $webinar->finished_at || ($this->webinarRepository->isFinishTimePastDue() && !$webinar->visitorsOnline);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function hasBroadcastingWebinars(): bool
    {
        /** @var Webinar $webinar */
        $webinar = $this->webinarRepository->getCurrentWebinar($this->getModel()->id);

        return $webinar && ($webinar->is_started || $webinar->starts_at->lte(now()));
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function save(): bool
    {
        return parent::save();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function delete(): bool
    {
        if ($this->hasFinishedWebinars()) {
            throw new UnprocessableEntityHttpException('Нельзя удалять комнату в которой есть завершенный вебинар.');
        }

        if ($this->hasBroadcastingWebinars()) {
            throw new UnprocessableEntityHttpException('Нельзя удалять комнату в которой есть идущий вебинар.');
        }

        return parent::delete();
    }

    /**
     * @return mixed
     */
    public function getAccount()
    {
        return $this->userRepository->setModel($this->model->owner)->getFirstLinkedAccount();
    }

    public function setWaitingText($waitingText)
    {
        $this->model->waiting_text = $waitingText;
        $this->model->save();
    }

    /**
     * @param Room $source Room source
     * @param Room $destination Room destination
     * @param string $mediaCollection
     */
    protected function duplicateMediaCollection(Room $source, Room $destination, string $mediaCollection): void
    {
        $mediaItems = $source->getMedia($mediaCollection);

        foreach ($mediaItems as $media) {
            try {
                $destination
                    ->copyMedia($media->getPath())
                    ->toMediaCollection($mediaCollection);
            } catch (FileDoesNotExist $e) {
                \Log::debug('Duplicate webinar thumbnail failed: FileDoesNotExist', [$destination, $e]);
            } catch (FileIsTooBig $e) {
                \Log::debug('Duplicate webinar thumbnail failed: FileIsTooBig', [$destination, $e]);
            }
        }
    }

    public function slides()
    {
        return $this->model->slides()->jsonPaginate();
    }

    public function getSlide(int $id)
    {
        return $this->model->slides()->where('id', $id)->first();
    }

    public function getByType(int $typeId)
    {
        return $this->model->where('type_id', $typeId)->get();
    }

    public function extendDurationIfNeeded(int $durationMilliseconds)
    {
        $scriptDurationMinutes = $durationMilliseconds / 1000 / 60;
        $room = $this->getModel();
        if ($scriptDurationMinutes > $room->duration_minutes) {
            $room->duration_minutes = $scriptDurationMinutes;
            $room->save();
        }
    }
}
