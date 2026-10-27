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
        Schema::create('ethnicities', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')->primary();
            $table->string('name', 100);
            $table->string('country_id', 3);

            $table->unique(['country_id', 'name']);
            $table->foreign('country_id')->references('id')->on('countries');
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->foreign('ethnicity_id')->references('id')->on('ethnicities');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropForeign(['ethnicity_id']);
        });

        Schema::dropIfExists('ethnicities');
    }
};
