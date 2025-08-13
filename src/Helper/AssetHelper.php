<?php

declare(strict_types=1);

namespace Guave\AssetLoadBundle\Helper;

use Contao\System;
use DOMDocument;
use Exception;
use RuntimeException;

class AssetHelper
{
    public static function assets(string $fileName): string
    {
        $filesDir = System::getContainer()->getParameter('contao.localconfig')['assetPath'];
        $assetPath = $filesDir.'/'.$fileName;
        $manifest = json_decode(
            file_get_contents(
                $_SERVER['DOCUMENT_ROOT'].'/'.$filesDir.'/dist/manifest.json'
            ),
            true
        );

        return $manifest[$assetPath];
    }

    /**
     * @throws Exception
     */
    public static function loadJsViaEntrypoints(string $entrypoint): string
    {
        return self::loadEntrypoint($entrypoint, 'js');
    }

    /**
     * @throws Exception
     */
    public static function loadCssViaEntrypoints(string $entrypoint): string
    {
        return self::loadEntrypoint($entrypoint, 'css');
    }

    public static function loadEntrypoint(string $entrypoint, string $resourceType): string
    {
        $rootDir = System::getContainer()->getParameter('kernel.project_dir');
        $assetPath = System::getContainer()->getParameter('contao.localconfig')['assetPath'];
        $path = $rootDir.'/'.$assetPath.'/dist/entrypoints.json';

        if (!is_file($path)) {
            throw new RuntimeException('entrypoints.json not found. did you run the build?');
        }

        $entrypoints = json_decode(file_get_contents($path), true);

        if (!isset($entrypoints['entrypoints'][$entrypoint][$resourceType])) {
            return "<!-- WARNING: $entrypoint not found in entrypoints.json for $resourceType -->";
        }

        $resources = [];

        foreach ($entrypoints['entrypoints'][$entrypoint][$resourceType] as $path) {
            $resources[] = self::renderResource($resourceType, $path);
        }

        return implode('', $resources);
    }

    public static function loadSvg(string $filePath, string $class = '', bool $silent = false): bool|string
    {
        $rootDir = System::getContainer()->getParameter('kernel.project_dir');
        $filePath = $filePath[0] === '/' ? $filePath : '/'.$filePath;
        $filePath = $rootDir.$filePath;

        if ($filePath === '' || !isset($filePath)) {
            return '';
        }

        if (!is_file($filePath)) {
            if ($silent) {
                return '';
            }

            return 'file does not exist: '.$filePath;
        }

        if ($class) {
            $svg = file_get_contents($filePath);
            $dom = new DOMDocument();
            // this is necessary because for some reason DOMDocument can't handle the truth!!! I mean SVG ;)
            libxml_use_internal_errors(true);
            $dom->loadHTML($svg);

            foreach ($dom->getElementsByTagName('svg') as $element) {
                $classes = $element->getAttribute('class') ?: '';
                $element->setAttribute('class', "$classes $class");
            }

            return $dom->saveHTML();
        }

        return file_get_contents($filePath);
    }

    protected static function renderResource(string $type, string $path): string
    {
        $hash = System::getContainer()->getParameter('contao.localconfig')['gitHash'];

        $version = '';
        if ($hash) {
            $version = '?version='.$hash;
        }

        return match ($type) {
            'css' => '<link type="text/css" href="' . $path . $version . '" rel="stylesheet">' . "\n",
            'js' => '<script src="' . $path . $version . '"></script>' . "\n",
            default => '<!-- don\'t know how to render "'.$type.'" -->' . "\n",
        };
    }
}
