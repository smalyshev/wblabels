<?php
require_once 'Names.php';

$template = json_decode(file_get_contents('label.map'), true);

if (!$template) {
    die("Bad template");
}

$labels = [];
foreach (\MediaWiki\Languages\Data\Names::$names as $lang => $name) {
    $labels[$lang] = $template['labels']['properties']['en'];
}

$template['labels']['properties'] = $labels;
echo json_encode(["properties" => $template], JSON_PRETTY_PRINT);