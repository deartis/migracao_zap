<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            /**
             * Criar colunas para os serviços
             */
            $table->string('number')
                ->after('password');

            $table->integer('msgLimit')
                ->after('number');

            $table->integer('sendedMsg')
                ->after('msgLimit');

            $table->string('role')
                ->after('sendedMsg')
                ->default('nu');

            $table->boolean('enabled')
                ->after('role')
                ->default('0');

            $table->boolean('rightNumber')
                ->after('enabled')
                ->default('0');

            $table->text('lastMessage')
                ->after('rightNumber')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('number');
            $table->dropColumn('msgLimit');
            $table->dropColumn('sendedMsg');
            $table->dropColumn('role');
            $table->dropColumn('enabled');
            $table->dropColumn('lastMessage');
            $table->dropColumn('rightNumber');
            $table->dropColumn('sendedMsg');
        });
    }
};
