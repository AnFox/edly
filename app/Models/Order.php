<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * Class Order
 * @package App\Models
 *
 * @property int $id
 * @property int $account_id
 * @property int $status_id
 * @property int $user_id
 * @property int $product_id
 * @property int $webinar_id
 * @property float $amount
 * @property string $currency_code
 * @property string $description
 *
 * @property-read Account $account
 * @property-read Payment $payment
 * @property-read Product $product
 * @property-read User $user
 * @property-read Webinar $webinar
 */
class Order extends Model
{
    protected $fillable = [
        'account_id',
        'user_id',
        'product_id',
        'webinar_id',
        'amount',
        'currency_code',
        'description',
    ];

    protected $with = [
        'status',
    ];

    public function scopeStartDate(Builder $query, $date): Builder
    {
        if (!$date) {
            return $query;
        }

        return $query->where('created_at', '>=', Carbon::parse($date));
    }

    public function scopeEndDate(Builder $query, $date): Builder
    {
        if (!$date) {
            return $query;
        }

        return $query->where('created_at', '<=', Carbon::parse($date));
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function webinar(): BelongsTo
    {
        return $this->belongsTo(Webinar::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class);
    }

}
