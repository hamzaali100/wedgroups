<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['id', 'country_id', 'name'])]
class State extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;
}
