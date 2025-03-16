<?php

    use Facebook\WebDriver\WebDriverBy;
    use Facebook\WebDriver\WebDriverExpectedCondition;

    function test_selenium_1($properties) {

        $selenium = $properties['selenium'];

        $selenium->get($properties['endpoint_url']);

        var_dump($properties);

        $selenium->wait(20, 500)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('login-link'))
        );

    }

?>