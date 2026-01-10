<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('serial_number')->unique();
            $table->text('certificate');
            $table->text('public_key')->nullable();
            $table->string('subject');
            $table->string('issuer');
            $table->timestamp('valid_from');
            $table->timestamp('valid_until');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_revoked')->default(false);
            $table->timestamp('revoked_at')->nullable();
            $table->string('revocation_reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index('serial_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_certificates');
    }
};
