<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->backfillSlugs();

        Schema::table('subjects', function (Blueprint $table) {
            // Slug único por instituição (multi-tenant).
            $table->unique(['institution_id', 'slug'], 'subjects_institution_slug_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropUnique('subjects_institution_slug_unique');
        });
    }

    /**
     * Garante que toda matéria tenha um slug não vazio e único por instituição
     * antes de aplicar o índice único (evita falha em dados legados).
     */
    private function backfillSlugs(): void
    {
        $seen = [];

        DB::table('subjects')->orderBy('id')->get()->each(function ($subject) use (&$seen): void {
            $institutionKey = (string) ($subject->institution_id ?? 'null');

            $base = empty($subject->slug)
                ? Str::slug((string) $subject->name)
                : Str::slug($subject->slug);

            if ($base === '') {
                $base = 'subject-' . $subject->id;
            }

            $slug = $base;
            $suffix = 1;

            while (in_array($slug, $seen[$institutionKey] ?? [], true)) {
                $slug = $base . '-' . $suffix++;
            }

            $seen[$institutionKey][] = $slug;

            if ($slug !== $subject->slug) {
                DB::table('subjects')->where('id', $subject->id)->update(['slug' => $slug]);
            }
        });
    }
};
