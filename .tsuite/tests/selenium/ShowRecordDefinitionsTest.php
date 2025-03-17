<?php

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

function test_selenium_1($properties) {

    echo "Start of test\n";

    $selenium = $properties['selenium'];

    $selenium->get('http://tsuite02:1347/test');

    echo $properties['endpoint_url'] . "\n";

    echo "Before wait\n";

    $selenium->wait(15, 500)->until(
        function () use ($selenium) {
            $elements = $selenium->findElements(WebDriverBy::cssSelector('div'));
    
            return count($elements) > 2;
        },
        'Error locating more than two div elements'
    );

    echo "End of test";

}
?>
