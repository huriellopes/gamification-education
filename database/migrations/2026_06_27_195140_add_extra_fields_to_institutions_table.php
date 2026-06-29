<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->string('razao_social')->after('name');
            $table->string('cnpj')->nullable()->after('razao_social');
            $table->string('slug')->unique()->after('cnpj');
            $table->json('address')->nullable()->after('slug');
            $table->json('phones')->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn(['razao_social', 'cnpj', 'slug', 'address', 'phones']);
        });
    }
};
