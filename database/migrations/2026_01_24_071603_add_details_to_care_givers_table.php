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
        Schema::table('care_givers', function (Blueprint $table) {
            $table->string('role')->nullable()->after('email'); // Registered Nurse, Caregiver, etc.
            $table->string('license_number')->nullable()->after('role');
            $table->integer('experience_years')->nullable()->after('license_number');
            $table->text('address')->nullable()->after('experience_years');
            $table->string('state')->nullable()->after('address');
            $table->string('city')->nullable()->after('state');
            $table->string('gender')->nullable()->after('city');
            $table->date('date_of_birth')->nullable()->after('gender');
            $table->text('bio')->nullable()->after('date_of_birth');
            $table->string('profile_photo_path')->nullable()->after('bio');
            $table->string('cv_path')->nullable()->after('profile_photo_path');
            $table->string('verification_status')->default('pending')->after('cv_path'); // pending, verified, rejected
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('care_givers', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'license_number',
                'experience_years',
                'address',
                'state',
                'city',
                'gender',
                'date_of_birth',
                'bio',
                'profile_photo_path',
                'cv_path',
                'verification_status'
            ]);
        });
    }
};
