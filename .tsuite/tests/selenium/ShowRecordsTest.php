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
        $selenium->wait(10, 50)->until(
            function () use ($selenium) {
                $elements = $selenium->findElements(WebDriverBy::cssSelector('*'));
        
                return count($elements) > 4;
            },
            'Error locating five or more elements'
        );

        $add_new_record_modals = $selenium->findElements(WebDriverBy::id('add-new-record-modal'));
        
        assertTrue(isset($add_new_record_modals) && is_array($add_new_record_modals) && sizeof($add_new_record_modals) === 1, 'the search for #add-new-record-modal did not yield one result');

        $add_new_record_modal = $add_new_record_modals[0];

        assertFalse($add_new_record_modal->isDisplayed(), 'modal is visible but expected it to be invisible');

        $buttons = $selenium->findElements(WebDriverBy::className('btn-primary'));

        assertTrue(isset($buttons) && is_array($buttons), 'buttons not set or is not array');

        // Find button for Add New Record
        $found_add_new_record_button = false;

        $add_new_record_button = null;

        foreach($buttons as $button) {
            if($button->getText() === 'Add New Record') {
                $found_add_new_record_button = true;
                $add_new_record_button = $button;
            }
        }

        assertTrue($found_add_new_record_button, '\'Add New Record\' button not found');

        $add_new_record_button->click();

        $selenium->wait(10, 50)->until(
            WebDriverExpectedCondition::visibilityOf($add_new_record_modal),
            'Modal did not become visible after clicking #add-new-record-modal'
        );

        assertTrue($add_new_record_modal->isDisplayed(), 'modal is invisible but expected it to be visible');

        $input1 = $selenium->findElement(WebDriverBy::cssSelector('input.form-control[name="t1"]'));
        $input2 = $selenium->findElement(WebDriverBy::cssSelector('input.form-control[name="t2"]'));
        $input3 = $selenium->findElement(WebDriverBy::cssSelector('input.form-control[name="t3"]'));

        $submit_button = $selenium->findElement(
            WebDriverBy::xpath("//button[text()='Submit']")
        );

        for($x = 0; $x < 3; $x++) {

            $input1->click();
            $input1->sendKeys('1');

            $input2->click();
            $input2->sendKeys('3');
            
            $input3->click();
            $input3->sendKeys('6');

            assertEquals($input1->getAttribute('value'), '1');
            assertEquals($input2->getAttribute('value'), '3');
            assertEquals($input3->getAttribute('value'), '6');

            $submit_button->click();

            $selenium->wait(5, 100)->until(
                function () use ($input1) {
                    // We wait until the value attribute is empty
                    return $input1->getAttribute('value') === '';
                },
                'Form was not cleared by JavaScript after submission'
            );

            assertEquals($input1->getAttribute('value'), '');
            assertEquals($input2->getAttribute('value'), '');
            assertEquals($input3->getAttribute('value'), '');
            
        }

    }

?>