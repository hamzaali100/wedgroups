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
        Schema::create('group_admin_candidate', function (Blueprint $table) {
            $table->foreignId('group_admin_id');
            $table->foreignId('candidate_id');
            $table->string('notes', 1000)->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');

            $table->primary(['group_admin_id', 'candidate_id']);
            $table->foreign('candidate_id')->references('user_id')->on('candidates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_admin_candidate');
    }
};
