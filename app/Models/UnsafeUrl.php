<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnsafeUrl extends Model
{
    protected $fillable = [
        'url_hash',
    ];

    /**
     * @inheritdoc
     */
    public function getUpdatedAtColumn()
    {
        return null;
    }
}
