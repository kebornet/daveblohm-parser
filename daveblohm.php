<?php

require_once __DIR__ . '/src/DaveBlohm.php';

set_time_limit(0);

function main()
{
    $parser = new DaveBlohm();

    try {
        $bibsInfo = $parser->getInfoBibs();
    } catch (Exception $e) {
        throw new Exception('Error: ' .  $e->getMessage());
    }

    $fieldsCsvFile =  [
        'Race Type',
        'Race',
        'Picture'
    ];

    $bibsInfoCsv = fopen(dirname(__FILE__) . "/result/bibsInfo.csv", 'w');

    if (!$bibsInfoCsv) {
        throw new Exception('File csv not found.');
    }

    fputcsv($bibsInfoCsv, $fieldsCsvFile);

    foreach ($bibsInfo as $bibInfoRaceTypes) {
        foreach ($bibInfoRaceTypes as $bibInfoRace) {
        fputcsv($bibsInfoCsv, $bibInfoRace);
        }
    }

    fclose($bibsInfoCsv);
}

main();
