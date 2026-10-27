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
        Schema::create('candidate_candidates_favorited', function (Blueprint $table) {
            $table->foreignId('candidate_id');
            $table->foreignId('favorited_candidate_id');
            $table->dateTime('created_at');

            $table->primary(['candidate_id', 'favorited_candidate_id']);
            $table->foreign('candidate_id')->references('user_id')->on('candidates');
            $table->foreign('favorited_candidate_id')->references('user_id')->on('candidates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_candidates_favorited');
    }
};
