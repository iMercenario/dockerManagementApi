<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContainerNetwork extends Model
{
    protected $fillable = ['container_id', 'network_id', 'ip_address'];
}
