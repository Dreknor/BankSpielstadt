<?php

namespace App\Helpers;

class Vite
{
    private static ?array $manifest = null;

    /**
     * Gibt die HTML-Tags für die übergebenen Vite-Assets zurück.
     *
     * @param  array|string  $entrypoints
     */
    public static function tags(array|string $entrypoints): string
    {
        $manifest = static::manifest();
        $entrypoints = (array) $entrypoints;
        $tags = [];

        foreach ($entrypoints as $entry) {
            $entry = ltrim($entry, '/');
            $chunk = $manifest[$entry] ?? null;

            if ($chunk === null) {
                continue;
            }

            if (str_ends_with($chunk['file'], '.css')) {
                $tags[] = '<link rel="stylesheet" href="' . asset('build/' . $chunk['file']) . '">';
            } elseif (str_ends_with($chunk['file'], '.js')) {
                $tags[] = '<script type="module" src="' . asset('build/' . $chunk['file']) . '"></script>';
            }

            foreach ($chunk['css'] ?? [] as $css) {
                $tags[] = '<link rel="stylesheet" href="' . asset('build/' . $css) . '">';
            }
        }

        return implode("\n    ", $tags);
    }

    private static function manifest(): array
    {
        if (static::$manifest !== null) {
            return static::$manifest;
        }

        $path = public_path('build/manifest.json');

        if (! file_exists($path)) {
            return static::$manifest = [];
        }

        return static::$manifest = json_decode(file_get_contents($path), true);
    }
}

