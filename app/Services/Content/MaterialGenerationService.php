<?php

declare(strict_types=1);

namespace App\Services\Content;

use Illuminate\Support\Str;

class MaterialGenerationService
{
    /**
     * Conteúdos pré-definidos para temas comuns.
     */
    protected array $themes = [
        'laravel_eloquent' => [
            'material' => [
                'title' => 'Dominando o Eloquent ORM no Laravel 13',
                'content' => '# Dominando o Eloquent ORM no Laravel 13

O **Eloquent ORM** é o mapeador objeto-relacional padrão do Laravel. Ele fornece uma implementação ActiveRecord bonita e simples para trabalhar com seu banco de dados.

## 1. Relacionamentos Fundamentais
No Laravel, os relacionamentos são definidos como métodos em suas classes de Model.

* **One To Many (Um para Muitos):**
  Definido usando `hasMany` no model pai e `belongsTo` no model filho.
  ```php
  // Model Subject
  public function studyMaterials() {
      return $this->hasMany(StudyMaterial::class);
  }
  ```

* **Many To Many (Muitos para Muitos):**
  Definido com `belongsToMany`. Requer uma tabela pivô.
  ```php
  // Model User
  public function completedMaterials() {
      return $this->belongsToMany(StudyMaterial::class, \'study_material_user\');
  }
  ```

## 2. Eager Loading (Carregamento Adiantado)
Por padrão, o Eloquent carrega relações de forma "preguiçosa" (Lazy Loading). Isso causa o famoso problema de consulta **N+1**. Para resolver isso, usamos o método `with`:

```php
// Correto: Apenas 2 consultas executadas
$subjects = Subject::with(\'studyMaterials\')->get();

foreach ($subjects as $subject) {
    echo $subject->studyMaterials->count();
}
```

## 3. Otimização de Consultas
Para otimizar o consumo de memória, evite carregar colunas desnecessárias e use `select` ou métodos de paginação e paginação rápida como `cursorPaginate` e `chunk`.',
            ],
            'test' => [
                'title' => 'Avaliação: Eloquent ORM e Relacionamentos',
                'description' => 'Teste seus conhecimentos sobre relacionamentos, N+1 e otimizações de consultas usando o Eloquent ORM no Laravel.',
                'questions' => [
                    [
                        'question_text' => 'Qual método é utilizado para evitar o problema de consulta N+1 no Eloquent ORM?',
                        'options' => ['load() apenas', 'with() (Eager Loading)', 'select()', 'lazy()'],
                        'correct_option_index' => 1,
                    ],
                    [
                        'question_text' => 'Como é definido um relacionamento de "Um para Muitos" no Model pai?',
                        'options' => ['belongsTo()', 'hasOne()', 'hasMany()', 'belongsToMany()'],
                        'correct_option_index' => 2,
                    ],
                    [
                        'question_text' => 'Qual a finalidade de usar transações de banco de dados (DB::transaction) no Laravel?',
                        'options' => [
                            'Executar consultas SQL mais rapidamente.',
                            'Garantir que um grupo de operações no banco seja executado por completo ou desfeito em caso de erro.',
                            'Excluir automaticamente dados duplicados.',
                            'Criar tabelas de histórico de forma nativa.',
                        ],
                        'correct_option_index' => 1,
                    ],
                ],
            ],
        ],
        'vue_composition' => [
            'material' => [
                'title' => 'Componentização Moderna com Vue 3 Composition API',
                'content' => '# Componentização Moderna com Vue 3 Composition API

A **Composition API** é a abordagem moderna do Vue.js para organizar o código em componentes, focando em flexibilidade e reuso de lógica através de funções chamadas *Composables*.

## 1. Reatividade com `ref` vs `reactive`
O Vue 3 oferece duas formas principais de criar dados reativos:

* **`ref`:** Usado para tipos primitivos (String, Number, Boolean) ou arrays/objetos inteiros. No Javascript, você acessa e altera via `.value`. No template HTML, o Vue faz o unwrap automático (sem precisar do `.value`).
  ```javascript
  import { ref } from \'vue\';
  const count = ref(0);
  count.value++; // no JS
  ```
* **`reactive`:** Usado apenas para objetos. Ele não usa `.value`, agindo como um proxy reativo do próprio objeto.
  ```javascript
  import { reactive } from \'vue\';
  const state = reactive({ count: 0 });
  state.count++; // direto
  ```

## 2. Computed Properties (Propriedades Computadas)
Uma propriedade computada é atualizada automaticamente quando suas dependências mudam e seu resultado é mantido em cache para melhor performance.
```javascript
import { ref, computed } from \'vue\';
const points = ref(50);
const doublePoints = computed(() => points.value * 2);
```

## 3. Composables (Reuso de Lógica)
Um Composable é uma função que encapsula estado reativo e lógica de ciclo de vida para ser compartilhada entre múltiplos componentes. Exemplo clássico: `useMouse`, `useFetch`, ou no nosso caso, `usePoints`.',
            ],
            'test' => [
                'title' => 'Avaliação: Reactividade e Estrutura no Vue 3',
                'description' => 'Verifique se você domina as diferenças entre ref/reactive, propriedades computadas e criação de composables no Vue 3.',
                'questions' => [
                    [
                        'question_text' => 'Como se acessa o valor de uma variável declarada como `const name = ref("John")` no bloco `<script setup>` do Vue 3?',
                        'options' => ['name', 'name.value', 'name.val', 'name()'],
                        'correct_option_index' => 1,
                    ],
                    [
                        'question_text' => 'Qual é a principal limitação do método `reactive()` em comparação com `ref()`?',
                        'options' => [
                            'Não suporta reatividade em tempo real.',
                            'Só pode receber objetos/arrays, não suportando primitivos diretamente.',
                            'Não pode ser atualizado de forma dinâmica.',
                            'Gera erros ao usar com componentes InertiaJS.',
                        ],
                        'correct_option_index' => 1,
                    ],
                    [
                        'question_text' => 'Qual a principal vantagem de usar `computed()` em vez de um método comum para retornar um valor calculado no template?',
                        'options' => [
                            'O computed faz cache do resultado e só reavalia se um de seus estados reativos mudar.',
                            'O computed roda em uma thread separada do navegador.',
                            'O computed permite alterar variáveis diretamente sem reatividade.',
                            'O computed só roda uma vez na vida do componente.',
                        ],
                        'correct_option_index' => 0,
                    ],
                ],
            ],
        ],
        'tailwind_css' => [
            'material' => [
                'title' => 'Layouts Flexíveis e Responsivos com Tailwind CSS',
                'content' => '# Layouts Flexíveis e Responsivos com Tailwind CSS

O **Tailwind CSS** é um framework CSS utilitário que permite construir interfaces modernas diretamente no HTML.

## 1. Flexbox no Tailwind
Para habilitar o Flexbox, adicionamos a classe `flex`. Configuramos a direção e alinhamentos:
* `flex-row` ou `flex-col`: define a direção dos itens.
* `justify-between` / `justify-center`: controla o alinhamento no eixo principal.
* `items-center`: controla o alinhamento no eixo secundário.

```html
<div class="flex justify-between items-center bg-gray-900 p-4 rounded-xl">
    <span>Ranking do Aluno</span>
    <span class="text-yellow-400">1º Lugar</span>
</div>
```

## 2. CSS Grid no Tailwind
CSS Grid é perfeito para layouts bidimensionais. Definimos o número de colunas usando classes como `grid-cols-1`, `grid-cols-3` e o espaçamento com `gap-4`:

```html
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-zinc-800 p-6">Matéria A</div>
    <div class="bg-zinc-800 p-6">Matéria B</div>
    <div class="bg-zinc-800 p-6">Matéria C</div>
</div>
```

## 3. Design Responsivo
O Tailwind usa uma abordagem *Mobile-First*. Prefixos como `sm:`, `md:`, `lg:`, `xl:` aplicam estilos apenas em telas daquele tamanho para cima.',
            ],
            'test' => [
                'title' => 'Avaliação: Flexbox, Grid e Responsividade CSS',
                'description' => 'Teste suas habilidades na construção de layouts responsivos usando classes do Tailwind CSS.',
                'questions' => [
                    [
                        'question_text' => 'Qual classe do Tailwind CSS deve ser aplicada para definir uma grade com 3 colunas de tamanhos iguais?',
                        'options' => ['grid-3', 'grid-cols-3', 'cols-span-3', 'flex-cols-3'],
                        'correct_option_index' => 1,
                    ],
                    [
                        'question_text' => 'Dado o prefixo responsive `md:block hidden`, qual o comportamento visual do elemento?',
                        'options' => [
                            'O elemento fica oculto em telas menores que o breakpoint `md` e visível (como block) de `md` em diante.',
                            'O elemento fica visível em celulares e some em desktops.',
                            'O elemento some de forma animada.',
                            'O elemento sempre fica visível como block.',
                        ],
                        'correct_option_index' => 0,
                    ],
                    [
                        'question_text' => 'Como alinhamos verticalmente no centro os itens dentro de um container flexível (`flex`)?',
                        'options' => ['content-center', 'justify-center', 'items-center', 'self-center'],
                        'correct_option_index' => 2,
                    ],
                ],
            ],
        ],
    ];

