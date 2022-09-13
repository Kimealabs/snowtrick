<?php

namespace App\Twig;

use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;

class Youtube extends AbstractExtension
{
    public function getFilters()
    {
        return [new TwigFilter('formatYoutube', [$this, 'formatYoutube'])];
    }

    public function formatYoutube($url)
    {
        if (preg_match('#youtu#', $url)) {
            $url = parse_url($url, PHP_URL_PATH);
            $url = str_replace("/", "", $url);
        }

        return $url;
    }
}
