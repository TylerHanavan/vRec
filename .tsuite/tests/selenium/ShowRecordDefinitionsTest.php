<?php

    function test_selenium_1($properties) {

        $selenium = $properties['selenium'];

        $selenium->get($properties['endpoint_url']);

        assertEquals('Test', $selenium->getTitle());

    }

?>