<?php

namespace Src;

use DiDom\Document;
use DiDom\Element;

class Parser
{
    public function run()
    {
        $baseUrl = 'https://www.imdb.com';
        $actorsUrl = $baseUrl . '/title/tt0499549/fullcredits?ref_=tt_cl_sm';

        $document = new Document();
        $document->loadHtmlFile($actorsUrl);

        $actorPageUrls = [];
        /** @var Element $actorsList */
        $actorsList = $document->find('.cast_list')[0]; // на странице только один блок cast_list
        foreach ($actorsList->find('.primary_photo') as $actor) {
            $path = $actor->find('a')[0]->getAttribute('href');
            $actorPageUrls[] = $baseUrl . $path;
        }
    }
}