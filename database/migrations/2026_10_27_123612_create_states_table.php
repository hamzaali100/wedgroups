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
        Schema::create('states', function (Blueprint $table) {
            $table->string('id', 7)->primary();
            $table->string('country_id', 3);
            $table->string('name', 100);

            $table->unique(['country_id', 'name']);
            $table->foreign('country_id')->references('id')->on('countries');
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->foreign('state_id')->references('id')->on('states');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropForeign(['state_id']);
        });

        Schema::dropIfExists('states');
    }
};
