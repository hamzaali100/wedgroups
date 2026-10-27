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
        Schema::create('countries', function (Blueprint $table) {
            $table->string('id', 3)->primary();
            $table->string('name', 100)->unique();
            $table->string('nationality', 100);
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->foreign('country_id')->references('id')->on('countries');
            $table->foreign('grew_up_in')->references('id')->on('countries');
        });

        Schema::table('candidate_nationality', function (Blueprint $table) {
            $table->foreign('nationality_id')->references('id')->on('countries');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidate_nationality', function (Blueprint $table) {
            $table->dropForeign(['nationality_id']);
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->dropForeign(['grew_up_in']);
            $table->dropForeign(['country_id']);
        });

        Schema::dropIfExists('countries');
    }
};
