<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Currency
 * @package App\Models
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $sign
 */
class Currency extends Model
{
    const RUB = 1;
    const USD = 2;
}
