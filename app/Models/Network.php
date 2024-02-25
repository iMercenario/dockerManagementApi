<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Network extends Model
{
    protected $fillable = ['docker_network_id', 'name', 'driver', 'scope'];
}
