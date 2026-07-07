<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Adiciona um hash do conteúdo para evitar materiais duplicados criados a
     * partir do upload do mesmo arquivo/conteúdo.
     */
    public function up(): void
    {
        Schema::table('study_materials', function (Blueprint $table) {
            $table->string('source_hash', 64)->nullable()->after('content')->index();
        });

        // Backfill dos registros existentes para que a deduplicação também
        // considere materiais já cadastrados.
        DB::table('study_materials')
            ->select('id', 'content')
            ->orderBy('id')
            ->chunk(200, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('study_materials')
                        ->where('id', $row->id)
                        ->update(['source_hash' => hash('sha256', (string) $row->content)]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('study_materials', function (Blueprint $table) {
            $table->dropIndex(['source_hash']);
            $table->dropColumn('source_hash');
        });
    }
};
