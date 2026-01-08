<?php

    use Facebook\WebDriver\WebDriverBy;
    use Facebook\WebDriver\WebDriverExpectedCondition;

    function test_show_records($properties) {

        //global $session_cookie; // Not actually needed...

        $url = 'http://' . $properties['endpoint_url'] . '/admin/show-records?r=test';

        $selenium = $properties['selenium'];

        $selenium->get("$url");

        if($properties['tester']->has_driver_quit()) throw new Exception("Selenium driver quit prior to test");

        $add_new_record_button = $selenium->wait(5, 100)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath('//button[text()=\'Add New Record\']')),
            'Add New Record button not found'
        );

        $add_new_record_modal = $selenium->findElement(WebDriverBy::id('add-new-record-modal'));

        assertTrue(isset($add_new_record_modal), 'Add New Record modal not found');

        $add_new_record_button->click();

        $selenium->wait(10, 100)->until(
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

            $input1->sendKeys('1');

            $input2->sendKeys('3');
            
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