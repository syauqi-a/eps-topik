<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Relations\HasMany;

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
        'created_by',
    ];

    public function scopeQuestionTypes(): array
    {
        return [
            '듣기' => '듣기 (listening)',
            '읽기' => '읽기 (reading)',
        ];
    }

    public function scopeTags(): array
    {
        return [
            '빈칸' => '빈칸 (isi yg kosong)',
            '내용' => '내용 (isi pokok)',
            '관계있는' => '관계있는 (yg berkaitan)',
            '반대말' => '반대말 (antonim)',
            '비슷말' => '비슷말 (sinonim)',
            '그림/사진' => '그림/사진 (gambar)',
            '그래프' => '그래프 (grafik)',
            '표지판' => '표지판 (rambu-rambu)',
        ];
    }

    public function choices(): HasMany
    {
        return $this->hasMany(Choice::class);
    }
}
