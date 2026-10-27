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
        Schema::create('group_admins', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained();
            $table->string('name', 150);
            $table->string('phone', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('description', 1000)->nullable();
            $table->dateTime('approval_date')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });

        Schema::table('candidate_group', function (Blueprint $table) {
            $table->foreign('approved_by')->references('user_id')->on('group_admins');
        });

        Schema::table('group_admin_candidate', function (Blueprint $table) {
            $table->foreign('group_admin_id')->references('user_id')->on('group_admins');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_admin_candidate', function (Blueprint $table) {
            $table->dropForeign(['group_admin_id']);
        });

        Schema::table('candidate_group', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
        });

        Schema::dropIfExists('group_admins');
    }
};
