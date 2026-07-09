<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Turmas criadas por professores ficam pendentes até um admin aprovar.
     * `approved_at` nulo = pendente. As turmas já existentes são consideradas
     * aprovadas (backfill) para não quebrar o comportamento atual.
     */
    public function up(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->timestamp('approved_at')->nullable()->after('is_active');
            $table->foreignId('approved_by')->nullable()->after('approved_at')
                ->constrained('users')->nullOnDelete();
        });

        // Backfill: tudo que já existe entra como aprovado.
        DB::table('classrooms')->whereNull('approved_at')->update([
            'approved_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn('approved_at');
        });
    }
};
