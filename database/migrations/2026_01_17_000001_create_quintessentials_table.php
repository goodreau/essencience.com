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
        Schema::create('quintessentials', function (Blueprint $table) {
            $table->id();
            $table->integer('number')->unique()->comment('Number 1-10');
            $table->string('name')->comment('Name of the quintessential');
            $table->string('slug')->unique()->comment('URL-friendly slug');
            $table->text('description')->nullable()->comment('Brief description');
            $table->longText('content')->nullable()->comment('Full content/essay');
            $table->string('icon')->nullable()->comment('Icon or symbol');
            $table->string('color')->nullable()->comment('Associated color');
            $table->integer('order_by')->default(0)->comment('Display order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quintessentials');
    }
};
