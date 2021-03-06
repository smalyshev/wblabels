<?php
require_once 'Names.php';
require_once 'vendor/autoload.php';

use Wikibase\JsonDumpReader\JsonDumpFactory;

$factory = new JsonDumpFactory();

$dumpReader = $factory->newExtractedDumpReader("php://stdin");
$dumpIterator = $factory->newStringDumpIterator($dumpReader);
$api = new \Mediawiki\Api\MediawikiApi('https://www.wikidata.org/w/api.php');
$services = new \Mediawiki\Api\MediawikiFactory($api);

foreach ($dumpIterator as $jsonLine) {
    $data = json_decode($jsonLine, true);
    $page = $services->newPageGetter()->getFromTitle($data['id']);
    $record = ["title" => $data['id'], 'wiki' => 'wikidatawiki', 'namespace' => 0, 'content_model' => 'wikibase-' . $data['type']];

    foreach (\MediaWiki\Languages\Data\Names::$names as $lang => $name) {
        if (empty($data['labels'][$lang]) && empty($data['aliases'][$lang])) {
            // no data for this language
            continue;
        }
        // Label always goes first, even if none given
        if (!empty($data['labels'][$lang])) {
            $record['labels'][$lang] = [$data['labels'][$lang]['value']];
        } else {
            $record['labels'][$lang] = [""];
        }
        if (!empty($data['aliases'][$lang])) {
            foreach ($data['aliases'][$lang] as $alias) {
                $record['labels'][$lang][] = $alias['value'];
            }
        }
    }

    $record['label_count'] = count($data['labels']);
    $record['sitelink_count'] = count($data['sitelinks']);

    $id = $page->getPageIdentifier()->getId();
    echo '{"index":{"_type":"page","_id":' . $id . '}}';
    echo "\n";
    echo json_encode($record);
    echo "\n";
}
