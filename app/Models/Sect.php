<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['religion_id', 'name'])]
class Sect extends Model
{
    public $timestamps = false;
}
