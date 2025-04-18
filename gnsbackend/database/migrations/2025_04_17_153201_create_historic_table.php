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
        Schema::create('historic', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('user');
            $table->text('contact');
            $table->text('status');
            $table->text('name');
            $table->text('errorType')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historic');
    }
};
