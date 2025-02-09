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
        Schema::create('selenium_drivers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('duration')->nullable();
            $table->string('host');
            $table->string('port');
            $table->string('browser')->default('chrome');
            $table->string('version')->nullable()->default('latest');
            $table->string('status')->default('active');
            $table->timestamp('last_usage')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('selenium_drivers');
    }
};
