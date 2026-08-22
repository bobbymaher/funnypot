<?php
declare(strict_types=1);
namespace Funnypot\App\Render;

/**
 * The default chrome for any path with no closer analog: a plain internal-app look (header, nav,
 * content box, footer) built entirely from PageSlots + VisualPersona. Every CSS byte and class name
 * is seed-derived (palette()/classPrefix()) so a fixed public skin still gives each fake host its
 * own look — collapsing every host to one static stylesheet would itself be a fleet-wide fingerprint.
 */
final class GenericSkin implements Skin
{
    /** Table-cell placeholders the model may emit instead of a real secret; resolved to a
     *  persona-coherent fake before escaping — the model never invents the actual value. */
    public const MARKERS = ['APITOKEN', 'EMAIL', 'AWSKEY'];

    public function matches(string $path): bool
    {
        return true;
    }

    public function key(): string
    {
        return 'generic';
    }

    public function render(PageSlots $slots, VisualPersona $persona, string $escapedPath): string
    {
        $p = $persona->classPrefix();
        $pal = $persona->palette();

        $company = Esc::text($persona->company());
        $appName = Esc::text($slots->appName());
        $title = $slots->pageTitle() !== '' ? $slots->pageTitle() : $slots->appName();

        $html = '<!doctype html><html lang=en><head><meta charset=utf-8>'
            . '<title>' . Esc::text($title) . '</title>'
            . '<style>' . $this->css($p, $pal) . '</style>'
            . '</head><body>';

        $html .= '<header class="' . $p . '-hd">'
            . '<span class="' . $p . '-brand">' . $company . '</span>';
        if ($appName !== '') {
            $html .= ' <span class="' . $p . '-app">' . $appName . '</span>';
        }
        $html .= '</header>';

        $html .= $this->nav($p, $slots->navItems());

        $html .= '<main class="' . $p . '-box">';
        $html .= $this->heading($slots->heading());
        $html .= $this->intro($p, $slots->intro());
        $html .= $this->table($p, $slots->tableCols(), $slots->tableRows(), $persona);
        $html .= $this->form($p, $slots->formFields(), $escapedPath);
        $html .= $this->flash($p, $slots->flash());
        $html .= '</main>';

        $html .= '<footer class="' . $p . '-ft">&copy; ' . $company;
        $footerNote = $slots->footerNote();
        if ($footerNote !== '') {
            $html .= ' &middot; ' . Esc::text($footerNote);
        }
        $html .= '</footer>';

        $html .= '</body></html>';

        return $html;
    }

    /** @param array{bg:string,fg:string,accent:string,muted:string,border:string} $pal */
    private function css(string $p, array $pal): string
    {
        return "body{margin:0;font-family:sans-serif;background:{$pal['bg']};color:{$pal['fg']}}"
            . ".{$p}-hd{background:{$pal['accent']};color:#fff;padding:14px 22px}"
            . ".{$p}-app{color:#fff;opacity:.85}"
            . ".{$p}-nav{background:{$pal['bg']};border-bottom:1px solid {$pal['border']};padding:8px 22px}"
            . ".{$p}-nav a{color:{$pal['fg']};margin-right:16px;text-decoration:none}"
            . ".{$p}-box{margin:22px;padding:22px;background:{$pal['bg']};border:1px solid {$pal['border']};border-radius:6px}"
            . ".{$p}-intro{color:{$pal['muted']}}"
            . ".{$p}-table{border-collapse:collapse;width:100%;margin-top:12px}"
            . ".{$p}-table th,.{$p}-table td{border:1px solid {$pal['border']};padding:6px 10px;text-align:left}"
            . ".{$p}-form input{border:1px solid {$pal['border']};padding:4px 8px;margin:4px 0;display:block}"
            . ".{$p}-flash{margin-top:12px;padding:8px 12px;background:{$pal['accent']};color:#fff;border-radius:4px}"
            . ".{$p}-ft{padding:14px 22px;color:{$pal['muted']};font-size:.85em}";
    }

    /** @param list<string> $items */
    private function nav(string $p, array $items): string
    {
        if ($items === []) {
            return '';
        }
        $html = '<nav class="' . $p . '-nav">';
        foreach ($items as $item) {
            // href is a trusted literal — a model value is never allowed into a URL sink.
            $html .= '<a href="#">' . Esc::text($item) . '</a>';
        }
        return $html . '</nav>';
    }

    private function heading(string $heading): string
    {
        return $heading !== '' ? '<h1>' . Esc::text($heading) . '</h1>' : '';
    }

    private function intro(string $p, string $intro): string
    {
        return $intro !== '' ? '<p class="' . $p . '-intro">' . Esc::text($intro) . '</p>' : '';
    }

    /**
     * @param list<string> $cols
     * @param list<list<string>> $rows
     */
    private function table(string $p, array $cols, array $rows, VisualPersona $persona): string
    {
        if ($cols === [] && $rows === []) {
            return '';
        }
        $html = '<table class="' . $p . '-table">';
        if ($cols !== []) {
            $html .= '<thead><tr>';
            foreach ($cols as $col) {
                $html .= '<th>' . Esc::text($col) . '</th>';
            }
            $html .= '</tr></thead>';
        }
        $html .= '<tbody>';
        foreach ($rows as $i => $row) {
            $html .= '<tr>';
            foreach ($row as $j => $cell) {
                $html .= '<td>' . Esc::text($this->resolveMarker($cell, $persona, $i, $j)) . '</td>';
            }
            $html .= '</tr>';
        }
        return $html . '</tbody></table>';
    }

    /** A cell equal (after trim) to a MARKERS entry becomes a persona-coherent fake; anything else passes through untouched — escaping still happens afterward, by the caller. */
    private function resolveMarker(string $cell, VisualPersona $persona, int $i, int $j): string
    {
        $trimmed = trim($cell);
        if (!in_array($trimmed, self::MARKERS, true)) {
            return $cell;
        }
        return match ($trimmed) {
            'APITOKEN' => $persona->fakeToken("r{$i}c{$j}"),
            'EMAIL' => $persona->adminEmail(),
            'AWSKEY' => $persona->awsKey(),
        };
    }

    /** @param list<string> $fields */
    private function form(string $p, array $fields, string $escapedPath): string
    {
        if ($fields === []) {
            return '';
        }
        // $escapedPath is pre-escaped by the caller; the field name is a synthetic index, not a
        // model value, so both are safe directly in these attribute sinks.
        $html = '<form class="' . $p . '-form" method="post" action="' . $escapedPath . '">';
        foreach ($fields as $idx => $field) {
            $html .= '<label>' . Esc::text($field)
                . '<input type="text" name="f' . $idx . '"></label>';
        }
        return $html . '<button type="submit">Submit</button></form>';
    }

    private function flash(string $p, string $flash): string
    {
        return $flash !== '' ? '<div class="' . $p . '-flash">' . Esc::text($flash) . '</div>' : '';
    }
}
