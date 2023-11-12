<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Relations\BelongsToMany;

class Course extends Model
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
        'is_private',
        'created_by',
        'teacher_ids',
        'students_ids',
        'course_key',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_private' => 'boolean',
    ];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            null,
            'student_course_ids',
            'student_ids',
        );
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            null,
            'teacher_course_ids',
            'teacher_ids',
        );
    }

    public function assignments(): BelongsToMany
    {
        return $this->belongsToMany(
            Assignment::class,
            null,
            'course_ids',
            'assignment_ids',
        );
    }
}
