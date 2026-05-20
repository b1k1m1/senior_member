<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeBearer extends Model
{
    protected $fillable = [
        'position',
        'name',
        'phone',
        'display_order',
    ];
}
