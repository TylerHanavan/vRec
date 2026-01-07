<?php

    use Facebook\WebDriver\WebDriverBy;
    use Facebook\WebDriver\WebDriverExpectedCondition;

    function test_show_records($properties) {

        var_dump($properties['selenium']->manage()->getCookies());

    }

?>