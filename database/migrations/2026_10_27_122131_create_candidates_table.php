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
        Schema::create('candidates', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained();

            $table->string('real_name', 150);
            $table->string('display_name', 100);
            $table->year('year_of_birth');
            $table->unsignedTinyInteger('month_of_birth');
            $table->enum('gender', ['male', 'female']);
            $table->unsignedSmallInteger('height');
            $table->enum('marital_status', [
                'never_married',
                'married',
                'divorced',
                'widowed',
                'nikah_break',
                'separated',
            ]);
            $table->unsignedSmallInteger('religion_id');
            $table->unsignedSmallInteger('sect_id')->nullable();
            $table->unsignedSmallInteger('ethnicity_id')->nullable();
            $table->string('other_details', 700)->nullable();
            $table->string('disabilities', 500)->nullable();
            $table->string('country_id', 3);
            $table->string('state_id', 7)->nullable();
            $table->string('city_id', 11)->nullable();
            $table->string('residence_details', 150)->nullable();
            $table->string('grew_up_in', 3)->nullable();
            $table->unsignedTinyInteger('total_siblings')->default(0);
            $table->string('siblings_description', 500)->nullable();
            $table->string('father_occupation', 150)->nullable();
            $table->string('mother_occupation', 150)->nullable();
            $table->string('family_details', 500)->nullable();
            $table->string('parent_contact_number', 30)->nullable();
            $table->string('ethnicity', 100)->nullable();
            $table->string('caste', 100)->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->enum('profile_visibility', ['visible', 'hidden'])->default('visible');
            $table->boolean('photos_blurred')->default(false);
            $table->enum('education_level', [
                'secondary_school',
                'high_school',
                'bachelors_degree',
                'masters',
                'phd',
            ]);
            $table->string('universities_names', 500)->nullable();
            $table->string('schools_names', 500)->nullable();
            $table->string('monthly_income', 300)->nullable();
            $table->unsignedSmallInteger('occupation_id')->nullable();
            $table->string('career_details', 500)->nullable();
            $table->string('linkedin', 255)->nullable();
            $table->boolean('willing_to_move_abroad')->nullable();
            $table->boolean('has_kids')->nullable();
            $table->enum('wants_children', ['yes', 'no', 'undecided'])->nullable();
            $table->string('phone_number', 30)->nullable();
            $table->string('contact_person', 150)->nullable();
            $table->string('contact_relationship', 50)->nullable();
            $table->string('contact_phone_number', 30)->nullable();
            $table->string('contact_email', 254)->nullable();
            $table->unsignedTinyInteger('partner_age_min')->nullable();
            $table->unsignedTinyInteger('partner_age_max')->nullable();
            $table->unsignedSmallInteger('partner_height_min')->nullable();
            $table->unsignedSmallInteger('partner_height_max')->nullable();
            $table->string('partner_requirement_details', 500)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
