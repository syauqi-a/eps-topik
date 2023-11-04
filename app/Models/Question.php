<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Relations\EmbedsMany;

class Question extends Model
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
        'content',
        'question_type',
        'tags',
        'question_image',
        'question_audio',
    ];

    public function multipleChoices(): EmbedsMany
    {
        return $this->embedsMany(
            MultipleChoice::class,
            relation: 'multiple_choices'
        );
    }
}
