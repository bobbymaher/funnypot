<?php

declare(strict_types=1);

namespace Funnypot\App\Llm;

use Funnypot\App\Render\PageShellRenderer;

/**
 * One response shape: the content kind (drives the sanitizer), the Content-Type served, the prompt
 * builder that steers the model toward that kind, and the grammar that constrains it (empty string =
 * unconstrained generation, for the types with no practical GBNF grammar). $renderer is set only on
 * the html profile when slot-based rendering is wired in; null means the legacy raw-HTML path.
 */
final class LlmResponseProfile
{
    public function __construct(
        public string $kind,
        public string $contentType,
        public LlmPromptBuilder $prompt,
        public string $grammar,
        public ?PageShellRenderer $renderer = null,
    ) {
    }
}
