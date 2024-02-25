<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContainerLog extends Model
{
    protected $fillable = ['container_id', 'log', 'timestamp'];
}
