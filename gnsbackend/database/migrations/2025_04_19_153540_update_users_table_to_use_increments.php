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
        // Primeiro criar uma tabela temporária com a estrutura desejada
        Schema::create('users_new', function (Blueprint $table) {
            $table->id(); // ID incremental
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            // Adicione outros campos que você precisa aqui
            $table->string('number')->nullable();
            $table->integer('msgLimit')->default(0);
            $table->integer('sendedMsg')->default(0);
            $table->string('role')->default('user');
            $table->boolean('enabled')->default(true);
            $table->boolean('rightNumber')->default(false);
            $table->timestamp('lastMessage')->nullable();
        });

        // Se você tiver dados que deseja preservar, copie-os
        // Isso vai funcionar apenas se não houver muitos dados
        // Se tiver muitos dados, considere uma abordagem mais robusta
        DB::statement('INSERT INTO users_new (name, email, email_verified_at, password, remember_token, created_at, updated_at, number, msgLimit, sendedMsg, role, enabled, rightNumber, lastMessage)
                      SELECT name, email, email_verified_at, password, remember_token, created_at, updated_at, number, msgLimit, sendedMsg, role, enabled, rightNumber, lastMessage FROM users');

        // Descarte a tabela antiga
        Schema::drop('users');

        // Renomeie a nova tabela para o nome correto
        Schema::rename('users_new', 'users');

        // Corrija a chave estrangeira na tabela sessions
        Schema::table('sessions', function (Blueprint $table) {
            // Primeiro remova a chave estrangeira existente se houver
            $table->dropIndex(['user_id']);

            // Se a coluna user_id já existir com o tipo ULID, você precisa recriá-la
            $table->dropColumn('user_id');
            $table->unsignedBigInteger('user_id')->nullable()->index()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Para reverter, você precisaria recriar a estrutura com ULID
        Schema::create('users_old', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            // Re-adicione os outros campos
            $table->string('number')->nullable();
            $table->integer('msgLimit')->default(0);
            $table->integer('sendedMsg')->default(0);
            $table->string('role')->default('user');
            $table->boolean('enabled')->default(true);
            $table->boolean('rightNumber')->default(false);
            $table->timestamp('lastMessage')->nullable();
        });

        // Nota: os dados não podem ser preservados corretamente no down()
        // porque os ULIDs anteriores foram perdidos

        Schema::drop('users');
        Schema::rename('users_old', 'users');

        // Você precisaria reverter as alterações na tabela sessions também
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
            $table->string('user_id')->nullable()->index()->after('id');
        });
    }
};
