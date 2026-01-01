<?php

    function test_enums_1($properties) {

        require_once($properties['INSTALL_LOCATION'] . "/lib/hook/Hook.php");
        
        $hook = new Hook('test_hook_1', []);

        assertEquals($hook->get_function(), 'test_hook_1', 'function getter not right');

    }
?>