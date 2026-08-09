<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * O token de login mágico passa a ser um par seletor (indexado, texto
     * puro, usado para localizar a linha) + verificador (com hash sha256 em
     * repouso na coluna `token`, comparado via hash_equals). Links pendentes
     * emitidos no formato antigo (token em texto puro, sem seletor) não são
     * compatíveis com o novo esquema — descartamos os que ainda não foram
     * usados; o pior efeito colateral é o usuário pedir um novo link.
     */
    public function up(): void
    {
        DB::table('magic_login_tokens')->whereNull('used_at')->delete();

        Schema::table('magic_login_tokens', function (Blueprint $table) {
            $table->string('selector', 16)->unique()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('magic_login_tokens', function (Blueprint $table) {
            $table->dropColumn('selector');
        });
    }
};
