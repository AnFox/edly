<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Product
 * @package App\Models
 *
 * @property int $id
 * @property int $account_id
 * @property int $type
 * @property string $name
 * @property string $description
 * @property int $currency_id
 * @property float $price
 *
 * @property-read Account $account
 * @property-read Currency $currency
 * @property-read Banner banner
 * @property-read Banner[] banners
 */
class Product extends Model
{
    const TYPE_BANNER = 1;
    const TYPE_BALANCE_REFILL = 2;

    protected $guarded = [];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function banner(): HasOne
    {
        return $this->hasOne(Banner::class);
    }

    public function banners(): HasMany
    {
        return $this->hasMany(Banner::class);
    }
}
