<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['id', 'state_id', 'name'])]
class City extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;
}
