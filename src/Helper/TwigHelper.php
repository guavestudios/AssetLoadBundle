<?php

declare(strict_types=1);

namespace Guave\AssetLoadBundle\Helper;

use Contao\System;

class TwigHelper
{
    public static function getAnalyticsId(): string|null
    {
        global $objPage;
        $tagManager = System::getContainer()->getParameter('googleTagManager');

        if (empty($tagManager)) {
            return null;
        }

        $layout = $objPage->getRelated('layout');

        if (empty($layout)) {
            return null;
        }

        $themeName = $layout->getRelated('pid')->name;

        return $tagManager[$themeName];
    }
}
