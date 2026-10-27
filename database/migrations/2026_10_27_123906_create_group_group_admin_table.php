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
        Schema::create('group_group_admin', function (Blueprint $table) {
            $table->foreignId('group_id');
            $table->foreignId('user_id');
            $table->boolean('is_primary')->default(false);
            $table->dateTime('approval_date')->nullable();
            $table->foreignId('approved_by')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');

            $table->primary(['group_id', 'user_id']);
            $table->foreign('group_id')->references('id')->on('groups');
            $table->foreign('user_id')->references('user_id')->on('group_admins');
            $table->foreign('approved_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_group_admin');
    }
};
