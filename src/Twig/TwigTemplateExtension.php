<?php

declare(strict_types=1);

namespace Guave\AssetLoadBundle\Twig;

use Contao\CoreBundle\Twig\Inheritance\TemplateHierarchyInterface;
use Guave\AssetLoadBundle\Helper\TwigHelper;
use Twig\Extension\AbstractExtension;
use Twig\Loader\LoaderInterface;
use Twig\TwigFunction;

class TwigTemplateExtension extends AbstractExtension
{
    private LoaderInterface $loader;

    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('dynamic_template_path', [$this, 'getDynamicThemePath']),
            new TwigFunction('theme_slug', [$this, 'getThemeSlug']),
            new TwigFunction('analytics_id', [TwigHelper::class, 'getAnalyticsId']),
        ];
    }

    public function getDynamicThemePath(string $template, string $theme = null): string
    {
        if (!$theme) {
            $theme = $this->getThemeSlug();
        }
        $chains = $this->loader->getInheritanceChains($theme);

        if (!empty($chains) && key_exists($template, $chains)) {
            return array_shift($chains[$template]);
        }

        return $template;
    }

    public function getThemeSlug(): ?string
    {
        global $objPage;

        if ($objPage && $objPage->templateGroup) {
            return substr($objPage->templateGroup, 10); // remove templates/
        }

        return null;
    }
}
