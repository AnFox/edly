<?php

namespace App\Jobs;

use App\Models\Conversion;
use App\Models\Room;
use App\Models\Webinar;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\PdfToImage\Pdf;

/**
 * Class ProcessRoomPdf
 * @package App\Jobs
 */
class ProcessRoomPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 0;

    /**
     * @var Room
     */
    private $room;

    /**
     * @var string
     */
    private $quality;

    /**
     * @var Media
     */
    private $media;

    /**
     * Create a new job instance.
     *
     * @param Webinar $room
     * @param Media $media
     * @param string $quality
     */
    public function __construct(Room $room, Media $media, string $quality)
    {
        $this->room = $room;
        $this->quality = $quality;
        $this->media = $media;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        set_time_limit(0);

        $conversion = $this->room->conversions->last();
        $conversion->status = Conversion::STATUS_PROCESSING;
        $conversion->save();

        try {
            $file = $this->media->getPath();
            $pdf = new Pdf($file);
            $numberOfPages = $pdf->getNumberOfPages();

            $this->room->clearMediaCollection(Room::MEDIA_COLLECTION_PRESENTATION_SLIDES);

            for ($i = 1; $i <= $numberOfPages; $i++) {
                $fileName = $i . '.jpg';
                $compressionQuality = Room::MEDIA_PDF_QIALITY_MAP[$this->quality];

                $pdf->setPage($i)
                    ->setCompressionQuality($compressionQuality)
                    ->saveImage(storage_path($fileName));

                $this->room->addMedia(storage_path($fileName))
                    ->toMediaCollection(Room::MEDIA_COLLECTION_PRESENTATION_SLIDES);

                $conversion->progress = $i / $numberOfPages * 100;
                $conversion->save();
            }

            $conversion->status = Conversion::STATUS_PROCESSED;
            $conversion->save();
        } catch (\Exception $e) {
            $conversion->status = Conversion::STATUS_FAILED;
            $conversion->save();
        }
    }
}
