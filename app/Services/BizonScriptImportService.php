<?php


namespace App\Services;


use App\Contracts\Repositories\RoomRepository;
use App\Contracts\Repositories\ScriptRepository;
use App\Contracts\Services\ScriptImportService;
use App\Models\Room;
use App\Models\Script;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Log;

/**
 * Class BizonScriptImportService
 * @package App\Services
 */
class BizonScriptImportService implements ScriptImportService
{
    const ACTION_START_RECORD = 'startRecord';
    const ACTION_START_STREAM = 'goOnline';
    const ACTION_WEBINAR_LAYOUT = 'webinarLayout';
    const ACTION_SET_PRESENTATION_PAGE = 'presentationPage';
    const ACTION_POST_MESSAGE = 'post';
    const ACTION_POST_BANNER = 'banner';
    const ACTION_LOCK_CHAT = 'lockChat';
    const ACTION_SET_PRESENTATION_FILE_NAME = 'presentationName';
    const ACTION_STOP_RECORD = 'stopRecord';
    /**
     * @var RoomRepository
     */
    private $roomRepository;
    /**
     * @var ScriptRepository
     */
    private $scriptRepository;

    public function __construct(RoomRepository $roomRepository, ScriptRepository $scriptRepository)
    {
        $this->roomRepository = $roomRepository;
        $this->scriptRepository = $scriptRepository;
    }

    public function import(int $roomId, UploadedFile $file): ?Collection
    {
        $content = file_get_contents($file);
        $script = json_decode($content, true);

        // Check bizon-script-editor app format version
        if (!$script['app'] || $script['app'] !== 'bizon-script-editor' || $script['format'] !== '1.0') {
            throw new \Exception('Неподдерживаемый формат импорта. Поддерживатся только формат bizon-script-editor 1.0.');
        }

        // Clear current room script actions
        $this->scriptRepository->deleteRoomCommands($roomId);

        $events = $script['data'];

        /** @var Room $room */
        $room = $this->roomRepository->find($roomId)->getModel();

        foreach ($events as $event) {
            switch ($event['action']) {
                case self::ACTION_START_RECORD:
                    // Set action
                    $payload = $event['data'];
                    $this->scriptRepository->createRoomCommand($room->id, $event['timeshift'], Script::ACTION_START_RECORD, $payload);
                    break;

                case self::ACTION_START_STREAM:
                    // Set room stream URL
                    if (strstr($event['data']['url'], 'youtu')) {
                        $this->roomRepository->fill(['video_src' => $event['data']['url']]);
                        $this->roomRepository->save();
                    }

                    // Set action
                    $this->scriptRepository->createRoomCommand($room->id, $event['timeshift'], Script::ACTION_START_STREAM, $event['data']);
                    break;

                case self::ACTION_SET_PRESENTATION_PAGE:
                    // Set action
                    $payload = [
                        'page' => $event['data']
                    ];
                    $this->scriptRepository->createRoomCommand($room->id, $event['timeshift'], Script::ACTION_SET_PRESENTATION_PAGE, $payload);
                    break;

                case self::ACTION_WEBINAR_LAYOUT:
                    // Set action
                    switch ($event['data']) {
                        case 'video':
                            $layout = 'center';
                            break;
                        case 'lt':
                            $layout = 'top-left';
                            break;
                        case 'rt':
                            $layout = 'top-right';
                            break;
                        case 'rb':
                            $layout = 'bottom-right';
                            break;
                        default:
                            $layout = 'center';
                    }

                    $payload = [
                        'layout' => $layout
                    ];
                    $this->scriptRepository->createRoomCommand($room->id, $event['timeshift'], Script::ACTION_WEBINAR_LAYOUT, $payload);
                    break;

                case self::ACTION_POST_MESSAGE:
                    // Set action
                    $payload = [
                        'username' => $event['username'],
                        'message' => $event['message'],
                        'role' => $event['role'],
                    ];
                    $this->scriptRepository->createRoomCommand($room->id, $event['timeshift'], Script::ACTION_POST_MESSAGE, $payload);
                    break;

/*                case self::ACTION_POST_BANNER:
                    // Set room banner and post it to webinar chat

                    // Set action
                    $payload = $event['data'];
                    $this->scriptRepository->createRoomCommand($room->id, $event['timeshift'], Script::ACTION_POST_BANNER, $payload);
                    break;*/

                case self::ACTION_LOCK_CHAT:
                    // Set action
                    $payload = [];
                    $action = (int)$event['data'] === 1 ? Script::ACTION_CHAT_BLOCK : Script::ACTION_CHAT_UNBLOCK;
                    $this->scriptRepository->createRoomCommand($room->id, $event['timeshift'], $action, $payload);
                    break;

                case self::ACTION_STOP_RECORD:
                    // Set action
                    $payload = $event['data'];
                    $this->scriptRepository->createRoomCommand($room->id, $event['timeshift'], Script::ACTION_STOP_RECORD, $payload);

                    // Extend room duration if scenario duration is longer
                    $this->roomRepository->extendDurationIfNeeded($event['timeshift']);
                    break;
            }
        }

        return $this->scriptRepository->getRoomCommands($roomId);
    }
}
