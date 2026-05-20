<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'website',
        'tax_id',
        'registration_no',
        'founder_name',
        'founder_title',
        'founder_photo',
        'logo',
        'welcome_message',
    ];
}
