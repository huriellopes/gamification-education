<?php

declare(strict_types=1);

return [
    'title' => 'Turmas',
    'admin_header' => 'Gerenciar Turmas da Instituição',
    'super_header' => 'Gerenciar Turmas (Todas as Instituições)',
    'teacher_header' => 'Minhas Turmas',
    'new' => 'Nova Turma',
    'edit' => 'Editar Turma',
    'empty' => 'Nenhuma turma cadastrada ainda.',
    'teacher_empty' => 'Você ainda não foi vinculado a nenhuma turma.',
    'search' => 'Buscar turmas...',

    'col_name' => 'Turma',
    'col_teacher' => 'Professor',
    'col_institution' => 'Instituição',
    'col_subjects' => 'Matérias',
    'no_teacher' => 'Sem professor',
    'no_subjects' => 'Nenhuma matéria vinculada',
    'subjects_count' => ':count matéria(s)',
    'students_count' => ':count aluno(s)',
    'add_students' => 'Adicionar alunos',
    'enroll_title' => 'Adicionar alunos — :name',
    'enroll_search' => 'Buscar aluno por nome ou e-mail...',
    'enroll_no_students' => 'Nenhum aluno encontrado.',
    'enroll_already' => 'Na turma',
    'enroll_submit' => 'Matricular selecionados',

    'form_name' => 'Nome da Turma',
    'form_name_placeholder' => 'Ex.: 3º Ano A - Manhã',
    'form_description' => 'Descrição',
    'form_institution' => 'Instituição',
    'form_select_institution' => 'Selecione a instituição',
    'form_teacher' => 'Professor responsável',
    'form_no_teacher' => 'Sem professor',
    'form_subjects' => 'Matérias da turma',
    'form_subjects_hint' => 'Selecione as matérias que pertencem a esta turma.',
    'form_no_subjects_available' => 'Nenhuma matéria disponível nesta instituição.',

    'enroll_label' => 'Turma do aluno',
    'enroll_none' => 'Sem turma',

    'confirm_delete_title' => 'Excluir turma',
    'confirm_delete_message' => 'Tem certeza que deseja excluir a turma ":name"? As matérias serão desvinculadas.',
    'confirm_toggle_title' => 'Alterar status',
    'confirm_toggle_message' => 'Deseja :action a turma ":name"?',
    'action_activate' => 'ativar',
    'action_deactivate' => 'desativar',

    // Fluxo de aprovação (turma criada por professor fica pendente)
    'created_pending' => 'Turma criada! Aguardando aprovação de um administrador.',
    'approved' => 'Turma aprovada e ativada!',
    'status_pending' => 'Pendente',
    'pending_hint' => 'Turma aguardando aprovação do administrador — você já pode matricular alunos.',
    'approve' => 'Aprovar',
    'approve_confirm_title' => 'Aprovar turma',
    'approve_confirm_message' => 'Aprovar e ativar a turma ":name"?',
    'teacher_new' => 'Criar turma',
    'teacher_create_title' => 'Nova turma',
    'teacher_create_submit' => 'Criar turma',
];
