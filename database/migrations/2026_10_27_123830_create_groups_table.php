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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('description', 150);
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->boolean('male_pics_allowed')->default(true);
            $table->boolean('female_pics_allowed')->default(false);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });

        Schema::table('candidate_group', function (Blueprint $table) {
            $table->foreign('group_id')->references('id')->on('groups');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidate_group', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
        });

        Schema::dropIfExists('groups');
    }
};