    /**
     * Gera os dados do Material de Estudo com base no tema.
     */
    public function generateMaterialData(string $theme): array
    {
        $key = $this->getThemeKey($theme);

        if ($key !== 'generic' && isset($this->themes[$key])) {
            return $this->themes[$key]['material'];
        }

        // Fallback genérico para outros tópicos
        $title = 'Estudo Aprofundado: ' . ucwords($theme);
        $content = '# Estudo Aprofundado: ' . ucwords($theme) . '

Este material foi gerado de forma dinâmica para fornecer conceitos essenciais sobre **' . ucwords($theme) . '**.

## Introdução e Conceitos Chave
Entender ' . ucwords($theme) . ' é um passo fundamental para se destacar no mercado profissional atual. A aplicação prática desse conhecimento envolve a compreensão de:

1. **Fundamentos sólidos:** a base teórica e de boas práticas.
2. **Arquitetura organizada:** dividindo responsabilidades e mantendo o código limpo.
3. **Padrões de mercado:** adotando os frameworks e conceitos mais populares.

## Próximos Passos
Após esta leitura, realize as atividades práticas anexadas para consolidar a teoria em conhecimento prático acumulando pontuações para o ranking competitivo.
';

        return [
            'title' => $title,
            'content' => $content,
        ];
    }

