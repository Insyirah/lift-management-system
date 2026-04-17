<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['super_admin', 'admin', 'inspector']);
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('phone')->nullable();
            $table->string('cert_number')->nullable()->comment('Inspector certification number');
            $table->string('cert_expiry')->nullable()->comment('Certification expiry date');
            $table->string('signature_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
