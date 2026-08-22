<?php
declare(strict_types=1);
namespace Funnypot\Tests\App;
use Funnypot\App\Render\PageSlots;
use PHPUnit\Framework\TestCase;

final class PageSlotsTest extends TestCase
{
    public function test_defaults_when_empty(): void
    {
        $s = PageSlots::fromArray([]);
        self::assertSame('', $s->heading());
        self::assertSame([], $s->navItems());
        self::assertSame([], $s->tableRows());
        self::assertFalse($s->hasBody());
    }

    public function test_wrong_types_do_not_throw_and_coerce_to_defaults(): void
    {
        $s = PageSlots::fromArray(['table' => 'none', 'nav_items' => 'x', 'heading' => 42]);
        self::assertSame([], $s->tableRows());
        self::assertSame([], $s->navItems());
        self::assertSame('', $s->heading());
    }

    public function test_caps_are_enforced(): void
    {
        $s = PageSlots::fromArray([
            'nav_items' => ['a', 'b', 'c', 'd', 'e', 'f', 'g'],
            'table' => ['cols' => ['1','2','3','4','5'], 'rows' => [['a'],['b'],['c'],['d']]],
        ]);
        self::assertCount(5, $s->navItems());
        self::assertCount(4, $s->tableCols());
        self::assertCount(3, $s->tableRows());
    }
}
