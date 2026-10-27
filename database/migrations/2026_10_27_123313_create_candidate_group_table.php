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
        Schema::create('candidate_group', function (Blueprint $table) {
            $table->foreignId('group_id');
            $table->foreignId('candidate_id');
            $table->enum('status', ['pending', 'active', 'inactive', 'rejected']);
            $table->dateTime('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable();
            $table->dateTime('group_terms_accepted_at')->nullable();
            $table->string('group_terms_version', 20)->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');

            $table->primary(['group_id', 'candidate_id']);
            $table->foreign('candidate_id')->references('user_id')->on('candidates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_group');
    }
};
