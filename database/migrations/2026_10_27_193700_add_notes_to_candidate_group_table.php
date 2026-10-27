<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Notes belong to the group membership so every admin of that group shares them.
     */
    public function up(): void
    {
        Schema::table('candidate_group', function (Blueprint $table) {
            $table->string('notes', 1000)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidate_group', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
