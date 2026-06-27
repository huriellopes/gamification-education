<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Institution;
use App\Models\Subject;
use App\Actions\GenerateStudyMaterialAction;
use App\Actions\GenerateTestForSubjectAction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Criar Instituições de Ensino
        $ift = Institution::create([
            'name' => 'Instituto Federal de Tecnologia (IFT)',
            'description' => 'Campus Avançado de Tecnologia da Informação e Programação.'
        ]);

        $uec = Institution::create([
            'name' => 'Universidade Estadual de Computação (UEC)',
            'description' => 'Centro de Excelência em Engenharia de Software e Ciências de Dados.'
        ]);

        // 2. Criar Usuário Administrador
        User::create([
            'name' => 'Administrador da Arena',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'points' => 0,
            'institution_id' => null,
        ]);

        // 3. Criar Alunos Comuns com pontuações variadas para testar o Ranking
        // Maria (UEC) - 290 pts
        User::create([
            'name' => 'Maria Silva',
            'email' => 'maria@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'points' => 290,
            'institution_id' => $uec->id,
        ]);

        // Student (IFT) - 225 pts (Aluno padrão para testes rápidos)
        User::create([
            'name' => 'Aluno Demonstrativo',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'points' => 225,
            'institution_id' => $ift->id,
        ]);

        // João (IFT) - 180 pts
        User::create([
            'name' => 'João Souza',
            'email' => 'joao@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'points' => 180,
            'institution_id' => $ift->id,
        ]);

        // Pedro (IFT) - 95 pts
        User::create([
            'name' => 'Pedro Rocha',
            'email' => 'pedro@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'points' => 95,
            'institution_id' => $ift->id,
        ]);

        // 4. Criar Matérias para o IFT
        $subjectWeb = Subject::create([
            'institution_id' => $ift->id,
            'name' => 'Desenvolvimento Web com Laravel',
            'description' => 'Aprofundamento no Laravel Framework 13.x, InertiaJS, Vue 3 e arquiteturas limpas baseadas em padrões.'
        ]);

        $subjectDatabase = Subject::create([
            'institution_id' => $ift->id,
            'name' => 'Arquitetura de Banco de Dados',
            'description' => 'Otimização de queries, indexação de tabelas e persistência de dados em alta performance.'
        ]);

        // 5. Instanciar as Actions para gerar materiais e testes automaticamente via Seeder
        $generateMaterial = app(GenerateStudyMaterialAction::class);
        $generateTest = app(GenerateTestForSubjectAction::class);

        // Gerar materiais e testes para a matéria de Web/Laravel
        $generateMaterial->execute($subjectWeb, 'laravel_eloquent');
        $generateTest->execute($subjectWeb, 'laravel_eloquent');

        $generateMaterial->execute($subjectWeb, 'vue_composition');
        $generateTest->execute($subjectWeb, 'vue_composition');

        // Gerar material e teste para a matéria de Banco de Dados
        $generateMaterial->execute($subjectDatabase, 'tailwind_css'); // Usando tema do preset para simular conteúdo rico
        $generateTest->execute($subjectDatabase, 'tailwind_css');
    }
}
