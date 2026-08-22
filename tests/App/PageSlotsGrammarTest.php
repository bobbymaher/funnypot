<?php
declare(strict_types=1);
namespace Funnypot\Tests\App;
use PHPUnit\Framework\TestCase;

final class PageSlotsGrammarTest extends TestCase
{
    private function grammar(): string
    {
        return (string) file_get_contents(dirname(__DIR__, 2) . '/resources/llm/page-slots.gbnf');
    }

    public function test_grammar_is_present_and_locks_the_key_names(): void
    {
        $g = $this->grammar();
        self::assertNotSame('', $g);
        foreach (['"app_name"', '"page_title"', '"heading"', '"nav_items"', '"table"'] as $key) {
            self::assertStringContainsString($key, $g, "grammar must literal-lock {$key}");
        }
    }

    public function test_schar_charset_excludes_angle_brackets(): void
    {
        $g = $this->grammar();
        self::assertMatchesRegularExpression('/schar\s*::=\s*\[[^\]]*\]/', $g);
        // The line that defines schar must not contain < or >.
        foreach (explode("\n", $g) as $line) {
            if (strpos($line, 'schar') === 0) {
                self::assertStringNotContainsString('<', $line);
                self::assertStringNotContainsString('>', $line);
            }
        }
    }

    public function test_alternations_are_single_line(): void
    {
        // llama.cpp GBNF: a rule's alternation must be on one line. No rule line may end with '|'.
        foreach (explode("\n", $this->grammar()) as $line) {
            self::assertStringEndsNotWith('|', rtrim($line));
        }
    }
}
