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
        Schema::create('relatives', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained();
            $table->string('name', 150);
            $table->string('phone', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('email', 254)->nullable();
            $table->enum('status', ['pending', 'active', 'revoked']);
            $table->dateTime('approval_date')->nullable();
            $table->foreignId('approved_by')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');

            $table->foreign('approved_by')->references('user_id')->on('group_admins');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relatives');
    }
};
