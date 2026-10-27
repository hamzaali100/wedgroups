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
        Schema::create('candidate_relative', function (Blueprint $table) {
            $table->foreignId('candidate_id');
            $table->foreignId('relative_id');
            $table->enum('relationship', [
                'mother',
                'father',
                'brother',
                'sister',
                'son',
                'daughter',
                'spouse',
                'guardian',
                'other',
            ]);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');

            $table->primary(['candidate_id', 'relative_id']);
            $table->foreign('candidate_id')->references('user_id')->on('candidates');
            $table->foreign('relative_id')->references('user_id')->on('relatives');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_relative');
    }
};
