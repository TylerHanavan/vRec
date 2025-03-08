<?php

    $session_token = null;

    function test_login_1($properties) {
        $response = test_curl($properties['endpoint_url'] . '/xhr/login', array('username' => 'username'), true);
        assertEquals(200, $response['http_code'], 'http code mismatch');
        assertEquals('{"xhr_response_type":"login","xhr_response_status":"error","error":"Missing username or password"}', $response['response'], 'response mismatch');
    }

    function test_login_2($properties) {
        $response = test_curl($properties['endpoint_url'] . '/xhr/login', array('password' => '12345678'), true);
        assertEquals(200, $response['http_code'], 'http code mismatch');
        assertEquals('{"xhr_response_type":"login","xhr_response_status":"error","error":"Missing username or passwor"}', $response['response'], 'response mismatch');
    }

    function test_login_3($properties) {
        $response = test_curl($properties['endpoint_url'] . '/xhr/login', array('username' => 'username', 'password' => '12346567789'), true);
        assertEquals(200, $response['http_code'], 'http code mismatch');
        assertEquals('{"xhr_response_type":"login","xhr_response_status":"error","error":"Invalid username or password"}', $response['response'], 'response mismatch');
    }

    function test_login_4($properties) {
        $response = test_curl($properties['endpoint_url'] . '/xhr/login', array('username' => 'username1', 'password' => '12345678'), true);
        assertEquals(200, $response['http_code'], 'http code mismatch');
        assertEquals('{"xhr_response_type":"login","xhr_response_status":"error","error":"Invalid username or password"}', $response['response'], 'response mismatch');
    }

    function test_login_success($properties) {
        $response = test_curl($properties['endpoint_url'] . '/xhr/login', array('username' => 'username', 'password' => '12345678'), true);
        assertEquals(200, $response['http_code'], 'http code mismatch');
        $response_array = json_decode($response['response'], true);
        assertTrue(isset($response_array['session_token']), 'session token not set');

        global $session_token;
        $session_token = $response_array['session_token'];

        assertEquals($response_array['xhr_response_status'], 'success', 'response status mismatch');
        assertEquals($response_array['xhr_response_type'], 'login', 'response type mismatch');
    }

?>