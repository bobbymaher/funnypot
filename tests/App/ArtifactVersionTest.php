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

    /** Editing a file under a subdirectory of $srcDir (e.g. Skins/) must change the version — the
     *  regression this fix closes: a non-recursive glob missed the Skins/ subdirectory entirely, so
     *  editing a resemblance skin never busted the cache. */
    public function test_a_file_in_a_subdirectory_changes_the_version(): void
    {
        $root = $this->makeTempTree();
        $res = $root . '/resources/llm';
        $src = $root . '/src/App/Render';

        $before = ArtifactVersion::current($res, $src, 'v1');
        $this->touchLater($src . '/Skins/FooSkin.php');
        $after = ArtifactVersion::current($res, $src, 'v1');

        self::assertNotSame($before, $after, 'a change under Skins/ must bust the artifact version');
        $this->removeTempTree($root);
    }

    /** LlmPromptBuilder.php (the source of the slot-prompt instructions) must also be a hashed input —
     *  a prompt edit must bust the html cache even though the file lives outside $srcDir. */
    public function test_prompt_builder_source_changes_the_version(): void
    {
        $root = $this->makeTempTree();
        $res = $root . '/resources/llm';
        $src = $root . '/src/App/Render';

        $before = ArtifactVersion::current($res, $src, 'v1');
        $this->touchLater($root . '/src/App/Llm/LlmPromptBuilder.php');
        $after = ArtifactVersion::current($res, $src, 'v1');

        self::assertNotSame($before, $after, 'a LlmPromptBuilder.php change must bust the artifact version');
        $this->removeTempTree($root);
    }

    /** Builds a throwaway tree shaped like the real repo (src/App/Render, src/App/Render/Skins,
     *  src/App/Llm) so the recursive-enumeration + sibling-file behaviour can be tested without
     *  touching real source file mtimes. */
    private function makeTempTree(): string
    {
        $root = sys_get_temp_dir() . '/funnypot-artifact-version-' . bin2hex(random_bytes(6));
        mkdir($root . '/resources/llm', 0777, true);
        mkdir($root . '/src/App/Render/Skins', 0777, true);
        mkdir($root . '/src/App/Llm', 0777, true);
        file_put_contents($root . '/resources/llm/page-slots.gbnf', 'root ::= "x"');
        file_put_contents($root . '/src/App/Render/GenericSkin.php', '<?php // generic');
        file_put_contents($root . '/src/App/Render/Skins/FooSkin.php', '<?php // foo');
        file_put_contents($root . '/src/App/Llm/LlmPromptBuilder.php', '<?php // prompt');
        return $root;
    }

    /** Advances a file's mtime by a full second so the change is observable regardless of filesystem
     *  mtime resolution. */
    private function touchLater(string $file): void
    {
        touch($file, filemtime($file) + 1);
        clearstatcache(true, $file);
    }

    private function removeTempTree(string $root): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($root);
    }
}
