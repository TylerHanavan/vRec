<?php

    use Facebook\WebDriver\WebDriverBy;
    use Facebook\WebDriver\WebDriverExpectedCondition;

    function test_selenium_1($properties) {

        echo "Start of test";

        $selenium = $properties['selenium'];

        $selenium->get($properties['endpoint_url']);

        $selenium->wait(20, 500)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('login-link'))
        );

        $login_element = $selenium->findElement(WebDriverBy::id('login-link'));

        assertEquals('Logint', $login_element->getText());

        echo "End of test";

    }

?>