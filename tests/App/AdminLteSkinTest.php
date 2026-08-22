<?php
declare(strict_types=1);
namespace Funnypot\Tests\App;
use Funnypot\App\Render\Skins\AdminLteSkin;
use Funnypot\App\Render\{PageSlots, VisualPersona};
use PHPUnit\Framework\TestCase;

final class AdminLteSkinTest extends TestCase
{
    public function test_matches_admin_paths(): void
    {
        $s = new AdminLteSkin();
        self::assertTrue($s->matches('/admin/index.php'));
        self::assertTrue($s->matches('/dashboard'));
        self::assertTrue($s->matches('/manage/users'));
        self::assertFalse($s->matches('/hr/portal'));
    }

    public function test_key_is_adminlte(): void
    {
        self::assertSame('adminlte', (new AdminLteSkin())->key());
    }

    public function test_resembles_adminlte_and_escapes(): void
    {
        $html = (new AdminLteSkin())->render(
            PageSlots::fromArray([
                'heading' => '<x onerror=1>',
                'app_name' => 'Ops Console',
                'nav_items' => ['Dashboard', 'Users'],
                'table' => ['cols' => ['id', 'user'], 'rows' => [['1', 'bob']]],
            ]),
            VisualPersona::fromSeed(4), '/admin/index.php'
        );
        self::assertStringStartsWith('<!doctype html>', $html);
        self::assertStringContainsString('sidebar', strtolower($html)); // resemblance marker
        self::assertStringNotContainsString('<x onerror', $html);       // escaping holds
    }
}
