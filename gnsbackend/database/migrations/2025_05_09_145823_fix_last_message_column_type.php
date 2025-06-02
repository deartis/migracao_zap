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
        // Solução direta para MySQL usando SQL nativo
        DB::statement('ALTER TABLE users MODIFY COLUMN lastMessage TEXT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter para o tipo original
        DB::statement('ALTER TABLE users MODIFY COLUMN lastMessage TIMESTAMP NULL');
    }
};
