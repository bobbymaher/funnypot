<?php
declare(strict_types=1);
namespace Funnypot\Tests\App;
use Funnypot\App\Render\ArtifactVersion;
use PHPUnit\Framework\TestCase;

final class ArtifactVersionTest extends TestCase
{
    public function test_shape_and_changes_with_prompt_version(): void
    {
        $res = dirname(__DIR__, 2) . '/resources/llm';
        $src = dirname(__DIR__, 2) . '/src/App/Render';
        $a = ArtifactVersion::current($res, $src, 'v1');
        $b = ArtifactVersion::current($res, $src, 'v2');
        self::assertMatchesRegularExpression('/^a[0-9a-f]{11}$/', $a);
        self::assertNotSame($a, $b);
    }
}
