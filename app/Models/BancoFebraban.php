<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BancoFebraban extends Model
{
    public $incrementing = true;
    public $guarded = ['id'];

    protected $casts = ['ativo' => 'boolean'];
}
