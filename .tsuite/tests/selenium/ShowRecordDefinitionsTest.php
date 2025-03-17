<?php

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

function test_selenium_1($properties) {

    $selenium = $properties['selenium'];

    $selenium->get('http://tsuite02:1347/test');

    $selenium->wait(15, 500)->until(
        function () use ($selenium) {
            $elements = $selenium->findElements(WebDriverBy::cssSelector('div'));
    
            return count($elements) > 2;
        },
        'Error locating more than two div elements'
    );

}
?>
