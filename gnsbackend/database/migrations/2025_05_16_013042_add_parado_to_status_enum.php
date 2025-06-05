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
        Schema::table('envio_progresso', function (Blueprint $table) {
            DB::statement("ALTER TABLE envio_progresso MODIFY COLUMN status ENUM('em_andamento', 'finalizado', 'parado') NOT NULL DEFAULT 'parado'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE envio_progresso MODIFY COLUMN status ENUM('em_andamento', 'finalizado') NOT NULL DEFAULT 'em_andamento'");
    }
};
