<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Conversion
 * @package App\Models
 *
 * @property string $model_type
 * @property int $model_id
 * @property int $status
 * @property float $progress
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read MorphTo $model
 */
class Conversion extends Model
{
    const STATUS_NOT_PROCESSED = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_PROCESSED = 2;
    const STATUS_FAILED = 3;

    /**
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
