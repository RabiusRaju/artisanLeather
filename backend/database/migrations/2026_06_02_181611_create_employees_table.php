<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();

            // Personal
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('photo')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male','female'])->nullable();
            $table->string('nationality')->default('Omani');
            $table->string('national_id')->nullable();       // Civil ID
            $table->string('passport_number')->nullable();
            $table->enum('visa_type', ['citizen','employment_visa','dependent_visa','visit_visa','other'])->nullable();
            $table->date('visa_expiry')->nullable();

            // Contact
            $table->string('phone');
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('governorate')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();

            // Employment
            $table->string('job_title');
            $table->enum('employment_type', ['full_time','part_time','contract','freelance'])->default('full_time');
            $table->date('date_hired');
            $table->date('date_terminated')->nullable();
            $table->decimal('monthly_salary_omr', 10, 3)->nullable();
            $table->decimal('hourly_rate_omr', 8, 3)->nullable();

            // Skills (leather-specific)
            $table->json('skills')->nullable();   // ['cutting','stitching','finishing','monogramming','design']

            // Status
            $table->enum('status', ['active','probation','on_leave','terminated'])->default('active');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('employees'); }
};
