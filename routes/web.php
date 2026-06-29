<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\Legal\GuidelinesController;
use App\Http\Controllers\Legal\PrivacyPolicyController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

// Rota Inicial (Site Público)
Route::get('/', WelcomeController::class);
Route::get('/sitemap.xml', SitemapController::class);

// Páginas Legais Públicas
Route::get('/politica-de-privacidade', PrivacyPolicyController::class)->name('legal.privacy');
Route::get('/diretrizes', GuidelinesController::class)->name('legal.guidelines');

// Redirecionamento de Dashboard por Role
Route::get('/dashboard', DashboardRedirectController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Modularização de Rotas por Contexto
require __DIR__ . '/parts/super_admin.php';
require __DIR__ . '/parts/admin.php';
require __DIR__ . '/parts/teacher.php';
require __DIR__ . '/parts/student.php';
require __DIR__ . '/parts/shared.php';

// Rotas de Autenticação
require __DIR__ . '/auth.php';
