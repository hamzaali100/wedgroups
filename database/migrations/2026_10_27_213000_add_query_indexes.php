<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Indexes for the lookups that load profiles, memberships, and saved lists.
     * Foreign keys already index their own columns; these match the filters and sorts on top of them.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index(['role', 'name'], 'users_role_name_index');
            $table->index('created_at', 'users_created_at_index');
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->index(
                ['review_status', 'profile_status', 'profile_visibility', 'updated_at'],
                'candidates_browse_index',
            );
            $table->index(['gender', 'year_of_birth'], 'candidates_gender_birth_year_index');
            $table->index('display_name', 'candidates_display_name_index');
            $table->index('created_at', 'candidates_created_at_index');
        });

        Schema::table('candidate_group', function (Blueprint $table) {
            $table->index(['candidate_id', 'status', 'group_id'], 'candidate_group_member_status_index');
            $table->index(['approved_by', 'approved_at'], 'candidate_group_approved_by_at_index');
        });

        Schema::table('candidate_photos', function (Blueprint $table) {
            $table->index(
                ['candidate_id', 'review_status', 'sort_order'],
                'candidate_photos_candidate_review_sort_index',
            );
        });

        Schema::table('candidate_candidates_liked', function (Blueprint $table) {
            $table->index(['liked_candidate_id', 'candidate_id'], 'candidate_likes_reverse_index');
            $table->index('created_at', 'candidate_likes_created_at_index');
        });

        Schema::table('candidate_candidates_favorited', function (Blueprint $table) {
            $table->index('created_at', 'candidate_favorites_created_at_index');
        });

        Schema::table('candidate_candidates_passed', function (Blueprint $table) {
            $table->index('created_at', 'candidate_passes_created_at_index');
        });

        Schema::table('group_group_admin', function (Blueprint $table) {
            $table->index(['user_id', 'group_id'], 'group_group_admin_user_group_index');
            $table->index('created_at', 'group_group_admin_created_at_index');
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->index('name', 'groups_name_index');
        });

        Schema::table('group_admins', function (Blueprint $table) {
            $table->index('name', 'group_admins_name_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_admins', function (Blueprint $table) {
            $table->dropIndex('group_admins_name_index');
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropIndex('groups_name_index');
        });

        Schema::table('group_group_admin', function (Blueprint $table) {
            $table->dropIndex('group_group_admin_created_at_index');
            $table->dropIndex('group_group_admin_user_group_index');
        });

        Schema::table('candidate_candidates_passed', function (Blueprint $table) {
            $table->dropIndex('candidate_passes_created_at_index');
        });

        Schema::table('candidate_candidates_favorited', function (Blueprint $table) {
            $table->dropIndex('candidate_favorites_created_at_index');
        });

        Schema::table('candidate_candidates_liked', function (Blueprint $table) {
            $table->dropIndex('candidate_likes_created_at_index');
            $table->dropIndex('candidate_likes_reverse_index');
        });

        Schema::table('candidate_photos', function (Blueprint $table) {
            $table->dropIndex('candidate_photos_candidate_review_sort_index');
        });

        Schema::table('candidate_group', function (Blueprint $table) {
            $table->dropIndex('candidate_group_approved_by_at_index');
            $table->dropIndex('candidate_group_member_status_index');
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->dropIndex('candidates_created_at_index');
            $table->dropIndex('candidates_display_name_index');
            $table->dropIndex('candidates_gender_birth_year_index');
            $table->dropIndex('candidates_browse_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_created_at_index');
            $table->dropIndex('users_role_name_index');
        });
    }
};
