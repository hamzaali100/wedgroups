<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['id', 'name'])]
class Religion extends Model
{
    public $incrementing = false;

    public $timestamps = false;
}
