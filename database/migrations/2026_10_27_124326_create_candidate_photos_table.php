<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('candidate_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id');
            $table->string('photo', 500);
            $table->year('photo_year');
            $table->boolean('is_main')->default(false);
            $table->unsignedTinyInteger('sort_order');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');

            $table->unique(['candidate_id', 'sort_order']);
            $table->foreign('candidate_id')->references('user_id')->on('candidates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_photos');
    }
};
