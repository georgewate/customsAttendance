<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance_reasons', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['late_arrival', 'early_clock_out']);
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category', 'active']);
        });

        $defaults = [
            'Medical Appointment',
            'Personal Emergency',
            'Family Commitment',
            'Transport Issues',
            'Work Assignment Offsite',
            'Official Leave Approval',
            'Training / Workshop',
            'Weather Conditions',
            'Internet / System Issues',
            'Other',
        ];

        $now = now();
        $rows = [];

        foreach (['late_arrival', 'early_clock_out'] as $category) {
            foreach ($defaults as $name) {
                $rows[] = [
                    'category' => $category,
                    'name' => $name,
                    'description' => null,
                    'active' => true,
                    'created_by' => null,
                    'updated_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('attendance_reasons')->insert($rows);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_reasons');
    }
};
