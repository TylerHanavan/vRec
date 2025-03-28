<?php declare(strict_types=1);

$hookman->add_hook('xhr_login', array('url' => '/xhr/login', 'layer' => 'page_load_pre'));

function xhr_login(&$data) {
    if($data['_CMS']['path'] == '/xhr/login') {
        header('Content-Type: application/json');
        $response = array();
        $response['xhr_response_type'] = 'login';
        
        // Get POST data
        $username = $_POST['username'] ?? null;
        $password = $_POST['password'] ?? null;

        if(!$username || !$password || $username == null || $password == null || $username === '' || $password === '') {
            $response['xhr_response_status'] = 'error';
            $response['error'] = 'Missing username or password';
            echo json_encode($response);
            graceful_exit();
        }

        try {
            $account = new Account($data['_CMS']['database']);

            $userId = $account->verifyPassword($username, $password);
            
            if ($userId) {
                // Create session token
                $sessionToken = $account->createSession($userId);
                
                if ($sessionToken) {
                    // Set session cookie - expires in 24 hours
                    setcookie('session_token', $sessionToken, time() + 86400, '/', '', false, true);
                    
                    $response['xhr_response_status'] = 'success';
                    $response['message'] = 'Login successful';
                    $response['session_token'] = $sessionToken;
                } else {
                    $response['xhr_response_status'] = 'error';
                    $response['error'] = 'Failed to create session';
                }
            } else {
                $response['xhr_response_status'] = 'error';
                $response['error'] = 'Invalid username or password';
            }
        } catch (Exception $e) {
            $response['xhr_response_status'] = 'error';
            $response['error'] = 'Server error occurred';
        }

        echo json_encode($response);
        graceful_exit();
    }
}
