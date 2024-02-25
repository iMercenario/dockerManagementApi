<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContainerOperation extends Model
{
    protected $fillable = ['container_id', 'operation_type', 'details', 'timestamp'];
}
