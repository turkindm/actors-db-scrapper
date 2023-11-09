<?php

namespace Src;

use DOMAttr;
use Symfony\Component\DomCrawler\Crawler;

class Parser
{
    public function run()
    {
        $baseUrl = 'https://www.kinopoisk.ru';
        $actorsUrl = $baseUrl . '/film/5051069/cast/';

        $options = [
            'http' => [
                'method' => "GET",
                'header' => 'Accept-language: ru\r\n' .
                    'Cookie: foo=bar\r\n' .
                    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',
            ],
        ];
        $context = stream_context_create($options);

//        $pageHtml = file_get_contents($actorsUrl, false, $context);
        $pageHtml = file_get_contents('actors-page.html');

        $crawler = new Crawler($pageHtml);

        $links = [];
        $actorsFound = false;
        $items = $crawler->filter('.block_left')->children();
        foreach ($items as $item) {
            if ($actorsFound) {
                if ($item->nodeName === 'div') {
                    /** @var DOMAttr $attr */
                    $attr = $item->attributes['class'];
                    if (strstr($attr->value, 'dub')) {
                        // get link
                    }
                }
            }
            if ($item->nodeName === 'a') {
                if (!$item->hasAttributes()) {
                    continue;
                }

                if ($actorsFound) {
                    $actorsFound = false;
                }

                /** @var DOMAttr $attr */
                $attr = $item->attributes['name'];
                if ($attr->value === 'actor') {
                    $actorsFound = true;
                }
            }
        }
    }
}