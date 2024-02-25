<?php

// app/Models/Container.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Container extends Model
{
    protected $fillable = ['docker_id', 'name', 'image', 'creation_date'];
}

