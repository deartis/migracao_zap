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
        Schema::create('envio_progresso', function (Blueprint $table){
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->integer('total')->default(0);
            $table->integer('enviadas')->default(0);
            $table->integer('totalLote')->default(0);
            $table->boolean('visto')->default(0);
            $table->string('Erro')->nullable();
            $table->enum('status', ['em_andamento', 'finalizado'])->default('em_andamento');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('envio_progresso');
    }
};
