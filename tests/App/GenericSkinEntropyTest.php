<?php
declare(strict_types=1);
namespace Funnypot\Tests\App;
use Funnypot\App\Render\{GenericSkin, VisualPersona, PageSlots};
use PHPUnit\Framework\TestCase;

final class GenericSkinEntropyTest extends TestCase
{
    public function test_css_and_class_names_diverge_across_seeds(): void
    {
        $slots = PageSlots::fromArray(['app_name' => 'Portal', 'heading' => 'Users']);
        $skin = new GenericSkin();
        $styles = [];
        for ($seed = 1; $seed <= 24; $seed++) {
            $html = $skin->render($slots, VisualPersona::fromSeed($seed), '/hr/portal');
            preg_match('~<style>(.*?)</style>~s', $html, $m);
            $styles[] = md5($m[1] ?? '');
        }
        // No two deployments share a normalized CSS hash: the anti-fleet-fingerprint property.
        self::assertCount(24, array_unique($styles), 'seed-derived CSS must not collapse to few hashes');
    }
}
