<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Institution;
use App\Models\User;

class GetMembersReportDataAction
{
    /**
     * Retorna os dados formatados para exportação do relatório de membros.
     *
     * @return array<int, array<int, mixed>>
     */
    public function execute(): array
    {
        $data = [
            ['ID', 'Nome', 'E-mail', 'Papel', 'Pontos Totais', 'Instituição Atual'],
        ];

        User::with('institution')->chunk(100, function ($users) use (&$data) {
            foreach ($users as $user) {
                $data[] = [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->role->label(),
                    $user->points,
                    $user->institution instanceof Institution ? $user->institution->name : 'N/A',
                ];
            }
        });

        return $data;
    }
}
