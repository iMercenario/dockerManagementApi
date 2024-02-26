<?php

namespace App\Models;


use MongoDB\Laravel\Eloquent\Model;

class DockerCommand extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'docker_dataset.commands';

    protected $fillable = ['input', 'output'];
}
