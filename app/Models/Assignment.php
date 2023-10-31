<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Assignment extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $primaryKey = '_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'created_by',
        'deadlines',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'deadlines.starts' => 'datetime',
        'deadlines.ends' => 'datetime',
    ];
}
