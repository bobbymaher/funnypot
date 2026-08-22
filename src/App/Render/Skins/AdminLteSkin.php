<?php
declare(strict_types=1);
namespace Funnypot\App\Render\Skins;

use Funnypot\App\Render\Esc;
use Funnypot\App\Render\PageSlots;
use Funnypot\App\Render\Skin;
use Funnypot\App\Render\VisualPersona;

/**
 * A hand-authored lookalike of an AdminLTE/Bootstrap-style admin panel: a fixed left sidebar of menu
 * links, a top navbar naming the company, and card content in the main pane. Structural resemblance
 * only — no upstream AdminLTE/Bootstrap markup or CSS bytes are reproduced. This is the broadest
 * matcher of the four skins (`/admin`, `/dashboard`, `/manage`), so it is registered last in the
 * SkinSet — more specific product analogs (WordPress, phpMyAdmin, Grafana) get first refusal.
 */
final class AdminLteSkin implements Skin
{
    public function matches(string $path): bool
    {
        return str_contains($path, '/admin') || str_contains($path, '/dashboard') || str_contains($path, '/manage');
    }

    public function key(): string
    {
        return 'adminlte';
    }

    public function render(PageSlots $slots, VisualPersona $persona, string $escapedPath): string
    {
        $company = Esc::text($persona->company());
        $appName = Esc::text($slots->appName());
        $title = $slots->pageTitle() !== '' ? $slots->pageTitle() : $slots->appName();

        $html = '<!doctype html><html lang="en"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width">'
            . '<title>' . Esc::text($title) . '</title>'
            . '<style>' . $this->css() . '</style>'
            . '</head><body class="alte-body">';

        $html .= '<div class="alte-wrapper">';

        $html .= '<nav class="alte-navbar">';
        $html .= '<span class="alte-brand">' . $company . '</span>';
        if ($appName !== '') {
            $html .= '<span class="alte-app">' . $appName . '</span>';
        }
        $html .= '</nav>';

        $html .= '<aside class="alte-sidebar">';
        $html .= '<ul class="alte-nav-sidebar">';
        foreach ($slots->navItems() as $item) {
            // href is a trusted literal — a model value never reaches a URL sink.
            $html .= '<li class="alte-nav-item"><a class="alte-nav-link" href="#">' . Esc::text($item) . '</a></li>';
        }
        $html .= '</ul>';
        $html .= '</aside>';

        $html .= '<div class="alte-content-wrapper"><section class="alte-content">';
        $html .= '<div class="alte-card">';

        $heading = $slots->heading();
        if ($heading !== '') {
            $html .= '<div class="alte-card-header">' . Esc::text($heading) . '</div>';
        }
        $html .= '<div class="alte-card-body">';
        if ($slots->intro() !== '') {
            $html .= '<p class="alte-intro">' . Esc::text($slots->intro()) . '</p>';
        }

        $html .= $this->table($slots->tableCols(), $slots->tableRows());

        if ($slots->flash() !== '') {
            $html .= '<div class="alte-flash">' . Esc::text($slots->flash()) . '</div>';
        }
        $html .= '</div>'; // alte-card-body
        $html .= '</div>'; // alte-card
        $html .= '</section></div>'; // alte-content-wrapper

        $html .= '</div>'; // alte-wrapper
        $html .= '</body></html>';

        return $html;
    }

    /**
     * @param list<string> $cols
     * @param list<list<string>> $rows
     */
    private function table(array $cols, array $rows): string
    {
        if ($cols === [] && $rows === []) {
            return '';
        }
        $html = '<table class="alte-table">';
        if ($cols !== []) {
            $html .= '<thead><tr>';
            foreach ($cols as $col) {
                $html .= '<th>' . Esc::text($col) . '</th>';
            }
            $html .= '</tr></thead>';
        }
        $html .= '<tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . Esc::text($cell) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        return $html;
    }

    private function css(): string
    {
        // Palette reads as a Bootstrap-admin-template scheme (dark sidebar, blue-grey accent) but every
        // hex is nudged off any specific template's exact brand tokens — resemblance, not reuse.
        return 'body.alte-body{margin:0;font-family:sans-serif;background:#eef1f3;color:#2c3136}'
            . '.alte-wrapper{min-height:100vh}'
            . '.alte-navbar{position:fixed;top:0;left:0;right:0;height:52px;background:#fff;'
            . 'border-bottom:1px solid #d7dbdf;display:flex;align-items:center;gap:10px;padding:0 16px;'
            . 'box-sizing:border-box;z-index:2}'
            . '.alte-brand{font-weight:bold;color:#3b7ea1}'
            . '.alte-app{color:#6c757d}'
            . '.alte-sidebar{position:fixed;top:52px;bottom:0;left:0;width:230px;background:#2f3640;'
            . 'padding-top:10px;box-sizing:border-box;overflow-y:auto}'
            . '.alte-nav-sidebar{list-style:none;margin:0;padding:0}'
            . '.alte-nav-item{margin:0}'
            . '.alte-nav-link{display:block;padding:10px 16px;color:#c9ccd1;text-decoration:none}'
            . '.alte-nav-link:hover{background:#3b4148;color:#fff}'
            . '.alte-content-wrapper{margin-left:230px;padding-top:52px;box-sizing:border-box}'
            . '.alte-content{padding:20px}'
            . '.alte-card{background:#fff;border:1px solid #d7dbdf;border-radius:4px}'
            . '.alte-card-header{padding:10px 14px;border-bottom:1px solid #d7dbdf;font-weight:bold;'
            . 'color:#2c3136}'
            . '.alte-card-body{padding:14px}'
            . '.alte-intro{color:#5b636a}'
            . '.alte-table{border-collapse:collapse;width:100%;margin-top:8px}'
            . '.alte-table th,.alte-table td{border:1px solid #d7dbdf;padding:6px 10px;text-align:left}'
            . '.alte-flash{margin-top:12px;padding:8px 12px;background:#eaf2f6;border-left:4px solid #3b7ea1}';
    }
}
