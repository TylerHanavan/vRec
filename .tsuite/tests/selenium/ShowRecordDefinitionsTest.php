<?php

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

function test_selenium_1($properties) {

    echo "Start of test\n";

    $selenium = $properties['selenium'];

    $selenium->get('tsuite02:1347/test');

    echo $properties['endpoint_url'] . "\n";

    echo "Before wait\n";

    $driver->wait()->until(
        function () use ($driver) {
            $elements = $driver->findElements(WebDriverBy::cssSelector('div'));
    
            return count($elements) > 2;
        },
        'Error locating more than five elements'
    );

    echo "End of test";

}
?>
