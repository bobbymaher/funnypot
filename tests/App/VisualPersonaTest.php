<?php
declare(strict_types=1);
namespace Funnypot\Tests\App;
use Funnypot\App\Render\VisualPersona;
use PHPUnit\Framework\TestCase;

final class VisualPersonaTest extends TestCase
{
    public function test_deterministic_per_seed(): void
    {
        $a = VisualPersona::fromSeed(123);
        $b = VisualPersona::fromSeed(123);
        self::assertSame($a->classPrefix(), $b->classPrefix());
        self::assertSame($a->palette(), $b->palette());
        self::assertSame($a->fakeToken('cell00'), $b->fakeToken('cell00'));
    }

    public function test_different_seeds_diverge(): void
    {
        self::assertNotSame(VisualPersona::fromSeed(1)->classPrefix(), VisualPersona::fromSeed(2)->classPrefix());
        self::assertNotSame(VisualPersona::fromSeed(1)->palette()['accent'], VisualPersona::fromSeed(2)->palette()['accent']);
    }

    public function test_palette_is_hex_and_token_shape(): void
    {
        $p = VisualPersona::fromSeed(7);
        foreach ($p->palette() as $c) {
            self::assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $c);
        }
        self::assertMatchesRegularExpression('/^tok_[0-9a-f]{12}$/', $p->fakeToken('x'));
        self::assertMatchesRegularExpression('/^fp-[0-9a-f]{4}$/', $p->classPrefix());
    }
}
