<?php

declare(strict_types=1);

return [
    'accepted' => 'O campo :attribute deve ser aceito.',
    'active_url' => 'O campo :attribute não é uma URL válida.',
    'after' => 'O campo :attribute deve ser uma data posterior a :date.',
    'alpha' => 'O campo :attribute deve conter apenas letras.',
    'array' => 'O campo :attribute deve ser uma matriz.',
    'before' => 'O campo :attribute deve ser uma data anterior a :date.',
    'between' => [
        'numeric' => 'O campo :attribute deve ser entre :min e :max.',
        'file' => 'O campo :attribute deve ser entre :min e :max kilobytes.',
        'string' => 'O campo :attribute deve ser entre :min e :max caracteres.',
        'array' => 'O campo :attribute deve ter entre :min e :max itens.',
    ],
    'boolean' => 'O campo :attribute deve ser verdadeiro ou falso.',
    'confirmed' => 'A confirmação do campo :attribute não coincide.',
    'date' => 'O campo :attribute não é uma data válida.',
    'email' => 'O campo :attribute deve ser um endereço de e-mail válido.',
    'exists' => 'O campo :attribute selecionado é inválido.',
    'max' => [
        'numeric' => 'O campo :attribute não deve ser maior que :max.',
        'string' => 'O campo :attribute não deve ser maior que :max caracteres.',
    ],
    'min' => [
        'numeric' => 'O campo :attribute deve ser no mínimo :min.',
        'string' => 'O campo :attribute deve ter no mínimo :min caracteres.',
    ],
    'numeric' => 'O campo :attribute deve ser um número.',
    'required' => 'O campo :attribute é obrigatório.',
    'required_if' => 'O campo :attribute é obrigatório quando :other é :value.',
    'string' => 'O campo :attribute deve ser uma string.',
    'unique' => 'O campo :attribute já está sendo utilizado.',
    'url' => 'O campo :attribute deve ser uma URL válida.',
];
