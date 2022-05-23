<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderStatus extends Model
{
    public const ORDER_STATUS_DRAFT = 1;
    public const ORDER_STATUS_PENDING = 2;
    public const ORDER_STATUS_PAID = 3;
    public const ORDER_STATUS_CANCELED = 4;

    protected $fillable = [
        'id',
        'name',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'status_id');
    }
}
