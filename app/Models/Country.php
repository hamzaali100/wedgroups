<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['id', 'name', 'nationality'])]
class Country extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;
}
