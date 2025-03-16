<?php

    use Facebook\WebDriver\WebDriverBy;
    use Facebook\WebDriver\WebDriverExpectedCondition;

    function test_selenium_1($properties) {

        echo "Start of test\n";

        $selenium = $properties['selenium'];

        $selenium->get($properties['endpoint_url']);

        echo "Before wait\n";
        echo $selenium->getPageSource();

        $selenium->wait(30, 500)->until(
            function($selenium) {
                return $selenium->executeScript('return document.readyState') === 'complete';
            }
        );

        /*$selenium->wait(30, 500)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('login-link'))
        );*/

        echo "After wait\n";

        $login_element = $selenium->findElement(WebDriverBy::id('login-link'));

        echo "After find\n";

        if($login_element == null) throw new Exception("#login-link not found");

        assertEquals('Logint', $login_element->getText());

        echo "End of test";

    }

?>