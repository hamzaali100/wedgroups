<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->enum('review_status', ['pending', 'approved', 'rejected', 'suspended'])->default('approved');
            $table->string('rejection_reason', 500)->nullable();
        });

        Schema::table('candidate_photos', function (Blueprint $table) {
            $table->enum('review_status', ['pending', 'approved'])->default('pending');
        });
    }

    public function down(): void
    {
        Schema::table('candidate_photos', function (Blueprint $table) {
            $table->dropColumn('review_status');
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn(['review_status', 'rejection_reason']);
        });
    }
};
