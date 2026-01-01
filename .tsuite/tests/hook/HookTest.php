<?php

    function test_hooks_1($properties) {

        require_once($properties['INSTALL_LOCATION'] . "/lib/hook/Hook.php");
        
        $hook = new Hook('test_hook_1', []);

        assertEquals($hook->get_function(), 'test_hook_1', 'function getter not right');

        assertTrue($hook->get_conditions() !== null, 'get_conditions is null');
        assertTrue(is_array($hook->get_conditions()), 'get_conditions is not an array');
        assertTrue(isset($hook->get_conditions()), 'get_conditions is not set');
        assertTrue(count($hook->get_conditions()) === 0, 'get_conditions is not empty');

        assertTrue($hook->can_call_hook([]) === true, 'can_call_hook was false for empty set of calling conditions');
        assertTrue($hook->can_call_hook(['logged_in' => true]) === true, 'can_call_hook was false for empty set of calling conditions');

    }
?>