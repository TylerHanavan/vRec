<?php

    function test_hooks_1($properties) {

        require($properties['INSTALL_LOCATION'] . "/lib/hook/Hook.php");
        
        $hook = new Hook('test_hook_1', []);

        assertEquals($hook->get_function(), 'test_hook_1', 'function getter not right');

        assertTrue($hook->get_conditions() !== null, 'get_conditions is null');
        assertTrue(is_array($hook->get_conditions()), 'get_conditions is not an array');
        assertTrue(count($hook->get_conditions()) === 0, 'get_conditions is not empty');

        assertTrue($hook->can_call_hook([]) === true, 'can_call_hook was false for empty set of calling conditions');
        assertTrue($hook->can_call_hook(['logged_in' => true]) === true, 'can_call_hook was false for empty set of calling conditions');

    }

    function test_hooks_2($properties) {
        
        $hook = new Hook('test_hook_2', ['logged_in' => true]);

        assertTrue($hook->can_call_hook([]) === false, 'can_call_hook was true for empty set of calling conditions, when hook conditions needed logged_in => true');
        assertTrue($hook->can_call_hook(['logged_in' => false]) === false, 'can_call_hook was true for logged_in => false calling conditions, when hook conditions needed logged_in => true');
        assertTrue($hook->can_call_hook(['logged_in' => true]) === true, 'can_call_hook was false for logged_in => true calling conditions, when hook conditions needed logged_in => true');

    }
    
    function test_hooks_3($properties) {
        
        $hook = new Hook('test_hook_3', ['logged_in' => true, 'uri' => '/seven']);

        assertTrue($hook->can_call_hook([]) === false, 'can_call_hook was true for empty set of calling conditions, when hook conditions needed logged_in => true, uri => /seven');
        assertTrue($hook->can_call_hook(['logged_in' => false]) === false, 'can_call_hook was true for logged_in => false calling conditions, when hook conditions needed logged_in => true, uri => /seven');
        assertTrue($hook->can_call_hook(['logged_in' => true]) === false, 'can_call_hook was true for logged_in => true calling conditions, when hook conditions needed logged_in => true, uri => /seven');
        assertTrue($hook->can_call_hook(['logged_in' => true, 'temp' => 'four']) === false, 'can_call_hook was true for logged_in => true, temp => four calling conditions, when hook conditions needed logged_in => true, uri => /seven');
        assertTrue($hook->can_call_hook(['logged_in' => true, 'temp' => 'four', 'uri' => '/seven']) === true, 'can_call_hook was false for logged_in => true, temp => four, uri => /seven calling conditions, when hook conditions needed logged_in => true, uri => /seven');
        assertTrue($hook->can_call_hook(['logged_in' => true, 'uri' => '/seven']) === true, 'can_call_hook was false for logged_in => true, uri => /seven calling conditions, when hook conditions needed logged_in => true, uri => /seven');
        assertTrue($hook->can_call_hook(['logged_in' => false, 'uri' => '/seven']) === false, 'can_call_hook was true for logged_in => false, uri => /seven calling conditions, when hook conditions needed logged_in => true, uri => /seven');
        assertTrue($hook->can_call_hook(['logged_in' => true, 'uri' => '/six']) === false, 'can_call_hook was true for logged_in => true, uri => /six calling conditions, when hook conditions needed logged_in => true, uri => /seven');

    }
?>