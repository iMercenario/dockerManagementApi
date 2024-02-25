<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranslationModel extends Model
{
    protected $fillable = ['name', 'path', 'is_active', 'description'];
}
