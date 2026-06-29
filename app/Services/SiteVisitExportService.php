<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SiteVisit;
use Shuchkin\SimpleXLSXGen;

class SiteVisitExportService
{
    /**
     * Gera um arquivo XLSX com os logs de visitas ao site e retorna o caminho temporário.
     */
    public function generateXlsx(): string
    {
        $visits = SiteVisit::orderBy('visited_at', 'desc')->get();

        $data = [
            ['ID', 'Data/Hora', 'Endereço IP (Descriptografado)', 'Navegador/Dispositivo'],
        ];

        foreach ($visits as $visit) {
            $data[] = [
                (string) $visit->id,
                $visit->visited_at ? $visit->visited_at->format('d/m/Y H:i:s') : '',
                (string) $visit->ip_address,
                (string) $visit->user_agent,
            ];
        }

        $filePath = tempnam(sys_get_temp_dir(), 'xlsx');

        if ($filePath === false) {
            abort(500, 'Não foi possível gerar o arquivo temporário de exportação.');
        }

        SimpleXLSXGen::fromArray($data)->saveAs($filePath);

        return $filePath;
    }

    /**
     * Nome do arquivo de exportação baseado na data/hora atual.
     */
    public function filename(): string
    {
        return 'visitas_site_' . date('Y_m_d_His') . '.xlsx';
    }
}
