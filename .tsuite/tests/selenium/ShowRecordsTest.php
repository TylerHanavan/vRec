<?php

    use Facebook\WebDriver\WebDriverBy;
    use Facebook\WebDriver\WebDriverExpectedCondition;

    function test_show_records($properties) {

        //global $session_cookie; // Not actually needed...

        $url = 'http://' . $properties['endpoint_url'] . '/admin/show-records?r=test';

        $selenium = $properties['selenium'];

        $selenium->get("$url");

        if($properties['tester']->has_driver_quit()) throw new Exception("Selenium driver quit prior to test");

        /* Check that Selenium can find more than 4 elements */
        $selenium->wait(10, 500)->until(
            function () use ($selenium) {
                $elements = $selenium->findElements(WebDriverBy::cssSelector('*'));
        
                return count($elements) > 4;
            },
            'Error locating five or more elements'
        );

        $buttons = $selenium->findElements(WebDriverBy::className('btn-primary'));

        assertTrue(isset($buttons) && is_array($buttons), 'buttons not set or is not array');

        // Find button for Add New Record
        $found_add_new_record_button = false;

        foreach($buttons as $button) {
            if($button->getText() === 'Add New Record') {
                $found_add_new_record_button = true;
            }
        }

        assertTrue($found_add_new_record_button, '\'Add New Record\' button not found');

    }

?>