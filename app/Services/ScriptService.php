<?php


namespace App\Services;


use App\Contracts\Repositories\ChatMessageRepository;
use App\Contracts\Repositories\RoomRepository;
use App\Contracts\Repositories\ScriptRepository;
use App\Contracts\Repositories\WebinarRepository;
use App\Events\WebinarLayoutChange;
use App\Events\WebinarScriptCommand;
use App\Events\WebinarSlideOpen;
use App\Models\Script;
use App\Models\Webinar;
use Carbon\Carbon;
use Exception;
use Log;

class ScriptService
{
    /**
     * @var ScriptRepository
     */
    private $scriptRepository;
    /**
     * @var ChatMessageRepository
     */
    private $chatMessageRepository;
    /**
     * @var WebinarRepository
     */
    private $webinarRepository;
    /**
     * @var RoomRepository
     */
    private $roomRepository;

    public function __construct(ScriptRepository $scriptRepository,
                                ChatMessageRepository $chatMessageRepository,
                                WebinarRepository $webinarRepository, RoomRepository $roomRepository)
    {
        $this->scriptRepository = $scriptRepository;
        $this->chatMessageRepository = $chatMessageRepository;
        $this->webinarRepository = $webinarRepository;
        $this->roomRepository = $roomRepository;
    }

    public function record(): void
    {
        // @todo: Implement me
    }

    /**
     * @param Webinar $webinar
     * @throws Exception
     */
    public function play(Webinar $webinar): void
    {
        $this->webinarRepository->setModel($webinar);
        $this->webinarRepository->setStarted();

        $commands = $this->scriptRepository->getRoomCommands($webinar->room->id);
        foreach ($commands as $command) {
            try {
                $webinarStartTime = Carbon::instance($webinar->starts_at);
                $commandTime = $webinarStartTime->addMilliseconds($command->timeshift);

                if (now() < $commandTime) {
                    $diffInMicroSeconds = $commandTime->diffInMicroseconds();
                    usleep($diffInMicroSeconds);
                    $this->runCommand($webinar, $command);
                } else {
                    $this->runCommand($webinar, $command);
                }
            } catch (Exception $e) {
                Log::error('Failed to execute script command [ID]: ' . $command->id . ' [action]: '
                    . $command->action . ' for auto webinar ID: ' . $webinar->id);
            }
        }
    }

    /**
     * @param Webinar $webinar
     * @param $command
     * @return void
     * @throws Exception
     */
    protected function runCommand(Webinar $webinar, $command): void
    {
        event(new WebinarScriptCommand($webinar, $command));

        switch ($command->action) {
            case Script::ACTION_WEBINAR_LAYOUT:
                $data = json_decode($command->payload);
                $this->webinarRepository->setLayout($data->layout);
                event(new WebinarLayoutChange($webinar, $data->layout));
                break;

            case Script::ACTION_SET_PRESENTATION_PAGE:
                $data = json_decode($command->payload);
                $slide = $webinar->room->slides()->where('name', $data->page)->first();
                if ($slide) {
                    $this->webinarRepository->setCurrentSlide($slide);
                    event(new WebinarSlideOpen($webinar, $slide));
                }
                break;

            case Script::ACTION_CHAT_BLOCK:
                $this->webinarRepository->setModel($webinar);
                $this->webinarRepository->blockChat();
                break;

            case Script::ACTION_CHAT_UNBLOCK:
                $this->webinarRepository->setModel($webinar);
                $this->webinarRepository->unblockChat();
                break;

            case Script::ACTION_POST_BANNER:
                if (!$webinar->chat) {
                    break;
                }

                $data = json_decode($command->payload);
//                $timestamp = now()->toDateTimeString();
//                Log::debug("{$timestamp} Banner ID: {$data->id}");

                $this->chatMessageRepository->create([
                    'chat_id' => $webinar->chat->id,
                    'sender_user_id' => $webinar->room->owner->id,
                    'is_fake' => false,
                    'banner_id' => $data->id,
                    'message' => ''
                ]);
                break;

            case Script::ACTION_POST_MESSAGE:
                if (!$webinar->chat) {
                    break;
                }

                $data = json_decode($command->payload);
//                $timestamp = now()->toDateTimeString();
//                Log::debug("{$timestamp} {$data->username}: {$data->message}");

                $this->chatMessageRepository->create([
                    'chat_id' => $webinar->chat->id,
                    'sender_user_id' => $data->role === 'admin' ? $webinar->room->owner->id : null,
                    'is_fake' => $data->role === 'guest',
                    'fake_sender_user_id' => rand(1000,10000000),
                    'fake_sender_user_name' => $data->username,
                    'message' => $data->message
                ]);
                break;

//            case Script::ACTION_START_RECORD:
//                $this->webinarRepository->setStarted();
//                break;

            case Script::ACTION_STOP_RECORD:
                $this->webinarRepository->setFinished();
                break;
        }
    }
}
