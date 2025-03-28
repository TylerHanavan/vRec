<?php

    use Facebook\WebDriver\WebDriverBy;
    use Facebook\WebDriver\WebDriverExpectedCondition;

    function test_login_link_and_modal($properties) {

        $url = 'http://' . $properties['endpoint_url'];

        $selenium = $properties['selenium'];

        $selenium->get("$url/test");

        if($properties['tester']->has_driver_quit()) throw new Exception("Selenium driver quit prior to test");

        /* Check that Selenium can find more than 4 elements */
        $selenium->wait(10, 500)->until(
            function () use ($selenium) {
                $elements = $selenium->findElements(WebDriverBy::cssSelector('*'));
        
                return count($elements) > 4;
            },
            'Error locating five or more elements'
        );
        
        /* Check that Selenium can locate #login-link and that it's text says 'Login' */
        $selenium->wait(10, 500)->until(
            function () use ($selenium) {
                $elements = $selenium->findElements(WebDriverBy::id('login-link'));
        
                return count($elements) == 1 && $elements[0]->getText() == 'Login';
            },
            'Error locating #login-link anchor'
        );
        
        /* Check that the element .modal exists */
        $selenium->wait(10, 500)->until(
            function () use ($selenium) {
                $elements = $selenium->findElements(WebDriverBy::className('modal'));
        
                return count($elements) != 0;
            },
            'Error location .modal'
        );
        
        /* Ensure there is only one .modal */
        $selenium->wait(10, 500)->until(
            function () use ($selenium) {
                $elements = $selenium->findElements(WebDriverBy::className('modal'));
        
                return count($elements) == 1;
            },
            'More than one .modal found'
        );

        $modal = $selenium->findElement(WebDriverBy::className('modal'));

        $login_anchor = $selenium->findElement(WebDriverBy::id('login-link'));

        /* Click the #login-link anchor to activate the modal */
        $login_anchor->click();
        
        /* Ensure the modal becomes visible */
        $selenium->wait(10, 500)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('.modal')),
            'Modal did not become visible after clicking #login-link'
        );

        /* Find all children of the .modal */
        $modal_children = $modal->findElements(WebDriverBy::cssSelector('*'));

        /* Throw an exception if the .modal has no children */
        if(!isset($modal_children) || count($modal_children) == 0)
            throw new Exception('.modal has no children');

        /* Find the first child of the modal (.modal_dialog) */
        $modal_dialog = $modal_children[0];
        $modal_dialog_class = $modal_dialog->getAttribute('class');

        /* Ensure .modal-dialog has a class(es) */
        if(!isset($modal_dialog_class) || $modal_dialog_class == null)
            throw new Exception('Expected .modal-dialog but it has no classes');

        /* Ensure .modal-dialog is attributed as such and is a div */
        assertEquals('div', $modal_dialog->getTagName());
        assertArrayContains('modal-dialog', explode(' ', $modal_dialog_class));

        /* Find all .modal-dialog children */
        $modal_dialog_children = $modal_dialog->findElements(WebDriverBy::cssSelector('*'));

        /* Throw an exception if .modal-dialog has no children */
        if(!isset($modal_dialog_children) || count($modal_dialog_children) == 0)
            throw new Exception('.modal-dialog has no children');

        /* Find the .modal-dialog first child (.modal-content) */
        $modal_content = $modal_dialog_children[0];
        $modal_content_class = $modal_content->getAttribute('class');

        /* Throw an exception if the .modal-content has no classes */
        if(!isset($modal_content_class) || $modal_content_class == null)
            throw new Exception('Expected .modal-content but it has no classes');

        /* Ensure .modal-content is attributed as such and is a div */
        assertEquals('div', $modal_content->getTagName());
        assertArrayContains('modal-content', explode(' ', $modal_content_class));
        
        /* Find all children of .modal-content */
        $modal_content_children = $modal_content->findElements(WebDriverBy::cssSelector('*'));

        /* Throw an exception if .modal-content has no children */
        if(!isset($modal_content_children) || count($modal_content_children) == 0)
            throw new Exception('.modal-content has no children');

        /* Find .modal-content's child .modal-header and throw error if not found */
        $modal_header = $modal_content->findElements(WebDriverBy::cssSelector('.modal-header'));
        if(!isset($modal_header) || count($modal_header) == 0) 
            throw new Exception('.modal-header not found');

        $modal_header = $modal_header[0];

        /* Find .modal-content's child .modal-body and throw error if not found */
        $modal_body = $modal_content->findElements(WebDriverBy::cssSelector('.modal-body'));
        if(!isset($modal_body) || count($modal_body) == 0) 
            throw new Exception('.modal-body not found');

        $modal_body = $modal_body[0];

        /* Find .modal-content's child .modal-footer and throw error if not found */
        $modal_footer = $modal_content->findElements(WebDriverBy::cssSelector('.modal-footer'));
        if(!isset($modal_footer) || count($modal_footer) == 0) 
            throw new Exception('.modal-footer not found');

        $modal_footer = $modal_footer[0];

        /* Ensure the .modal-header, .modal-body and .modal-footer are all divs */
        assertEquals('div', $modal_header->getTagName(), '.modal-header is not a div');
        assertEquals('div', $modal_body->getTagName(), '.modal-body is not a div');
        assertEquals('div', $modal_footer->getTagName(), '.modal-footer is not a div');

        /* Ensure the .modal-header, .modal-body and .modal-footer classes are all attributed correctly */
        assertArrayContains('modal-header', explode(' ', $modal_header->getAttribute('class')));
        assertArrayContains('modal-body', explode(' ', $modal_body->getAttribute('class')));
        assertArrayContains('modal-footer', explode(' ', $modal_footer->getAttribute('class')));

        /* Ensure .modal-header exists */
        $modal_title = $modal_header->findElements(WebDriverBy::cssSelector('.modal-title'));
        if(!isset($modal_title) || count($modal_title) == 0) 
            throw new Exception('.modal-title not found');

        $modal_title = $modal_title[0];

        /* Ensure the .modal-title is an h5 */
        assertEquals('h5', $modal_title->getTagName(), '.modal-title is not a h5');

        /* Ensure the .modal-title is attributed as such */
        assertArrayContains('modal-title', explode(' ', $modal_title->getAttribute('class')));

        /* Ensure the modal close button exists */
        $modal_close_button = $modal_header->findElements(WebDriverBy::cssSelector('.close'));
        if(!isset($modal_close_button) || count($modal_close_button) == 0) 
            throw new Exception('modal close button doesn\'t exist');

        $modal_close_button = $modal_close_button[0];

        /* Ensure the modal close button is a button */
        assertEquals('button', $modal_close_button->getTagName(), 'modal close button is not a button');

        /* Ensure the modal close button is attributed as .close */
        assertArrayContains('close', explode(' ', $modal_close_button->getAttribute('class')));

        /** TODO: Check that modal closes when you click X */

        /* Ensure the modal close button data-dismiss attribute is "modal" */
        assertEquals('modal', $modal_close_button->getAttribute('data-dismiss'));

        /* Ensure the .modal-body children exist */
        $login_forms = $modal_body->findElements(WebDriverBy::cssSelector('form'));
        if(!isset($login_forms) || count($login_forms) == 0) 
            throw new Exception('The .modal-body login form children do not exist');

        $login_form_1 = $login_forms[0];
        
        $login_form_children = $login_form_1->findElements(WebDriverBy::cssSelector('div'));
        if(!isset($login_form_children) || count($login_form_children) != 2) 
            throw new Exception('The login form 1 does not have exactly 2 children');

        $username_form_group = $login_form_children[0];

        assertEquals('div', $username_form_group->getTagName(), 'username .form-group is not a div');
        assertArrayContains('form-group', explode(' ', $username_form_group->getAttribute('class')), 'username .form-group does not contain .form-group class');

        $username_label = $username_form_group->findElements(WebDriverBy::cssSelector('label'));
        if(!isset($username_label) || count($username_label) != 1) 
            throw new Exception('The username label does not exist');

        $username_label = $username_label[0];

        assertEquals('label', $username_label->getTagName(), 'username label is not a label element');
        assertEquals('username', $username_label->getAttribute('for'), 'username label `for` attribute is wrong');

        $element_name = 'username <input>';

        $username_input = $username_form_group->findElements(WebDriverBy::cssSelector('input'));
        if(!isset($username_input) || (is_array($username_input) && count($username_input) != 1)) 
            throw new Exception("The $element_name does not exist");

        if(is_array($username_input))
            $username_input = $username_input[0];

        assertEquals('input', $username_input->getTagName(), "$element_name has wrong tag");
        $k='type'; $v='text'; assertEquals($v, $username_input->getAttribute($k), "$element_name `$k` attribute is wrong");
        $k='class'; $v='form-control'; assertEquals($v, $username_input->getAttribute($k), "$element_name `$k` attribute is wrong");
        $k='id'; $v='username'; assertEquals($v, $username_input->getAttribute($k), "$element_name `$k` attribute is wrong");
        $k='name'; $v='username'; assertEquals($v, $username_input->getAttribute($k), "$element_name `$k` attribute is wrong");

        $password_form_group = $login_form_children[1];

        assertEquals('div', $password_form_group->getTagName(), 'password .form-group is not a div');
        assertArrayContains('form-group', explode(' ', $password_form_group->getAttribute('class')), 'password .form-group does not contain .form-group class');

        $password_label = $password_form_group->findElements(WebDriverBy::cssSelector('label'));
        if(!isset($password_label) || count($password_label) != 1) 
            throw new Exception('The password label does not exist');

        $password_label = $password_label[0];

        assertEquals('label', $password_label->getTagName(), 'password label is not a label element');
        assertEquals('password', $password_label->getAttribute('for'), 'password label `for` attribute is wrong');

        $element_name = 'password <input>';

        $password_input = $password_form_group->findElements(WebDriverBy::cssSelector('input'));
        if(!isset($password_input) || (is_array($password_input) && count($password_input) != 1)) 
            throw new Exception("The $element_name does not exist");

        if(is_array($password_input))
            $password_input = $password_input[0];

        assertEquals('input', $password_input->getTagName(), "$element_name has wrong tag");
        $k='type'; $v='password'; assertEquals($v, $password_input->getAttribute($k), "$element_name `$k` attribute is wrong");
        $k='class'; $v='form-control'; assertEquals($v, $password_input->getAttribute($k), "$element_name `$k` attribute is wrong");
        $k='id'; $v='password'; assertEquals($v, $password_input->getAttribute($k), "$element_name `$k` attribute is wrong");
        $k='name'; $v='password'; assertEquals($v, $password_input->getAttribute($k), "$element_name `$k` attribute is wrong");

        $login_button = $modal_footer->findElements(WebDriverBy::cssSelector('.btn-primary'));

        if(!isset($login_button) || count($login_button) != 1) 
            throw new Exception('The Login button does not exist');
        
        if(is_array($login_button)) $login_button = $login_button[0];

        $username_input->click();

        $username_input->sendKeys('username');

        $password_input->click();

        $password_input->sendKeys('123456789');

        try {
            $login_button->click();

            // Wait for the alert to appear
            $selenium->wait(10, 500)->until(
                WebDriverExpectedCondition::alertIsPresent(),
                'Expected alert did not appear.'
            );
            $alert = $driver->switchTo()->alert();
            $alert->accept();
        } catch(Exception $e) {
            echo 'Alert generated as expected\n';
        }



        $username_input->clear();

        $username_input->click();

        $username_input->sendKeys('username');

        $passowrd_input->clear();

        $password_input->click();

        $password_input->sendKeys('12345678');

        $login_button->click();

        $selenium->wait(10, 500)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::id('login-link')),
            '#login-link did not become invisible after clicking #login-link'
        );

        /** TODO: Add .modal-footer checks */

        /** TODO: Add signup-form checks */

        echo "Reached end of selenium tests\n";
    }
?>
