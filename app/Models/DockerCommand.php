<?php

namespace App\Models;


use MongoDB\Laravel\Eloquent\Model;

class DockerCommand extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'commands';

    protected $fillable = ['input', 'output'];
}
