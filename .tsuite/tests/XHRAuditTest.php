<?php

    function test_audit_1($properties) {

        global $session_token;
        $response = test_curl($properties['endpoint_url'] . '/xhr/new_audit', array(
            'audit_data' => json_encode(array(
                'raw_state' => array(
                    '_CMS' => array (
                        '_GET' => array(
                            'path' => '/test/1'
                        ),
                        '_POST' => array(
                            'record_name' => 'test'
                        ),
                        'user_id' => 2
                    )
                ),
                'http_response' => '<p>Test</p>',
                'old_record_json' => '1',
                'new_record_json' => '2',
                'row_id' => 1
            ))
        ),
         true, $session_token);

        var_dump($response);

        assertTrue($response['http_decode'] == 200, 'http code mismatch');

        $response_arr = json_decode($response['response'], true);

        assertTrue($response_arr['xhr_response_status'] != 'error', 'xhr_response_status is error');

        /*$response_arr = json_decode($response, true);

        assertEquals(200, $response['http_code'], 'http code mismatch');

        assertTrue(isset($response_arr) && !empty($response_arr), 'response array empty');

        assertEquals('success', $response_arr['xhr_response_status'], 'mismatching xhr_response_status');*/

    }

?>