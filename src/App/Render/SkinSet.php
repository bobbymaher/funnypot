<?php
declare(strict_types=1);
namespace Funnypot\App\Render;

/** Picks the chrome for a path. Real-analog families (wp, phpmyadmin, …) get a resemblance skin;
 *  everything else falls to the generic seed-varied skin. First match wins; order = priority. */
final class SkinSet
{
    /** @param list<Skin> $skins */
    public function __construct(private array $skins, private Skin $default)
    {
    }

    public function select(string $path): Skin
    {
        foreach ($this->skins as $skin) {
            if ($skin->matches($path)) {
                return $skin;
            }
        }
        return $this->default;
    }
}
