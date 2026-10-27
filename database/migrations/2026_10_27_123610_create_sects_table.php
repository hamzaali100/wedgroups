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
        Schema::create('sects', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('religion_id');
            $table->string('name', 100);

            $table->unique(['religion_id', 'name']);
            $table->foreign('religion_id')->references('id')->on('religions');
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->foreign('sect_id')->references('id')->on('sects');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropForeign(['sect_id']);
        });

        Schema::dropIfExists('sects');
    }
};
