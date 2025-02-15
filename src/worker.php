<?php declare(strict_types=1); // strict typing

    set_time_limit(0);

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    require dirname(__FILE__) . '/lib/server/job/worker.php';
    require dirname(__FILE__) . '/lib/sql_helper.php';

    $_INSTALL_LOCATION = getenv('VREC_INSTALL_LOCATION');

    try {
        $config_file_contents = read_flat_file($_INSTALL_LOCATION . '/config');
    } catch(Exception $e) {
        worker_log("Unable to read config file contents: $_INSTALL_LOCATION/config", "ERROR");
        worker_log($e->getMessage(), "ERROR");
    }

    $config = array();

    $line = strtok($config_file_contents, "\r\n");

    $debug = false; //Keep = false

    while($line != false) {

        if($debug)
            worker_log("Reading line from config: $line", 'DEBUG');

        $kv = explode('=', $line);

        if($debug)
            worker_log($kv[0] . ' ' . $kv[1]);

        $config[$kv[0]] = $kv[1];

        $line = strtok("\r\n");
    }

    $db_user = $config['DB.USER'];
    $db_pass = $config['DB.PASSWORD'];
    $db_ip = $config['DB.IP'];
    $db_name = $config['DB.NAME'];

    $domain = $config['BACKEND.DOMAIN'];

    $domain = explode(',', $domain)[0];

    $audit_dir = $config['AUDIT.DIR'];

    $worker_log_dir = $config['WORKER.LOG.DIR'];

    $http_protocol = 'http';

    if(isset($config['BACKEND.REQUIRE.HTTPS']) && $config['BACKEND.REQUIRE.HTTPS'] == 'true')
        $http_protocol = 'https';

    if($debug) {
        worker_log("DB.USER set to $db_user", 'DEBUG');
        worker_log("DB.PASS set to $db_pass", 'DEBUG');
        worker_log("DB.IP set to $db_ip", 'DEBUG');
        worker_log("DB.NAME set to $db_name", 'DEBUG');
    }

    $conn = get_database_connection($db_ip, $db_user, $db_pass, $db_name);

    if($conn == null) {
        worker_log("Unable to connect to the database!", "ERROR");
    } else {
        $backup_schedules = get_backup_schedules();
        worker_log("Loaded " . sizeof($backup_schedules) . " backup schedules from the DB");
    }

    $ticker = 0;

    $worker_jobs = get_worker_jobs();

    while(true) {

        do_tick($ticker);

        sleep(1);
        if (ob_get_length()) {
            ob_end_flush();
            flush();
        }

        $ticker++;
    }

    function get_worker_jobs() {        
        $response = do_xhr('/xhr/worker/jobs', array(), false);

        var_dump($response);

        return json_decode($response['response'], true)['worker_jobs'];
    }

    function execute_worker_job($job) {
        global $_INSTALL_LOCATION;

        $namespace = $job['namespace'];
        $plugin_file = $job['plugin_file'];
        $func = $job['func'];

        $jobs_path = $_INSTALL_LOCATION . '/worker/jobs/';

        worker_log("Running worker job $namespace, $plugin_file, $func. Full path: " . $jobs_path . $namespace . '/' . $plugin_file);

        worker_log("require_once {$jobs_path}{$namespace}/$plugin_file");

        require_once $jobs_path . $namespace . '/' . $plugin_file;

        $pluginClass = "$namespace\\$namespace";

        $plugin = new $pluginClass();

        $response = $plugin->$func(array());

        worker_log("Response from $namespace/$plugin_file::$func: $response");
    }

    function do_tick($ticker) {
        global $audit_dir;
        global $worker_jobs;
        if($ticker % 300 == 0 || $ticker == 0) {
            worker_log("Standalone worker heartbeat (" . get_worker_pid() . ")", "INFO");
            if($worker_jobs != null) {
                worker_log("I have " . sizeof($worker_jobs) . " worker jobs", "INFO");
                foreach($worker_jobs as $job) {
                    execute_worker_job($job);
                }
            }
        }

        if($ticker % 5 == 0) {

            job_ingest_audit_data($audit_dir);
            
        }

        if($ticker % 60 * 5 == 0) {

            job_backup_tables();

        }
    }

    function job_ingest_audit_data($audit_dir, $process_batch_size = 10) {
        $audit_files = scandir($audit_dir);
        
        $files_count = get_files_in_directory($audit_files);

        if($files_count > 0)
            worker_log("Found " . get_files_in_directory($audit_files) . " files in the audit directory");

        foreach($audit_files as $file) {
            if($file == '.' || $file == '..' || str_ends_with($file, '.lock'))
                continue;

            worker_log("Ingesting audit data from $file");

            $path = $audit_dir . '/' . $file;

            $audit_data = read_flat_file($path);

            $response = do_xhr('/xhr/new_audit', array('audit_data' => $audit_data), true);

            if($response['http_code'] != 200) {
                worker_log("Failed to ingest audit data from $file", "ERROR");
                worker_log("Response from /xhr/new_audit: " . $response['response'], "ERROR");

                rename($path, $path . '.lock');

                continue;
            } else {
                worker_log("Successfully ingested audit data from $file");

                unlink($path);
            }

            if($process_batch_size-- <= 0)
                break;

        }
    }

    function get_files_in_directory($files) {
        $count = 0;

        foreach($files as $file)
            if($file != '.' && $file != '..' && !str_ends_with($file, '.lock'))
                $count++;

        return $count;
    }

    function do_xhr($uri, $data, $post = true) {

        global $http_protocol;
        global $domain;

        $url = "$http_protocol://$domain/$uri";

        echo "worker.php do_xhr url: $url\n";
        
        $ch = curl_init();

        if(!$post) {
            $url .= '?' . http_build_query($data);
        }
        else {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_NOBODY, false); // remove body

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $head = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return array('http_code' => $httpCode, 'response' => $head);
    }

    function job_backup_tables() {
        global $backup_schedules;

        foreach($backup_schedules as $schedule) {
            $table = $schedule['table_name'];
            $destination = $schedule['destination'];
            $frequency = $schedule['frequency'];
            $schedule_id = $schedule['id'];
            $last_backup = get_last_table_backup($table);

            $first_time = new DateTime($last_backup);
            $second_time = new DateTime();

            $diff = $first_time->diff($second_time);

            $elapsed_time = get_elapsed_time($diff);

            worker_log("Last backup of $table was executed $last_backup. Elapsed time since last backup: $elapsed_time");

            if($last_backup == 'Never' || translate_frequency_to_seconds($frequency) < ($second_time->getTimestamp() - $first_time->getTimestamp())) {
                do_backup($table, $destination, $frequency, $schedule_id);
            }
        }
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

    function translate_frequency_to_seconds($frequency) {
        if($frequency == 'hourly') return 60 * 60;
        if($frequency == 'daily') return 60 * 60 * 24;
        if($frequency == 'weekly') return 60 * 60 * 24 * 7;
        if($frequency == 'monthly') return 60 * 60 * 24 * 7 * 4;

        return 60 * 60 * 24;
    }

    function get_backup_schedules() {
        global $conn;

        $query = "SELECT * FROM backup_schedules";
        $stmt = $conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    function get_last_table_backup($table) {
        global $conn;

        $query = "SELECT be.status, be.executed FROM backups_executed AS be, backup_schedules AS bs WHERE bs.table_name = :table AND bs.id = be.schedule_id ORDER BY executed DESC LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->execute(array(':table' => $table));

        $res = $stmt->fetchAll();
        if($res == false || sizeof($res) == 0)
            return 'Never';
        return $res[0]['executed'];
    }

    function do_backup($table, $destination, $frequency, $schedule_id) {
        worker_log("Executing backup of $table to $destination");

        $success = true;

        try {
            insert_table_entry('backups_executed', get_table_columns('backups_executed'), array('schedule_id' => $schedule_id, 'status' => 1, 'executed' => '' . date("Y-m-d H:i:s")));
        } catch(Exception $e) {
            worker_log("Failed while trying to write entry into backups_executed table for $table", "ERROR");
            worker_log($e->getMessage(), "ERROR");
            worker_log("Aborting backup process for $table table because writing to backups_executed table is a critical prerequisite", "ERROR");
            return;
        }

        try {
            write_to_file($destination . '/' . get_backup_file_name($table, $frequency), export_table($table));
        } catch(Exception $e) {
            worker_log("Unable to write to file: " . $destination . '/' . get_backup_file_name($table, $frequency), 'ERROR');
            worker_log($e->getMessage(), "ERROR");
        }

    }

    function get_backup_file_name($table, $frequency) {
        return "backup_${table}_${frequency}_" . date('Y-m-d_H-i-s') . '.json';
    }

    function worker_log($message, $level = 'INFO') {
        log_message("(W " . get_worker_pid() . ") $message", $level);
    }

    function write_to_file($path, $message) {
        $cmd = "mkdir -p " . dirname($path) . "";
        $output = '';

        exec($cmd, $output);

        $cmd = "touch $path &";

        exec($cmd, $output);

        $ffile = fopen($path, "a") or die ("Unable to open $path");
        fwrite($ffile, $message);
        fclose($ffile);
    }

    function log_message($message, $level = 'INFO') {

        global $worker_log_dir;
        global $http_protocol;
        global $domain;

        $datagram = array();
        $datagram['message'] = $message;
        $datagram['level'] = $level;

        if($worker_log_dir != null) {
            $datagram['log_file'] = $worker_log_dir . '/' . date("Ymd") . "/log_" . date("Y-m-d_H-00-00") . ".log";
        }

        $url = "$http_protocol://$domain/log?" . http_build_query($datagram);
        $ch = curl_init($url);

        //curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_HEADER, TRUE);

        curl_setopt($ch, CURLOPT_NOBODY, TRUE); // remove body

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $head = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
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

    function add_hook() {}

?>
