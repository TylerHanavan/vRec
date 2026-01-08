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

        // Fetch all three elements into an array using one JS execution
        $elements = $selenium->executeScript("
            return [
                document.querySelector('input[name=\"t1\"]'),
                document.querySelector('input[name=\"t2\"]'),
                document.querySelector('input[name=\"t3\"]')
            ];
        ");

        $input1 = $elements[0];
        $input2 = $elements[1];
        $input3 = $elements[2];

        $submit_button = $selenium->findElement(
            WebDriverBy::xpath("//button[text()='Submit']")
        );

        for($x = 0; $x < 3; $x++) {

            $input1->sendKeys('1');

            $input2->sendKeys('3');
            
            $input3->sendKeys('6');

            // Verify values via JS (1 network trip instead of 3)
            $valuesMatch = $selenium->executeScript("
                return document.getElementsByName('t1')[0].value === '1' &&
                    document.getElementsByName('t2')[0].value === '3' &&
                    document.getElementsByName('t3')[0].value === '6';
            ");
            assertTrue($valuesMatch, 'Input values did not match expected data before submit');

            $submit_button->click();

            $selenium->wait(5, 100)->until(
                function () use ($input1) {
                    // We wait until the value attribute is empty
                    return $input1->getAttribute('value') === '';
                },
                'Form was not cleared by JavaScript after submission'
            );
            
            // 5. Final verification via JS (1 network trip instead of 3)
            $isCleared = $selenium->executeScript("
                return document.getElementsByName('t1')[0].value === '' &&
                    document.getElementsByName('t2')[0].value === '' &&
                    document.getElementsByName('t3')[0].value === '';
            ");
            assertTrue($isCleared, 'Form was not cleared after submit');
            
        }

    }

?>