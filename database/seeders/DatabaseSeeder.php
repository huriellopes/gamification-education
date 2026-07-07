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
     * Quantidade de registros em massa (para testar paginação/rankings).
     */
    private const BULK_STUDENTS = 120;

    private const BULK_TEACHERS = 10;

    private const BULK_SUBJECTS = 15;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->info('🌱 Iniciando o seeding do banco de dados...');

        [$ift, $uec] = $this->seedInstitutions();
        [$admin, $teacher] = $this->seedStaff($ift, $uec);
        $this->seedDemoStudents($ift, $uec);
        $this->seedSubjectsWithContent($ift, $teacher);
        $this->seedBulkData($ift, $uec);

        $this->info('✅ Seeding concluído com sucesso!');
        $this->info(sprintf(
            '   Resumo: %d usuários, %d instituições, %d matérias.',
            User::count(),
            Institution::count(),
            Subject::count(),
        ));
    }

    /**
     * Cria as instituições de ensino de demonstração.
     *
     * @return array{0: Institution, 1: Institution}
     */
    private function seedInstitutions(): array
    {
        $this->info('🏫 Criando instituições...');

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

        return [$ift, $uec];
    }

    /**
     * Cria super admin, administrador de instituição e professor.
     * O admin e o professor ficam vinculados a duas instituições (multi-instituição).
     *
     * @return array{0: User, 1: User}
     */
    private function seedStaff(Institution $ift, Institution $uec): array
    {
        $this->info('👑 Criando Super Administrador global...');
        $this->createUser([
            'name' => 'Super Administrador Global',
            'email' => 'super_admin@example.com',
            'role' => 'super_admin',
            'institution_id' => null,
        ]);

        $this->info('🧑‍💼 Criando Administrador (IFT + UEC)...');
        $admin = $this->createUser([
            'name' => 'Administrador IFT',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'institution_id' => $ift->id,
        ]);
        $admin->institutions()->sync([$ift->id, $uec->id]);

        $this->info('👨‍🏫 Criando Professor (multi-instituição: IFT + UEC)...');
        $teacher = $this->createUser([
            'name' => 'Professor de Tecnologia',
            'email' => 'teacher@example.com',
            'role' => 'teacher',
            'institution_id' => $ift->id,
        ]);
        $teacher->institutions()->sync([$ift->id, $uec->id]);

        return [$admin, $teacher];
    }

    /**
     * Cria alunos nomeados com pontuações variadas (para testar o ranking).
     */
    private function seedDemoStudents(Institution $ift, Institution $uec): void
    {
        $this->info('🎓 Criando alunos de demonstração...');

        $students = [
            ['name' => 'Maria Silva', 'email' => 'maria@example.com', 'points' => 290, 'institution_id' => $uec->id],
            ['name' => 'Aluno Demonstrativo', 'email' => 'student@example.com', 'points' => 225, 'institution_id' => $ift->id],
            ['name' => 'João Souza', 'email' => 'joao@example.com', 'points' => 180, 'institution_id' => $ift->id],
            ['name' => 'Pedro Rocha', 'email' => 'pedro@example.com', 'points' => 95, 'institution_id' => $ift->id],
        ];

        foreach ($students as $student) {
            $this->createUser([...$student, 'role' => 'student']);
        }
    }

    /**
     * Cria matérias do IFT e gera materiais + desafios automaticamente.
     */
    private function seedSubjectsWithContent(Institution $ift, User $teacher): void
    {
        $this->info('📚 Criando matérias e gerando conteúdo (materiais + desafios)...');

        $subjectWeb = Subject::create([
            'institution_id' => $ift->id,
            'name' => 'Desenvolvimento Web com Laravel',
            'slug' => 'desenvolvimento-web-com-laravel',
            'description' => 'Aprofundamento no Laravel Framework 13.x, InertiaJS, Vue 3 e arquiteturas limpas baseadas em padrões.',
        ]);

        $subjectDatabase = Subject::create([
            'institution_id' => $ift->id,
            'name' => 'Arquitetura de Banco de Dados',
            'slug' => 'arquitetura-de-banco-de-dados',
            'description' => 'Otimização de queries, indexação de tabelas e persistência de dados em alta performance.',
        ]);

        $generateMaterial = resolve(GenerateStudyMaterialAction::class);
        $generateTest = resolve(GenerateTestForSubjectAction::class);

        $content = [
            [$subjectWeb, 'laravel_eloquent'],
            [$subjectWeb, 'vue_composition'],
            [$subjectDatabase, 'tailwind_css'],
        ];

        foreach ($content as [$subject, $theme]) {
            $generateMaterial->execute($subject, $theme);
            $generateTest->execute($subject, $theme);
        }

        $teacher->subjects()->attach([$subjectWeb->id, $subjectDatabase->id]);
    }

    /**
     * Gera dados em massa para testar paginação e rankings.
     */
    private function seedBulkData(Institution $ift, Institution $uec): void
    {
        $this->info(sprintf(
            '📈 Gerando dados em massa: %d alunos, %d professores, %d matérias...',
            self::BULK_STUDENTS,
            self::BULK_TEACHERS,
            self::BULK_SUBJECTS,
        ));

        for ($i = 1; $i <= self::BULK_STUDENTS; $i++) {
            $this->createUser([
                'name' => "Estudante de Teste Paginação {$i}",
                'email' => "student_page_{$i}@example.com",
                'role' => 'student',
                'points' => rand(10, 800),
                'institution_id' => $i % 2 === 0 ? $ift->id : $uec->id,
            ]);
        }

        for ($i = 1; $i <= self::BULK_TEACHERS; $i++) {
            $this->createUser([
                'name' => "Professor Auxiliar {$i}",
                'email' => "teacher_page_{$i}@example.com",
                'role' => 'teacher',
                'institution_id' => $ift->id,
            ]);
        }

        for ($i = 1; $i <= self::BULK_SUBJECTS; $i++) {
            Subject::create([
                'institution_id' => $ift->id,
                'name' => "Matéria Optativa {$i}",
                'slug' => "materia-optativa-{$i}",
                'description' => "Descrição detalhada para a Matéria Optativa de número {$i} para fins de testes de paginação do admin.",
            ]);
        }
    }

    /**
     * Cria um usuário aplicando os valores padrão (senha e pontuação).
     *
     * @param  array<string, mixed>  $attributes
     */
    private function createUser(array $attributes): User
    {
        return User::create([
            'password' => Hash::make('password'),
            'points' => 0,
            ...$attributes,
        ]);
    }

    /**
     * Escreve uma mensagem de progresso no console (quando executado via artisan).
     */
    private function info(string $message): void
    {
        $this->command?->info($message);
    }
}
