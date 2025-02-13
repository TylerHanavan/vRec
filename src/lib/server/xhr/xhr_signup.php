<?php declare(strict_types=1);

$hookman->add_hook('xhr_signup', array('url' => '/xhr/signup', 'layer' => 'page_load_pre'));

function xhr_signup(&$data) {
    if($data['_CMS']['path'] == '/xhr/signup') {
        $response = array();
        $response['xhr_response_type'] = 'signup';
        
        // Get POST data
        $username = $_POST['username'] ?? null;
        $password = $_POST['password'] ?? null;
        $email = $_POST['email'] ?? null;

        if(get_config_value('ACCOUNTS.SIGNUPS.ENABLED') !== 'true') {
            $response['xhr_response_status'] = 'error';
            $response['error'] = 'Signup is disabled';
            echo json_encode($response);
            graceful_exit();
        }

        if(!$username || !$password || !$email) {
            $response['xhr_response_status'] = 'error';
            $response['error'] = 'Missing required fields';
            echo json_encode($response);
            graceful_exit();
        }

        // Validate input
        if (strlen($username) < 3 || strlen($username) > 50) {
            $response['xhr_response_status'] = 'error';
            $response['error'] = 'Username must be between 3 and 50 characters';
            echo json_encode($response);
            graceful_exit();
        }

        if (strlen($password) < 8) {
            $response['xhr_response_status'] = 'error';
            $response['error'] = 'Password must be at least 8 characters';
            echo json_encode($response);
            graceful_exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['xhr_response_status'] = 'error';
            $response['error'] = 'Invalid email format';
            echo json_encode($response);
            graceful_exit();
        }

        try {
            $account = new Account($data['_CMS']['database']);
            if ($account->createAccount($username, $password, $email)) {
                $response['xhr_response_status'] = 'success';
                $response['message'] = 'Account created successfully';
            } else {
                $response['xhr_response_status'] = 'error';
                $response['error'] = 'Failed to create account. Username or email may already exist.';
            }
        } catch (Exception $e) {
            $response['xhr_response_status'] = 'error';
            $response['error'] = 'Server error occurred';
        }

        echo json_encode($response);
        graceful_exit();
    }
}
