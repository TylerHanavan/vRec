<?php

    use Facebook\WebDriver\Remote\WebDriverBy;

    function test_selenium_1($properties) {

        $selenium = $properties['selenium'];

        $selenium->get($properties['endpoint_url']);

        $login_element = $selenium->findElement(WebDriverBy::id('login-link'));

        assertEquals('Login', $element->getText());

    }

?>