    /**
     * Gera os dados do Teste com base no tema.
     */
    public function generateTestData(string $theme): array
    {
        $key = $this->getThemeKey($theme);

        if ($key !== 'generic' && isset($this->themes[$key])) {
            return $this->themes[$key]['test'];
        }

        // Fallback genérico para outros tópicos
        return [
            'title' => 'Quiz Rápido: ' . ucwords($theme),
            'description' => 'Teste de nivelamento geral sobre ' . ucwords($theme) . '.',
            'questions' => [
                [
                    'question_text' => 'Qual é a principal vantagem de aplicar padrões de projeto na implementação de ' . ucwords($theme) . '?',
                    'options' => [
                        'Garantir melhor desempenho em tempo de compilação.',
                        'Facilitar a manutenção, legibilidade e reuso do código mantendo a separação de responsabilidades.',
                        'Excluir a necessidade de testes automatizados.',
                        'Economizar espaço de armazenamento em disco.',
                    ],
                    'correct_option_index' => 1,
                ],
                [
                    'question_text' => 'Na gamificação profissional, qual é o principal benefício do ranking competitivo?',
                    'options' => [
                        'Fazer os alunos estudarem menos.',
                        'Reduzir a pontuação geral.',
                        'Estimular o engajamento e a participação ativa no aprendizado através da competição saudável.',
                        'Gerar relatórios de erros técnicos.',
                    ],
                    'correct_option_index' => 2,
                ],
            ],
        ];
    }

    /**
     * Normaliza a chave do tema.
     */
    protected function getThemeKey(string $theme): string
    {
        $theme = Str::slug($theme, '_');

        if (str_contains($theme, 'eloquent') || str_contains($theme, 'laravel')) {
            return 'laravel_eloquent';
        }

        if (str_contains($theme, 'vue') || str_contains($theme, 'inertia')) {
            return 'vue_composition';
        }

        if (str_contains($theme, 'tailwind') || str_contains($theme, 'css') || str_contains($theme, 'flex') || str_contains($theme, 'grid')) {
            return 'tailwind_css';
        }

        return 'generic';
    }
}
