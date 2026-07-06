<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\Content\GenerateStudyMaterialAction;
use App\Actions\Content\GenerateTestForSubjectAction;
use App\Models\Institution;
use App\Models\Subject;
use App\Models\User;
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
            'razao_social' => 'Instituto Federal de Tecnologia Ltda',
            'cnpj' => '12345678000195',
            'slug' => 'ift',
            'description' => 'Campus Avançado de Tecnologia da Informação e Programação.',
            'address' => [
                'cep' => '70070-010',
                'logradouro' => 'Via S2',
                'numero' => 'S/N',
                'complemento' => 'Anexo IV',
                'bairro' => 'Zona Cívico-Administrativa',
                'cidade' => 'Brasília',
                'uf' => 'DF',
            ],
            'phones' => ['6133034100'],
        ]);

        $uec = Institution::create([
            'name' => 'Universidade Estadual de Computação (UEC)',
            'razao_social' => 'Universidade Estadual de Computação S.A.',
            'cnpj' => '98765432000198',
            'slug' => 'uec',
            'description' => 'Centro de Excelência em Engenharia de Software e Ciências de Dados.',
            'address' => [
                'cep' => '01310-200',
                'logradouro' => 'Avenida Paulista',
                'numero' => '1700',
                'complemento' => 'Bloco B',
                'bairro' => 'Bela Vista',
                'cidade' => 'São Paulo',
                'uf' => 'SP',
            ],
            'phones' => ['1132543000'],
        ]);

        // 2. Criar Usuário Super Admin (Acesso Global)
        User::create([
            'name' => 'Super Administrador Global',
            'email' => 'super_admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'points' => 0,
            'institution_id' => null,
        ]);

        // 3. Criar Usuário Administrador de Instituição (IFT)
        $admin = User::create([
            'name' => 'Administrador IFT',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'points' => 0,
            'institution_id' => $ift->id,
        ]);

        // 4. Criar Professor (IFT)
        $teacher = User::create([
            'name' => 'Professor de Tecnologia',
            'email' => 'teacher@example.com',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'points' => 0,
            'institution_id' => $ift->id,
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
            'description' => 'Aprofundamento no Laravel Framework 13.x, InertiaJS, Vue 3 e arquiteturas limpas baseadas em padrões.',
        ]);

        $subjectDatabase = Subject::create([
            'institution_id' => $ift->id,
            'name' => 'Arquitetura de Banco de Dados',
            'description' => 'Otimização de queries, indexação de tabelas e persistência de dados em alta performance.',
        ]);

        // 5. Instanciar as Actions para gerar materiais e testes automaticamente via Seeder
        $generateMaterial = resolve(GenerateStudyMaterialAction::class);
        $generateTest = resolve(GenerateTestForSubjectAction::class);

        // Gerar materiais e testes para a matéria de Web/Laravel
        $generateMaterial->execute($subjectWeb, 'laravel_eloquent');
        $generateTest->execute($subjectWeb, 'laravel_eloquent');

        $generateMaterial->execute($subjectWeb, 'vue_composition');
        $generateTest->execute($subjectWeb, 'vue_composition');

        // Gerar material e teste para a matéria de Banco de Dados
        $generateMaterial->execute($subjectDatabase, 'tailwind_css'); // Usando tema do preset para simular conteúdo rico
        $generateTest->execute($subjectDatabase, 'tailwind_css');

        // Associar matérias ao professor criado
        $teacher->subjects()->attach([$subjectWeb->id, $subjectDatabase->id]);

        // Associar instituições ao administrador criado
        $admin->institutions()->attach([$ift->id, $uec->id]);

        // 6. Gerar dados em lote para testar a paginação (120 estudantes, 10 professores e 15 matérias extras)
        for ($i = 1; $i <= 120; $i++) {
            User::create([
                'name' => "Estudante de Teste Paginação {$i}",
                'email' => "student_page_{$i}@example.com",
                'password' => Hash::make('password'),
                'role' => 'student',
                'points' => rand(10, 800),
                'institution_id' => $i % 2 === 0 ? $ift->id : $uec->id,
            ]);
        }

        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name' => "Professor Auxiliar {$i}",
                'email' => "teacher_page_{$i}@example.com",
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'points' => 0,
                'institution_id' => $ift->id,
            ]);
        }

        for ($i = 1; $i <= 15; $i++) {
            Subject::create([
                'institution_id' => $ift->id,
                'name' => "Matéria Optativa {$i}",
                'description' => "Descrição detalhada para a Matéria Optativa de número {$i} para fins de testes de paginação do admin.",
            ]);
        }
    }
}
