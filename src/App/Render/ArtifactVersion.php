<?php

declare(strict_types=1);

namespace Funnypot\App\Render;

/**
 * Cache-busting tag for LLM page artifacts. Derived from the grammar, every Render class's
 * mtime+size, and the prompt version, so a skin edit or grammar change can never serve a fake
 * built for the old shape out of the response cache.
 */
final class ArtifactVersion
{
    public static function current(string $resourcesDir, string $srcDir, string $promptVersion): string
    {
        $grammar = @file_get_contents(rtrim($resourcesDir, '/') . '/page-slots.gbnf');

        $files = glob(rtrim($srcDir, '/') . '/*.php') ?: [];
        sort($files);

        $fingerprint = '';
        foreach ($files as $file) {
            $fingerprint .= filemtime($file) . '.' . filesize($file);
        }

        $hash = hash('sha256', ($grammar === false ? '' : $grammar) . $fingerprint . $promptVersion);

        return 'a' . substr($hash, 0, 11);
    }
}
