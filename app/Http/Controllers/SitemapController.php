<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Gera o sitemap XML dinamicamente para indexação nos mecanismos de busca (Google, etc).
     */
    public function __invoke(): Response
    {
        $urls = [
            [
                'loc' => url('/'),
                'lastmod' => today()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '1.0',
            ],
            [
                'loc' => url('/login'),
                'lastmod' => today()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => route('legal.privacy'),
                'lastmod' => today()->toAtomString(),
                'changefreq' => 'yearly',
                'priority' => '0.5',
            ],
            [
                'loc' => route('legal.guidelines'),
                'lastmod' => today()->toAtomString(),
                'changefreq' => 'yearly',
                'priority' => '0.5',
            ],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $xml .= '<url>';
            $xml .= '<loc>' . htmlspecialchars($url['loc']) . '</loc>';
            $xml .= '<lastmod>' . $url['lastmod'] . '</lastmod>';
            $xml .= '<changefreq>' . $url['changefreq'] . '</changefreq>';
            $xml .= '<priority>' . $url['priority'] . '</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return response($xml, 200)
            ->header('Content-Type', 'application/xml');
    }
}
