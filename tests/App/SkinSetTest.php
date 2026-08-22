<?php
declare(strict_types=1);
namespace Funnypot\Tests\App;
use Funnypot\App\Render\{Skin, SkinSet, GenericSkin, PageSlots, VisualPersona};
use PHPUnit\Framework\TestCase;

final class SkinSetTest extends TestCase
{
    public function test_selects_first_matching_else_default(): void
    {
        $wp = new class implements Skin {
            public function matches(string $path): bool { return str_contains($path, '/wp-'); }
            public function key(): string { return 'wp'; }
            public function render(PageSlots $s, VisualPersona $p, string $ep): string { return 'WP'; }
        };
        $set = new SkinSet([$wp], new GenericSkin());
        self::assertSame('wp', $set->select('/wp-login.php')->key());
        self::assertSame('generic', $set->select('/hr/portal')->key());
    }
}
