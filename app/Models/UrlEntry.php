<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $url
 * @property string $url_hash
 * @property integer $url_short_hash
 * @property string $short_code
 * @property DateTime $valid_until
 */
class UrlEntry extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'url',
        'short_code',
        'valid_until',
    ];

    protected $visible = [
        'url',
        'short_code',
        'valid_until',
    ];

    protected $casts = [
        'valid_until' => 'datetime',
    ];
}
