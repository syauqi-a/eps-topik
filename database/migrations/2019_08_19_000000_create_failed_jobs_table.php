<?php

use MongoDB\Laravel\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('failed_jobs', function (Blueprint $collection) {
            $collection->id();
            $collection->string('uuid')->unique();
            $collection->text('connection');
            $collection->text('queue');
            $collection->longText('payload');
            $collection->longText('exception');
            $collection->timestamp('failed_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_jobs');
    }
};
