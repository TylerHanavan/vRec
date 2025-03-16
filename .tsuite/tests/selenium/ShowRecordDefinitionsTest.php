<?php

    function test_selenium_1($properties) {

        $selenium = $properties['selenium'];

        $selenium->get($properties['endpoint_url']);

        $login_element = $selenium->findElement(Facebook\WebDriver\Remote\WebDriverBy::id('login-link'));

        assertEquals('Login', $element->getText());

    }

?>