<?php
declare(strict_types=1);
namespace Funnypot\App\Render\Skins;

use Funnypot\App\Render\Esc;
use Funnypot\App\Render\PageSlots;
use Funnypot\App\Render\Skin;
use Funnypot\App\Render\VisualPersona;

/**
 * A hand-authored lookalike of a Grafana dashboard: dark top nav, a left icon rail, and a panel-grid
 * content area. Structural resemblance only — no upstream Grafana markup/CSS bytes are reproduced,
 * and the accent color is shifted off the product's exact brand hex.
 */
final class GrafanaSkin implements Skin
{
    public function matches(string $path): bool
    {
        return str_contains($path, '/grafana') || str_contains($path, '/d/');
    }

    public function key(): string
    {
        return 'grafana';
    }

    public function render(PageSlots $slots, VisualPersona $persona, string $escapedPath): string
    {
        $company = Esc::text($persona->company());
        $title = $slots->heading() !== '' ? $slots->heading() : ($slots->appName() !== '' ? $slots->appName() : 'Dashboard');

        $html = '<!doctype html><html lang="en"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width">'
            . '<title>' . Esc::text($title) . '</title>'
            . '<style>' . $this->css() . '</style>'
            . '</head><body class="gf-body">';

        $html .= '<div class="gf-topnav"><span class="gf-brand">' . $company . '</span>'
            . '<span class="gf-topnav-title">' . Esc::text($title) . '</span></div>';

        $html .= '<div class="gf-shell">';
        $html .= $this->rail($slots->navItems());

        $html .= '<main class="gf-content">';
        $html .= '<h1 class="gf-dashboard-title">' . Esc::text($title) . '</h1>';
        if ($slots->intro() !== '') {
            $html .= '<p class="gf-sub">' . Esc::text($slots->intro()) . '</p>';
        }
        if ($slots->flash() !== '') {
            $html .= '<div class="gf-alert">' . Esc::text($slots->flash()) . '</div>';
        }

        $html .= $this->panelGrid($slots->tableCols(), $slots->tableRows());

        $html .= '</main>';
        $html .= '</div>';
        $html .= '</body></html>';

        return $html;
    }

    /** @param list<string> $items */
    private function rail(array $items): string
    {
        $html = '<nav class="gf-rail">';
        foreach ($items as $item) {
            // href is a trusted literal — a model value never reaches a URL sink.
            $html .= '<a class="gf-rail-item" href="#">' . Esc::text($item) . '</a>';
        }
        $html .= '</nav>';
        return $html;
    }

    /**
     * @param list<string> $cols
     * @param list<list<string>> $rows
     */
    private function panelGrid(array $cols, array $rows): string
    {
        $html = '<div class="gf-panel-grid">';
        $html .= '<div class="gf-panel">';
        $html .= '<div class="gf-panel-header">Query results</div>';
        if ($cols !== [] || $rows !== []) {
            $html .= '<table class="gf-panel-table">';
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
        }
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    private function css(): string
    {
        // Dark-dashboard palette reads as Grafana-ish (dark chrome, warm accent on panel headers) but
        // every hex is nudged off the product's exact brand tokens (its canvas/panel background
        // swatches in particular) — resemblance, not reuse.
        return 'body.gf-body{margin:0;font-family:sans-serif;background:#16171d;color:#d3d5d8}'
            . '.gf-topnav{display:flex;align-items:center;gap:14px;background:#191b21;color:#e3d3b8;'
            . 'padding:10px 16px;border-bottom:1px solid #2a2c33}'
            . '.gf-brand{font-weight:bold;color:#d98a3d}'
            . '.gf-topnav-title{color:#9ea2ab}'
            . '.gf-shell{display:flex;min-height:100vh}'
            . '.gf-rail{width:52px;background:#1e2027;border-right:1px solid #2a2c33;padding-top:10px;'
            . 'display:flex;flex-direction:column;align-items:center;gap:14px}'
            . '.gf-rail-item{color:#9ea2ab;text-decoration:none;font-size:.85em;text-align:center}'
            . '.gf-content{flex:1;padding:20px 24px}'
            . '.gf-dashboard-title{margin:0 0 6px;color:#e7e9ec}'
            . '.gf-sub{color:#9ea2ab;margin-top:0}'
            . '.gf-alert{background:#3a2f1c;border-left:4px solid #d98a3d;padding:8px 12px;margin:10px 0;'
            . 'color:#e7e9ec}'
            . '.gf-panel-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));'
            . 'gap:14px;margin-top:14px}'
            . '.gf-panel{background:#1e2027;border:1px solid #2a2c33;border-radius:4px;padding:12px}'
            . '.gf-panel-header{font-size:.85em;color:#9ea2ab;margin-bottom:8px;text-transform:uppercase}'
            . '.gf-panel-table{border-collapse:collapse;width:100%;font-size:.9em}'
            . '.gf-panel-table th,.gf-panel-table td{border:1px solid #2a2c33;padding:5px 8px;text-align:left}';
    }
}
