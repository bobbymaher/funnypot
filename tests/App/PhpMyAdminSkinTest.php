<?php
declare(strict_types=1);
namespace Funnypot\Tests\App;
use Funnypot\App\Render\Skins\PhpMyAdminSkin;
use Funnypot\App\Render\{PageSlots, VisualPersona};
use PHPUnit\Framework\TestCase;

final class PhpMyAdminSkinTest extends TestCase
{
    public function test_matches_phpmyadmin_paths(): void
    {
        $s = new PhpMyAdminSkin();
        self::assertTrue($s->matches('/phpmyadmin/index.php'));
        self::assertTrue($s->matches('/pma/index.php'));
        self::assertTrue($s->matches('/PMA/index.php'));
        self::assertFalse($s->matches('/hr/portal'));
    }

    public function test_key_is_phpmyadmin(): void
    {
        self::assertSame('phpmyadmin', (new PhpMyAdminSkin())->key());
    }

    public function test_resembles_phpmyadmin_and_escapes(): void
    {
        $html = (new PhpMyAdminSkin())->render(
            PageSlots::fromArray([
                'heading' => '<x onerror=1>',
                'app_name' => 'DB Admin',
                'table' => ['cols' => ['id', 'name'], 'rows' => [['1', 'alice']]],
            ]),
            VisualPersona::fromSeed(4), '/phpmyadmin/index.php'
        );
        self::assertStringStartsWith('<!doctype html>', $html);
        self::assertStringContainsString('phpmyadmin', strtolower($html)); // resemblance marker
        self::assertStringNotContainsString('<x onerror', $html);          // escaping holds
    }
}
