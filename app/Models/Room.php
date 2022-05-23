<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Class Room
 * @package App\Models
 *
 * @property integer $id
 * @property integer $type_id
 * @property boolean $is_published
 * @property integer $user_id
 * @property string $slug
 * @property string $name
 * @property string $description
 * @property string $waiting_text
 * @property string $post_webinar_text
 * @property string $video_id
 * @property string $video_src
 * @property string $layout
 * @property boolean $is_bot_assign_required
 * @property string $bot_url_telegram
 * @property string $bot_url_whatsapp
 * @property string $bot_url_viber
 * @property integer $duration_minutes
 * @property Carbon $scheduled_at
 * @property string $schedule_interval
 * @property boolean $request_record
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read User $owner
 * @property-read Banner[] $banners
 * @property-read Collection $bannersVisible
 * @property-read boolean $adminable
 * @property-read boolean $moderatable
 * @property-read string $fbPixel
 * @property-read boolean $access_allowed
 * @property-read bool $has_presentation
 * @property-read bool $pdf_filename
 * @property-read Media[] $slides
 * @property-read Media $current_slide
 * @property-read Webinar[] $webinars
 * @property-read Conversion[] $conversions
 */
class Room extends Model implements HasMedia
{
    use InteractsWithMedia;

    const MEDIA_ACCEPTABLE_MIME = [
        'image/jpeg',
        'image/png',
    ];

    const MEDIA_CONVERSION_WEB = 'web';

    const MEDIA_MIN_WIDTH  = 1250;
    const MEDIA_MIN_HEIGHT = 536;
    const MEDIA_MAX_WIDTH  = 3000;
    const MEDIA_MAX_HEIGHT = 1287;

    const MEDIA_BANNER_MIN_WIDTH  = 290;
    const MEDIA_BANNER_MIN_HEIGHT = 124;

    const MEDIA_COLLECTION_THUMBNAIL = 'webinarThumbnail';
    const MEDIA_COLLECTION_PDF = 'PDF';
    const MEDIA_COLLECTION_PRESENTATION_SLIDES = 'PresentationSlides';
    const MEDIA_COLLECTION_BANNER_IMAGE = 'BannerImage';

    const MEDIA_PDF_QIALITY_STANDARD = 'standard';
    const MEDIA_PDF_QIALITY_BETTER = 'better';
    const MEDIA_PDF_QIALITY_MAXIMUM = 'maximum';

    const MEDIA_PDF_QIALITY_MAP = [
        self::MEDIA_PDF_QIALITY_STANDARD => 80,
        self::MEDIA_PDF_QIALITY_BETTER => 90,
        self::MEDIA_PDF_QIALITY_MAXIMUM => 100,
    ];

    const TYPE_LIVE = 1;
    const TYPE_AUTO = 2;

    protected $guarded = [];

    protected $dates = [
        'scheduled_at',
    ];

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion(self::MEDIA_CONVERSION_WEB)
            ->width(self::MEDIA_MIN_WIDTH)
            ->nonQueued();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany
     */
    public function banners(): HasMany
    {
        return $this->hasMany(Banner::class);
    }

    /**
     * @return Collection
     */
    public function getBannersVisibleAttribute()
    {
        return $this->banners()->where('is_visible', true)->get();
    }



    public function getModeratableAttribute()
    {
        return Auth::user()->can('moderate', $this);
    }

    public function getAdminableAttribute()
    {
        return Auth::user()->can('administer', $this);
    }

    public function getAccessAllowedAttribute()
    {
        return Auth::user()->can('view-webinar', $this);
    }

    public function getFbPixelAttribute()
    {
        return $this->owner->linkedAccounts()->first()->getOption('fb_pixel');
    }

    public function conversions(): MorphMany
    {
        return $this->morphMany(Conversion::class, 'model');
    }

    public function getHasPresentationAttribute(): bool
    {
        return $this->hasMedia(Room::MEDIA_COLLECTION_PRESENTATION_SLIDES);
    }

    public function getPdfFilenameAttribute()
    {
        return $this->hasMedia(Room::MEDIA_COLLECTION_PDF)
            ? $this->getFirstMedia(Room::MEDIA_COLLECTION_PDF)->file_name
            : null;
    }

    public function slides()
    {
        return $this->media()->where('collection_name', Room::MEDIA_COLLECTION_PRESENTATION_SLIDES);
    }

    public function webinars(): HasMany
    {
        return $this->hasMany(Webinar::class);
    }

    public function commands(): HasMany
    {
        return $this->hasMany(Script::class);
    }

    public function images()
    {
        return $this->media()->where('collection_name', self::MEDIA_COLLECTION_BANNER_IMAGE);
    }

}
