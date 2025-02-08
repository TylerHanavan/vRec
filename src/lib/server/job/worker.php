<?php declare(strict_types=1); // strict typing

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

    function get_process_list() {
        $cmd = "ps -ef | grep php | grep worker | grep -v grep";
        $output = '';

        exec($cmd, $output);
        return $output;
    }

    function spawn_worker() {
        global $_CMS;

        if(!empty(get_process_list())) return;

        $cmd = 'nohup sh ' . $_CMS['www_dir'] . '/worker.sh &';
        $output = '';

        exec($cmd, $output);
    }

    function get_worker_pid() {
        $cmd = 'ps -ef | grep php | grep worker | grep -v grep | awk \'{print $2}\'';
        $output = '';

        exec($cmd, $output);

        if(isset($output[0]))
            return $output[0];

        return '-1';
    }

    function kill_worker() {
        $pid = get_worker_pid();
        $cmd = 'kill -9 ' . $pid;
        $output = '';

        exec($cmd, $output);

        return $output;
    }

?>
