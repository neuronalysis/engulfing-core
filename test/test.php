<?php
$config = json_decode(file_get_contents(__DIR__ . "/../../../config/extract-info-config.json"), true);

require_once $config['framework']['path'] . 'engulfing-core/vendor/autoload.php';
require_once __DIR__ . '/../vendor/autoload.php';

$tst = new Testing();
$tst->setConfig($config);

$config_dev = json_decode(file_get_contents(__DIR__ . "/../config/dev-config.json"), true);
$config_live = json_decode(file_get_contents(__DIR__ . "/../config/live-config.json"), true);

$doc = new Document_Test();
$doc->setConfigs(array('dev' => $config_dev, 'live' => $config_live));

$results = $doc->test();

foreach($results as $key => $class_item) {
	echo "\n\n\nclass: " . $key . "\n";
	foreach($class_item->methodAsserts as $assert_item) {
		echo $assert_item;
	}
}

/*
$resttrans = new REST_Transformer_Test();
$resttrans->setConfigs(array('dev' => $config_dev, 'live' => $config_live));

$results = $resttrans->test();

foreach($results as $key => $class_item) {
	echo "\n\n\nclass: " . $key . "\n";
	foreach($class_item->methodAsserts as $assert_item) {
		echo $assert_item;
	}
}

$website = new Website_Test();
$website->setConfigs(array('dev' => $config_dev, 'live' => $config_live));

$results = $website->test();

foreach($results as $key => $class_item) {
    echo "\n\n\nclass: " . $key . "\n";
    foreach($class_item->methodAsserts as $assert_item) {
        echo $assert_item;
    }
}


$fileio = new FileIO_Test();
$fileio->setConfigs(array('dev' => $config_dev, 'live' => $config_live));

$results = $fileio->test();

foreach($results as $key => $class_item) {
    echo "\n\n\nclass: " . $key . "\n";
    foreach($class_item->methodAsserts as $assert_item) {
        echo $assert_item;
    }
}
*/

echo "tests done\n";

?>