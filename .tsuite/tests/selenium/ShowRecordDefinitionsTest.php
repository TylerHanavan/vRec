<?php

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

function test_selenium_1($properties) {

    echo "Start of test\n";

    $selenium = $properties['selenium'];

    $selenium->get('tsuite02:1347/test');

    echo $properties['endpoint_url'] . "\n";

    echo "Before wait\n";

    // Wait until a specific element is present (adjust the element's selector as needed)
    $selenium->wait(10, 500)->until(
        WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::tagName('body'))
    );

    echo "After wait\n";

    // You can check for JavaScript-rendered content here by waiting for an element or using executeScript

    echo $selenium->getPageSource();

    sleep(20);

    $element = WebDriverBy::tagName('body');

    var_dump($element);

    echo "End of test";

}
?>
