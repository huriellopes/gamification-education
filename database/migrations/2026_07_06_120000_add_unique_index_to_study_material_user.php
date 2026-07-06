<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove duplicatas pré-existentes (mantendo o menor id por par),
        // caso contrário a criação do índice único falharia.
        $duplicateIds = DB::table('study_material_user as t1')
            ->join('study_material_user as t2', function ($join): void {
                $join->on('t1.user_id', '=', 't2.user_id')
                    ->on('t1.study_material_id', '=', 't2.study_material_id')
                    ->on('t1.id', '>', 't2.id');
            })
            ->pluck('t1.id');

        if ($duplicateIds->isNotEmpty()) {
            DB::table('study_material_user')->whereIn('id', $duplicateIds)->delete();
        }

        Schema::table('study_material_user', function (Blueprint $table): void {
            // Impede que o mesmo material seja concluído duas vezes pelo mesmo
            // aluno (evita crédito duplicado de XP em requisições concorrentes).
            $table->unique(['user_id', 'study_material_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('study_material_user', function (Blueprint $table): void {
            $table->dropUnique(['user_id', 'study_material_id']);
        });
    }
};
