<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedEmailDomain extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'domain',
    ];
}
