<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MultipleChoice extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'multiple_choice';
    protected $primaryKey = '_id';
}
