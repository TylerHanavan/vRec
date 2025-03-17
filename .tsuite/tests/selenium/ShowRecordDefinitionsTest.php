<?php

    use Facebook\WebDriver\WebDriverBy;
    use Facebook\WebDriver\WebDriverExpectedCondition;

    function test_selenium_1($properties) {

        $url = 'http://' . $properties['endpoint_url'];

        $selenium = $properties['selenium'];

        $selenium->get("$url/test");

        $selenium->wait(10, 500)->until(
            function () use ($selenium) {
                $elements = $selenium->findElements(WebDriverBy::cssSelector('div'));
        
                return count($elements) > 2;
            },
            'Error locating more than two div elements'
        );
        
        $selenium->wait(10, 500)->until(
            function () use ($selenium) {
                $elements = $selenium->findElements(WebDriverBy::id('login-link'));
        
                return count($elements) == 1 && $elements[0]->getText() == 'Login';
            },
            'Error locating #login-link anchor'
        );
        
        $selenium->wait(10, 500)->until(
            function () use ($selenium) {
                $elements = $selenium->findElements(WebDriverBy::className('modal'));
        
                return count($elements) != 0;
            },
            'Error location .modal'
        );
        
        $selenium->wait(10, 500)->until(
            function () use ($selenium) {
                $elements = $selenium->findElements(WebDriverBy::className('modal'));
        
                return count($elements) == 1;
            },
            'More than one .modal found'
        );

        $modal = $selenium->findElement(WebDriverBy::className('modal'));

        $login_anchor = $selenium->findElement(WebDriverBy::id('login-link'));

        $login_anchor->click();
        
        $selenium->wait(10, 500)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('.modal')),
            'Modal did not become visible after clicking #login-link'
        );

        $modal_children = $modal->findElements(WebDriverBy::cssSelector('*'));

        if(!isset($modal_children) || count($modal_children) == 0)
            throw new Exception('.modal has no children');

        $modal_dialog = $modal_children[0];

        if(!isset($modal_dialog->getAttribute('class')) || $modal_dialog->getAttribute('class') == null)
            throw new Exception('Expected .modal-dialog but it has no classes');

        assertEquals('div', $modal_dialog->getTagName());
        asssertArrayContains('.modal-dialog', explode(' ', $modal_dialog->getAttribute('class')));

        $modal_dialog_children = $modal_dialog->findElements(WebDriverBy::cssSelector('*'));

        if(!isset($modal_dialog_children) || count($modal_dialog_children) == 0)
            throw new Exception('.modal-dialog has no children');

        $modal_content = $modal_dialog_children[0];

        if(!isset($modal_content->getAttribute('class')) || $modal_content->getAttribute('class') == null)
            throw new Exception('Expected .modal-content but it has no classes');
        
        assertEquals('div', $modal_content->getTagName());

        $modal_content_children = $modal_content->findElements(WebDriverBy::cssSelector('*'));

        if(!isset($modal_content_children) || count($modal_content_children) == 0)
            throw new Exception('.modal-content has no children');
    }
?>
