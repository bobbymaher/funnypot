<?php
declare(strict_types=1);
namespace Funnypot\App\Render;

/**
 * Model-supplied page content, decoded and made safe for the renderer: every field has a typed
 * default and every list is capped, so a thin or mistyped model response can never make the shell
 * throw (the LLM tier must only ever upgrade a 404, never 500).
 */
final class PageSlots
{
    private function __construct(
        private string $appName,
        private string $pageTitle,
        private string $heading,
        private string $intro,
        /** @var list<string> */ private array $navItems,
        /** @var list<string> */ private array $tableCols,
        /** @var list<list<string>> */ private array $tableRows,
        /** @var list<string> */ private array $formFields,
        private string $flash,
        private string $footerNote,
    ) {
    }

    /** @param array<mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            self::str($d, 'app_name'),
            self::str($d, 'page_title'),
            self::str($d, 'heading'),
            self::str($d, 'intro'),
            self::strList($d['nav_items'] ?? null, 5),
            self::strList(is_array($d['table'] ?? null) ? ($d['table']['cols'] ?? null) : null, 4),
            self::rows(is_array($d['table'] ?? null) ? ($d['table']['rows'] ?? null) : null),
            self::strList($d['form_fields'] ?? null, 4),
            self::str($d, 'flash'),
            self::str($d, 'footer_note'),
        );
    }

    public function appName(): string { return $this->appName; }
    public function pageTitle(): string { return $this->pageTitle; }
    public function heading(): string { return $this->heading; }
    public function intro(): string { return $this->intro; }
    /** @return list<string> */ public function navItems(): array { return $this->navItems; }
    /** @return list<string> */ public function tableCols(): array { return $this->tableCols; }
    /** @return list<list<string>> */ public function tableRows(): array { return $this->tableRows; }
    /** @return list<string> */ public function formFields(): array { return $this->formFields; }
    public function flash(): string { return $this->flash; }
    public function footerNote(): string { return $this->footerNote; }

    public function hasBody(): bool
    {
        return $this->intro !== '' || $this->navItems !== [] || $this->tableRows !== []
            || $this->formFields !== [];
    }

    /** @param array<mixed> $d */
    private static function str(array $d, string $k): string
    {
        $v = $d[$k] ?? null;
        return is_string($v) ? $v : '';
    }

    /** @param mixed $v @return list<string> */
    private static function strList($v, int $cap): array
    {
        if (!is_array($v)) {
            return [];
        }
        $out = [];
        foreach ($v as $item) {
            if (is_string($item)) {
                $out[] = $item;
            }
            if (count($out) >= $cap) {
                break;
            }
        }
        return $out;
    }

    /** @param mixed $v @return list<list<string>> */
    private static function rows($v): array
    {
        if (!is_array($v)) {
            return [];
        }
        $out = [];
        foreach ($v as $row) {
            $out[] = self::strList($row, 4);
            if (count($out) >= 3) {
                break;
            }
        }
        return $out;
    }
}
