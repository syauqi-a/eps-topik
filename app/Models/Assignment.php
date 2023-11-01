<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsToMany;
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
        'is_unlimited',
        'deadlines',
        'timezone',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'deadlines.starts' => 'datetime',
        'deadlines.ends' => 'datetime',
        'is_unlimited' => 'boolean',
    ];

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(
            Course::class,
            null,
            'assignment_ids',
            'course_ids',
        );
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            null,
            'assignment_ids',
            'student_ids',
        );
    }
}
