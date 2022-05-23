<?php

namespace App\Models;

use App\Traits\OptionsTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Payment
 * @package App\Models
 *
 * @property int $order_id
 * @property int $payment_id
 * @property string $status
 * @property bool $paid
 * @property float $amount
 * @property string $payment_method
 * @property int $payment_method_id
 * @property string $description
 * @property string $metadata
 * @property Carbon $expires_at
 * @property bool $test
 * @property int $fail_reason_code
 * @property string $fail_reason_text
 * @property string $payment_data
 * @property Carbon $payment_ts
 * @property Carbon $synced_at
 *
 * @property-read Order $order
 */
class Payment extends Model
{
    use OptionsTrait;

    const PAYMENT_STATUS_DRAFT = 'draft';
    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_WAITING_FOR_CAPTURE = 'waiting_for_capture';
    const PAYMENT_STATUS_SUCCEEDED = 'succeeded';
    const PAYMENT_STATUS_CANCELED = 'canceled';
    const PAYMENT_STATUS_FAILED = 'failed';
    const PAYMENT_STATUS_REFUNDED = 'refunded';

    const PAYMENT_METHOD_CARD = 1;
    const PAYMENT_METHOD_DEBIT = 2;

    public $optionsField = 'metadata';

    protected $fillable = [
        'order_id',
        'payment_id',
        'status',
        'paid',
        'amount',
        'payment_method',
        'payment_method_id',
        'description',
        'metadata',
        'expires_at',
        'test',
        'fail_reason_code',
        'fail_reason_text',
        'payment_data',
        'payment_ts',
        'synced_at',
    ];

    protected $dates = [
        'payment_ts',
        'synced_at',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
