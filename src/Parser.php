<?php

namespace Src;

use DiDom\Document;

class Parser
{
    public function run()
    {
        $baseUrl = 'https://www.imdb.com';

        $actorsStorageFile = __DIR__ . '/../data/actors.json';
        $actorsDataList = [];
        if (file_exists($actorsStorageFile)) {
            $actorsDataList = json_decode(file_get_contents($actorsStorageFile), true) ?? [];
        }

        $progressDumpFile = __DIR__ . '/../data/progress.json';
        $defaultProgress = [
            'lastSeen' => [
                'sourceId' => 1,
            ]
        ];
        $progress = $defaultProgress;
        if (file_exists($progressDumpFile)) {
            $progress = json_decode(file_get_contents($progressDumpFile), true) ?? $defaultProgress;
        }

        $personMaxIndex = 9999999;
        $personStartIndex = $progress['lastSeen']['sourceId'] ?? 1;

        if ($personStartIndex === 1) {
            echo "start parsing\n";
        } else {
            echo "continue parsing from $personStartIndex\n";
        }

        for ($i = $personStartIndex; $i <= $personMaxIndex; $i++) {
            $sourceId = sprintf('%07d', $i);
            echo "\n";
            echo "try person ID: $sourceId\n";

            $exists = false;
            foreach ($actorsDataList as $data) {
                if ($data['sourceId'] === $sourceId) {
                    $exists = true;
                    break;
                }
            }

            if ($exists) {
                echo "skip: already exists\n";
                continue;
            }

            $document = new Document();
            $url = $baseUrl . '/name/nm' . $sourceId;
            echo "request $url\n";
            $document->loadHtmlFile($url);

            // Check is actor
            $roleElements = $document->find(
                'section > div:nth-child(4) > section > section > div> div > ul'
            )[0] ?? null;
            if ($roleElements === null) {
                echo "skip: can't determine person's role\n";
                continue;
            }
            if (!(stristr($roleElements->text(), 'actor') || stristr($roleElements->text(), 'actress'))) {
                echo "skip: isn't actor\n";
                continue;
            }

            $nameElement = $document->find('section > div > div > h1 > span')[0] ?? null;
            if ($nameElement === null) {
                continue;
            }
            $name = $nameElement->text();

            $photoElement = $document->find(
                'section > div:nth-child(4) > section > section > div > div > div > div > div > img'
            )[0];
            $photo = $photoElement?->getAttribute('src') ?? '';

            $bioUrl = $url . '/bio';
            echo "request $bioUrl\n";
            $document->loadHtmlFile($bioUrl);

            $overviewElement = $document->find(
                'section > div > section > div > div > section:nth-child(2) > div > ul'
            )[0];

            $bornInfo = '';
            foreach ($overviewElement?->children() as $prop) {
                $propName = $prop->find('span')[0];
                if ($propName?->text() === 'Born') {
                    $bornInfo = $prop->find('div')[0]?->text() ?? '';
                }
            }

            $bio = $document->find('#mini_bio_0 > div > ul > div:nth-child(1) > div')[0]?->text() ?? '';

            $actorData = [
                'sourceId' => $sourceId,
                'name' => $name,
                'photo' => $photo,
                'born' => $bornInfo,
                'bio' => $bio,
            ];

            // Save actor
            $actorsDataList[] = $actorData;
            echo "save actor: $name\n";
            file_put_contents($actorsStorageFile, json_encode($actorsDataList));

            // Dump progress
            $progress['lastSeen']['sourceId'] = $i;
            file_put_contents($progressDumpFile, json_encode($progress));
        }
    }
}