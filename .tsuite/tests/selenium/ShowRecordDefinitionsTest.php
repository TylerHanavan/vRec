<?php

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

function test_selenium_1($properties) {

    $url = 'http://' . $properties['endpoint_url'];

    $selenium = $properties['selenium'];

    $selenium->get("$url/test");

    $selenium->wait(10, 500)->until(
        function () use ($selenium) {
            $elements = $selenium->findElements(WebDriverBy::cssSelector('div'));
    
            return count($elements) > 2;
        },
        'Error locating more than two div elements'
    );    
    
    $selenium->wait(10, 500)->until(
        function () use ($selenium) {
            $elements = $selenium->findElements(WebDriverBy::id('login-link'));
    
            return count($elements) == 1;
        },
        'Error locating login-link anchor'
    );

}
?>
