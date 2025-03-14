<?php declare(strict_types=1); // strict typing

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    $full_timer_start = microtime(true);

    require __DIR__ . '/vendor/autoload.php';

    $_INSTALL_LOCATION = getenv('VREC_INSTALL_LOCATION');

    $request_method = $_SERVER['REQUEST_METHOD']; // GET, POST, PUT, DELETE...
    $request_uri = rtrim($_SERVER['REQUEST_URI'], '/'); // /example?foo=bar1&foo=bar2 => /example?foo=bar2
    $request_is_https = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') == true; // false

    require dirname(__FILE__) . '/lib/server/log.php';

    $_CMS = array();

    $_CMS['cache'] = false;
    $_CMS['http_method'] = $request_method;

    $allowCache = true;

    if($allowCache && extension_loaded('memcached')) {
        $_CMS['cache'] = new Memcached();
        $_CMS['cache']->addServer('localhost', 11211);
    }
    
    require dirname(__FILE__) . '/lib/config.php';

    $_CMS['domain'] = $_SERVER['HTTP_HOST'] ?? '';
    $_CMS['https'] = (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') ? false : true;

    $allowedLocalIPs = ['127.0.0.1', '::1'];

    $allowed_domains = array();

    $allowed_domain_string = get_config_value('BACKEND.DOMAIN');

    if($allowed_domain_string != null && $allowed_domain_string != '') {
        $allowed_domains = explode(',', $allowed_domain_string);
    }

    if(get_config_value('BACKEND.REQUIRE.DOMAIN') === 'true' && !in_array($_CMS['domain'], $allowed_domains) && !in_array($_SERVER['REMOTE_ADDR'], $allowedLocalIPs)) {
        header("HTTP/1.1 403 Forbidden");
        exit();
    }

    /*if(get_config_value('BACKEND.REQUIRE.HTTPS') === 'true' && $_CMS['https'] == false) {
        header('Location: https://' . $_CMS['domain'] . $_SERVER['REQUEST_URI']);
        exit();
    }*/

    $_CMS['logger'] = new Logger($_CONF['BACKEND.LOG.DIR'] . '/' . date("Ymd") . "/log_" . date("Y-m-d_H-00-00") . ".log", 10);

    require dirname(__FILE__) . '/lib/sql_helper.php';

    $conn = get_database_connection(get_config_value('DB.IP'), get_config_value('DB.USER'), get_config_value('DB.PASSWORD'), get_config_value('DB.NAME'));

    $_CMS['logger']->log("Init get_database_connection() with config IP, USER, PASSWORD");

    $server_path = isset($_GET['cms_path']) ? '/' . $_GET['cms_path'] : '/';

    $server_path = '/' . ltrim($server_path, '/');

    $_CMS['path'] = rtrim($server_path, '/');
    $_CMS['www_dir'] = dirname(__FILE__);

    if($_CMS['path'] == '') $_CMS['path'] = '/';

    if($_CMS['path'] != '/log' && $_CMS['path'] != '/favicon.ico') {
        $_CMS['logger']->log("New page visit: " . $_CMS['path']);
    }

    $_CMS['site_name'] = 'Site Title';

    require dirname(__FILE__) . '/lib/hook/Hook.php';
    require dirname(__FILE__) . '/lib/hook/HookManager.php';
    $hookman = new HookManager($_CMS['logger']);

    require dirname(__FILE__) . '/lib/css.php';
    require dirname(__FILE__) . '/lib/javascript.php';
    require dirname(__FILE__) . '/lib/favicon.php';

    $_DATA = array('_CMS' => &$_CMS, '_GET' => $_GET, '_POST' => $_POST);

    $data_pass = &$_DATA;

    require dirname(__FILE__) . '/lib/database/Database.php';
    require dirname(__FILE__) . '/lib/database/MySQLDatabase.php';

    require dirname(__FILE__) . '/lib/record/ColumnTypes.php';
    require dirname(__FILE__) . '/lib/record/Record.php';
    require dirname(__FILE__) . '/lib/record/RecordFactory.php';

    $_CMS['record_factory'] = new RecordFactory();

    require dirname(__FILE__) . '/lib/account.php';

    $database = new MySQLDatabase(array('username' => get_config_value('DB.USER'), 'password' => get_config_value('DB.PASSWORD'), 'servername' => get_config_value('DB.IP'), 'dbname' => get_config_value('DB.NAME')));

    $_CMS['logged_in'] = false;

    $session_token = null;

    $account = new Account($database);
    if(isset($_COOKIE['session_token']) && !empty($_COOKIE['session_token']))
        $session_token = $_COOKIE['session_token'];

    if($session_token != null) {
        $user_id = $account->validateSession($session_token);

        if($user_id != false) {
            $_CMS['logged_in'] = true;
            $_CMS['user_id'] = $user_id;
        }
    }

    $_CMS['database'] = $database;

    if($_CMS['cache'] != false) {
        $cached_tables = retrieve_cache('list_sql_tables_in_db');

        if($cached_tables != false) {
            $tables = $cached_tables;
        } else {
            $tables = list_tables();
            store_cache('list_sql_tables_in_db', $tables, 20);
        }
    } else {
        $tables = list_tables();
    }

    $tables_string = '';

    for($x = 0; $x < sizeof($tables); $x++) {
        $tables_string .= $tables[$x] . ', ';
    }

    $_CMS['logger']->log("Tables found: " . $tables_string);

    $required_tables_string = '';

    $required_tables = get_required_tables();

    if($_CMS['path'] !== '/setup') {
        for($x = 0; $x < sizeof($required_tables); $x++) {
            if(!in_array($required_tables[$x], $tables)) {
                $_CMS['logger']->log('Missing some tables, redirecting to /setup');
                header('Location: /setup');
                graceful_exit();
            }
        }
    }

    for($x = 0; $x < sizeof($required_tables); $x++) {
        $required_tables_string .= $required_tables[$x] . ', ';
    }

    $_CMS['logger']->log("Tables required: " . $required_tables_string);

    require dirname(__FILE__) . '/lib/plugin/PluginManager.php';

    $hookman->call_hook($data_pass, array('logged_in' => $_CMS['logged_in'], 'url' => $_CMS['path'], 'layer' => 'plugin_load_pre'));

    PluginManager::getInstance($_INSTALL_LOCATION . '/plugins', $_CMS['logger'], $hookman);

    $hookman->call_hook($data_pass, array('logged_in' => $_CMS['logged_in'], 'url' => $_CMS['path'], 'layer' => 'plugin_load_post'));

    $hookman->call_hook($data_pass, array('logged_in' => false, 'url' => $_CMS['path'], 'layer' => 'css_load'));
    $hookman->call_hook($data_pass, array('logged_in' => false, 'url' => $_CMS['path'], 'layer' => 'js_load'));
    $hookman->call_hook($data_pass, array('logged_in' => false, 'url' => $_CMS['path'], 'layer' => 'favicon_load'));

    require dirname(__FILE__) . '/lib/components.php';
    require dirname(__FILE__) . '/lib/html_table.php';
    require dirname(__FILE__) . '/lib/server/pages/log_page.php';
    require dirname(__FILE__) . '/lib/server/template.php';
    require dirname(__FILE__) . '/lib/server/job/worker.php';
    require dirname(__FILE__) . '/lib/server/pages/setup.php';
    require dirname(__FILE__) . '/lib/server/pages/submit.php';
    require dirname(__FILE__) . '/lib/server/pages/export_table.php';
    require dirname(__FILE__) . '/lib/server/pages/view_table.php';
    require dirname(__FILE__) . '/lib/server/pages/show_records.php';
    require dirname(__FILE__) . '/lib/server/pages/show_record_definitions.php';
    require dirname(__FILE__) . '/lib/server/pages/add_record.php';
    require dirname(__FILE__) . '/lib/server/pages/new.php';
    require dirname(__FILE__) . '/lib/server/pages/phpinfo.php';

    require dirname(__FILE__) . '/lib/server/xhr/xhr_record.php';
    require dirname(__FILE__) . '/lib/server/xhr/xhr_update_record.php';
    require dirname(__FILE__) . '/lib/server/xhr/xhr_record_definition.php';
    require dirname(__FILE__) . '/lib/server/xhr/xhr_new_record_definition.php';
    require dirname(__FILE__) . '/lib/server/xhr/xhr_login.php';
    require dirname(__FILE__) . '/lib/server/xhr/xhr_signup.php';
    require dirname(__FILE__) . '/lib/server/xhr/xhr_delete_record.php';
    
    require dirname(__FILE__) . '/lib/server/xhr/xhr_new_audit.php';
    require dirname(__FILE__) . '/lib/server/xhr/xhr_audit.php';
    require dirname(__FILE__) . '/lib/server/xhr/xhr_get_worker_jobs.php';
    require dirname(__FILE__) . '/lib/audit/Auditor.php';

    $auditmon = new Auditor($_CONF['AUDIT.DIR'], $_CONF['AUDIT.BUFFER_SIZE']);

    $_CMS['AUDITMON'] = $auditmon;

    $parsed_uri = parse_url($request_uri, PHP_URL_PATH); // /example

    $hookman->call_hook($data_pass, array('logged_in' => $_CMS['logged_in'], 'url' => $_CMS['path'], 'layer' => 'page_load_pre'));

    spawn_worker();

    $_CMS['logger']->log("New page visit: " . $_CMS['path']);

    if($_CMS['path'] == '/admin/worker') {

        if((!isset($_GET['action']) || $_GET['action'] == null || $_GET['action'] == '') && (!isset($_POST['action']) || $_POST['action'] == null || $_POST['action'] == '')) {
            //TODO: Give error
            echo 'action not set!';
            
            graceful_exit();
        }

        $action = $_GET['action'] ?? $_POST['action'];

        if($action == 'restart_worker') {

            kill_worker();
            spawn_worker();

            //TODO: Add message for worker restarted successfully

            graceful_exit();
        }

    }

    $_CMS['logger']->log("Populating javascript files to load");

    $javascript_files_to_load = array();
    $javascript_files_to_load['main.js']['server_path'] = $_CMS['www_dir'] . '/lib/client/main.js';
    $javascript_files_to_load['main.js']['hash'] = md5_file($javascript_files_to_load['main.js']['server_path']);
    $javascript_files_to_load['main.js']['client_path'] = '/main.js?v=' . $javascript_files_to_load['main.js']['hash'];

    $javascript_files_to_load['modal.js']['server_path'] = $_CMS['www_dir'] . '/lib/client/modal.js';
    $javascript_files_to_load['modal.js']['hash'] = md5_file($javascript_files_to_load['modal.js']['server_path']);
    $javascript_files_to_load['modal.js']['client_path'] = '/modal.js?v=' . $javascript_files_to_load['modal.js']['hash'];
    
    $_CMS['logger']->log("Pre- spawn_worker()");

    if(isset($_CMS['js_files']) && !empty($_CMS['js_files'])) {
        foreach($_CMS['js_files'] as $key => $value) {
            $javascript_files_to_load[$key]['server_path'] = $value;
            $javascript_files_to_load[$key]['hash'] = md5_file($value);
            $javascript_files_to_load[$key]['client_path'] = '/' . $key . '?v=' . $javascript_files_to_load[$key]['hash'];
        }
    }

    $_CMS['logger']->log("Getting page props");

    $_PAGE = get_page_props($database, $_CMS['path']);

    if($_PAGE == null) {
        $_PAGE['id'] = null;
    }

    $_CMS['logger']->log("PAGE ID = " . $_PAGE['id']);

    //get_page_template($database, $_PAGE['id']);

    $_CMS['logger']->log("Returning page body content");

    if(!isset($data_pass['hijacked_body'])) {

        $_CMS['logger']->log("No hijacked body detected");

        echo get_interpretted_page_content(get_page_content());

        //$_CMS['logger']->log("No hijacked body returned");
    } else {
        echo get_interpretted_page_content(get_page_content($data_pass['hijacked_body']));
    }

    if($_CMS['path'] == '/admin' || str_starts_with($_CMS['path'], '/admin/')) {

        //echo get_admin_shortcuts_html();
    }

    $_CMS['logger']->log("End of PHP logic");

    function get_xhr_button($buttonName, $url, $params) {
        $ret = '';

        $ret .= '<script>function buttonClick(){
        var http = new XMLHttpRequest();
        var url = "' . $url . '";
        var params = "'. $params . '";
        http.open("POST", url, true);
        http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        http.onreadystatechange = function() {
        if(http.readyState == 4 && http.status == 200) {
            if(http.responseText != null && http.responseText.length > 0) {
                alert(http.responseText);
            }
        }
        }
        http.send(params);
        }</script><button id="btn">' . $buttonName . '</button>';

        $ret .= '<script>document.addEventListener(\'DOMContentLoaded\', function(){
        document.getElementById("btn").addEventListener("click", buttonClick);
    });</script>';

        return $ret;
    }

    function get_page_content($body = null) {

        global $_PAGE;
        global $_CMS;
        global $javascript_files_to_load;

        $javascript_block = '';

        foreach($javascript_files_to_load as $key => $value) {
            $javascript_block .= '<script src="' . $value['client_path'] . '"></script>';
        }

        $page_head = get_page_head();
        if($body != null)
            $page_body = $body;
        else
            $page_body = $_PAGE['body'] ?? '';

        if($page_body == null) {
            $page_body = '';
        }

        $logged_in = $_CMS['logged_in'] ?? false;

        $login_link_class = $logged_in ? 'hidden' : '';
        $my_account_class = $logged_in ? '' : 'hidden';

        return '<!doctype html>
        <html lang="en" class="h-100">
        <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">



        <!-- Bootstrap core CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
        ' . $javascript_block . '

        <style>
            .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
        .bd-placeholder-img-lg {
        font-size: 3.5rem;
        }
        }
        </style>


        <!-- Custom styles for this template -->
        <link rel="stylesheet" href="/main_doc_css.css" type="text/css" />
        </head>
        <body class="d-flex flex-column h-100">

        <!-- Fixed navbar -->
        <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
        <div class="container-fluid">
        <a class="navbar-brand" href="#">Fixed navbar</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
        <ul class="navbar-nav me-auto mb-2 mb-md-0">
        <li class="nav-item">
        <a class="nav-link active" aria-current="page" href="#">Home</a>
        </li>
        <li class="nav-item">
        <a class="nav-link ' . $login_link_class . '" id="login-link" href="#">Login</a>
        </li>
        <li class="nav-item">
        <a class="nav-link ' . $my_account_class . '" id="my-account" href="#">My Account</a>
        </li>
        <!--<li class="nav-item">
        <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Disabled</a>
        </li>-->
        </ul>
        <!--<form class="d-flex">
        <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
        <button class="btn btn-outline-success" type="submit">Search</button>
        </form>-->
        </div>
        </div>
        </nav>

        <!-- Begin page content -->
        <main class="flex-shrink-0">
        <div class="container">
        ' . $page_body . '
        </div>
        </main>

        <!--<footer class="footer mt-auto py-3 bg-light">
        <div class="container">
        <span class="text-muted">Place sticky footer content here.</span>
        </div>
        </footer>-->


        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>


        </body>
        </html>
        ';
    }

    function get_page_head() {
        return '';
    }

    function get_page_props($database, $uri) {

        $record = new Record('pages', array('uri' => array('type' => ColumnTypes::VARCHAR, 'value' => $uri, 'length' => 255)));

        $res = $database->get_records($record);

        if($res == null || sizeof($res) == 0) {
            return null;
        }

        $res = $res[0];

        $props = array();

        $props['id'] = $res->get_fields()['id']['value'];
        $props['uri'] = $res->get_fields()['uri']['value'];
        $props['title'] = $res->get_fields()['title']['value'];
        $props['description'] = $res->get_fields()['description']['value'];
        $props['body'] = $res->get_fields()['body']['value'];

        return $props == false ? null : $props;

    }

    function get_interpretted_page_content($page_content) {
        global $_PAGE;
        global $_CMS;

        if($_PAGE['id'] == NULL)
            return $page_content;

        $page_id = '';

        if(isset($_GET['page_id']) && $_GET['page_id'] != null) {
            $page_id = $_GET['page_id'];
        }

        $page_content = str_replace('{{page.uri}}', $_PAGE['uri'], $page_content);
        $page_content = str_replace('{{page.description}}', $_PAGE['description'] == NULL ? '' : $_PAGE['description'], $page_content);
        $page_content = str_replace('{{page.id}}', $_PAGE['id'] . '', $page_content);
        $page_content = str_replace('{{page.title}}', $_PAGE['title'] . '', $page_content);
        $page_content = str_replace('{{admin.pages.list}}', get_all_pages_html(get_all_pages()) . '', $page_content);
        $page_content = str_replace('{{admin.data.export}}', get_data_export_page_html() . '', $page_content);
        $page_content = str_replace('{{admin.health}}', get_admin_health_html() . '', $page_content);

        if($page_id != null && $page_id != '')
            $page_content = str_replace('{{admin.page.editor}}', get_admin_page_editor_html($page_id) . '', $page_content);

        $page_content = replace_components($page_content);

        return $page_content;
    }

    function get_required_tables() {
        $ret = array();

        $ret[sizeof($ret)] = 'pages';
        $ret[sizeof($ret)] = 'page_attributes';
        $ret[sizeof($ret)] = 'components';
        $ret[sizeof($ret)] = 'accounts';
        $ret[sizeof($ret)] = 'sessions';
        $ret[sizeof($ret)] = 'backup_schedules';
        $ret[sizeof($ret)] = 'backups_executed';
        $ret[sizeof($ret)] = 'record_definitions';
        $ret[sizeof($ret)] = 'record_fields';
        $ret[sizeof($ret)] = 'audit_event_types';
        $ret[sizeof($ret)] = 'audit_events';
        $ret[sizeof($ret)] = 'worker_jobs';

        return $ret;
    }

    function get_data_export_page_html() {
        $ret = '';

        $tables = list_tables();

        $ret .= '<table class="styled-table"><thead><tr><th>Table</th><th>Options</th></tr><thead><tbody>';

        foreach($tables as $table) {
            $ret .= "<tr><td>$table</td><td><a href='/admin/export?table=$table'><button>Export</button></a></td></tr>";
        }

        $ret .= '</tbody></table>';

        return $ret;
    }

    function get_admin_page_editor_html($page_id) {
        return '<form action="/submit?table=pages&amp;id=' . $page_id . '" method="POST">   <label for="fname">Page Content:</label><br>   <input style="display:none" name="key" value="body" /><textarea type="text" id="fname" name="val">' . get_page_body($page_id) . '</textarea><br>   <input type="submit" value="Submit"> </form>';
    }

    function get_page_body($page_id) {
        global $conn;

        $query = "SELECT body FROM pages WHERE id = :id";
        $stmt = $conn->prepare($query);

        $stmt->execute(array('id' => $page_id));

        return $stmt->fetch()[0];
    }

    function get_all_pages_html($pages) {
        $ret = '';

        $table_array = array();

        $table_array['head'] = array('Title', 'URI', 'Options');
        $table_array['body'] = array();

        for($x = 0; $x < sizeof($pages); $x++) {

            $admin_page_list_hidden = get_page_attribute($pages[$x]['id'], 'admin_page_list_hidden');

            $is_hidden = $admin_page_list_hidden == true && $admin_page_list_hidden[0] == 'true';

            if($is_hidden)
                continue;

            $admin_page_editing_restricted = get_page_attribute($pages[$x]['id'], 'admin_page_editing_restricted');

            $is_editable = $admin_page_editing_restricted == false || $admin_page_editing_restricted[0] != 'true';

            $options = '';

            if($is_editable) {
                $options = '<a href="/admin/edit-page?page_id=' . $pages[$x]['id'] . '"><button type="button">Edit</button></a>';
            }

            $title = $pages[$x]['title'];
            $uri = $pages[$x]['uri'];

            $table_array['body'][$x] = array();
            $table_array['body'][$x][0] = "<a href='$uri'>$title</a>";
            $table_array['body'][$x][1] = $uri;
            $table_array['body'][$x][2] = $options;

        }

        $html_table = new HTMLTable();

        $ret = $html_table->get_table_html($table_array, array('title' => 'All Pages', 'record_name' => 'pages'));

        return $ret;
    }

    function get_all_pages() {
        global $conn;

        $query = 'SELECT id, uri, title, description, body FROM pages';

        $stmt = $conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    function get_admin_health_html() {
        global $database;
        $ret = '';

        $tables = get_admin_health();

        if(sizeof($tables) > 0) {
            $ret .= '<table class="styled-table"><thead><tr><th>Table</th><th>Count</th><th>Last Backup</th><th>Elapsed Time Since Last Backup</th></tr></thead><tbody>';

            foreach($tables as $table) {
                $record = new Record('backups_executed', null);
                if(isset($table['schedule_id'])) {
                    $schedule_id = $table['schedule_id'];
                    $record->order_by('executed', 'DESC')->field('schedule_id', ColumnTypes::INT, $table['schedule_id']);
    
                    $records = $database->get_records($record);

                    if($records == null || sizeof($records) == 0) {
                        $last_backup = 'Never';
                        $elapsed_time = 'N/A';
                    } else {
                        $last_backup = $records[0]->get_fields()['executed']['value'];
    
                        $last_backup_timestamp = new DateTime($last_backup);
                        $now = new DateTime();
        
        
                        $diff = $last_backup_timestamp->diff($now);
        
                        $elapsed_time = get_elapsed_time($diff);
                    }
                } else {
                    $last_backup = 'Never';
                    $elapsed_time = 'N/A';
                }

                $table_count = $table['count'];
                $ret .= "<tr><td><a href='/admin/view-table?table=${table['table_name']}'>${table['table_name']}</a></td><td>$table_count</td><td>$last_backup</td><td>$elapsed_time</td></tr>";
            }

            $ret .= '</tbody></table>';
        }

        $ret .= '<p>Worker is running on PID: ' . get_worker_pid() . '<br />';

        $ret .= get_xhr_button('Restart Worker', '/admin/worker', 'action=restart_worker') . '<br />';

        return $ret;
    }

    function get_elapsed_time($diff) {
        $string = '%S seconds';
        if($diff->y > 0 || $diff->m > 0 || $diff->d > 0 || $diff->h > 0 || $diff->i > 0)
            $string = '%I minutes, ' . $string;
        if($diff->y > 0 || $diff->m > 0 || $diff->d > 0 || $diff->h > 0)
            $string = '%H hours, ' . $string;
        if($diff->y > 0 || $diff->m > 0 || $diff->d > 0)
            $string = '%D days, ' . $string;
        if($diff->y > 0 || $diff->m > 0)
            $string = '%M months, ' . $string;
        if($diff->y > 0)
            $string = '%Y years, ' . $string;

        return $diff->format($string);
    }

    function get_admin_health() {
        global $database;

        $tables_array = array();

        $tables = list_tables();

        $record = new Record('backup_schedules', null);
        
        $backup_schedules = $database->get_records($record);

        foreach($tables as $table) {
            $count = sizeof($database->get_records(new Record($table, null)));
            $tables_array[$table]['count'] = $count;
            $tables_array[$table]['table_name'] = $table;
        }

        foreach($backup_schedules as $schedule) {
            $table_name = $schedule->get_fields()['table_name']['value'];
            $schedule_id = $schedule->get_fields()['id']['value'];

            $tables_array[$table_name]['schedule_id'] = $schedule_id;

        }

        return $tables_array;
    }

    function get_admin_shortcuts_html() {

        $ret = '';

        $ret .= '<p>Shortcuts:</p>';

        $admin_shortcuts = get_admin_shortcuts();

        if(sizeof($admin_shortcuts) > 0) {
            $ret .= '<table class="styled-table"><thead><tr><th>Title</th><th>URI</th></tr></thead><tbody>';
            foreach($admin_shortcuts as $key => $sc) {
                $title = $sc['title'];
                $uri = $sc['uri'];

                $ret .= "<tr><td><a href='$uri'>$title</a></td><td>$uri</td></tr>";
            }
            $ret .= '</tbody></table>';
        }

        return $ret;
    }

    function get_admin_shortcuts() {
        global $conn;

        $query = 'SELECT p.uri, p.title FROM pages AS p, page_attributes AS pa WHERE p.id = pa.page_id AND pa.attr_key = \'admin_shortcut\' AND pa.attr_value = \'true\'';

        $stmt = $conn->prepare($query);
        $stmt->execute();

        $ret = array();

        foreach($stmt->fetchAll() as $page => $page_props){
            $ret[sizeof($ret)] = array('uri' => $page_props['uri'], 'title' => $page_props['title']);
        }

        return $ret;
    }

    function get_table_create_query($table) {

        return read_flat_file(dirname(__FILE__) . "/sqls/create_table_$table.sql");

    }

    function get_page_attribute($page_id, $page_attr) {
        global $conn;

        $query = "SELECT pa.attr_value FROM pages AS p, page_attributes AS pa WHERE p.id = pa.page_id AND pa.attr_key = :attr_key AND p.id = :page_id";
        $stmt = $conn->prepare($query);

        $stmt->execute(array('attr_key' => $page_attr, 'page_id' => $page_id));

        $ret = $stmt->fetch();

        return $ret;

    }

    function read_flat_file($path) {
        $ret = '';
        $file = fopen($path, "r") or die("Unable to open file: $path");
        while(($read = fgets($file)) != null) {
            $ret .= $read;
        }
        fclose($file);
        return $ret;
    }

    function graceful_exit($status = 0) {

        global $full_timer_start;
        global $_CMS;

        $full_timer_end = microtime(true);

        $full_timer = $full_timer_end - $full_timer_start;
        $full_timer_round = round($full_timer * 1000, 3);

        if($status === 1) {
            $_CMS['logger']->log("Hit end of page with no hooks graceful-exiting early");
        } else {
            $_CMS['logger']->log("Graceful-exiting early - a hook was likely intercepting");
        }

        $_CMS['logger']->log('PHP run time for ' . $_CMS['path'] . ' took ' . $full_timer_round . ' milliseconds');

        exit();
    }

    function hash_cache_key($key) {
        if($key == null) {
            return null;
        }
        if(is_array($key)) {
            //$key = ksort($key);
            $key = json_encode($key);
        }
        $ret = hash('md5', $key);

        return $ret;
    }

    function retrieve_cache($key) {
        global $_CMS;

        if($_CMS['cache'] == false) {
            return false;
        }

        return $_CMS['cache']->get($key);
    }

    function store_cache($key, $value, $time = 3600) {
        global $_CMS;

        if($_CMS['cache'] == false) {
            return false;
        }

        return $_CMS['cache']->set($key, $value, $time);
    }

    graceful_exit(1);

?>
