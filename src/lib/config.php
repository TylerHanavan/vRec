<?php

    $_CONF = array();

    if(PHP_OS_FAMILY == 'Windows') {
        $_CONF['config_path'] = 'C:\SediCMS\config';
    } else {
        $_CONF['config_path'] = '/opt/sedicms/config';
    }

    try {
        $config_file_contents = read_flat_file($_CONF['config_path']);
    } catch(Exception $e) {
        $_CMS['logger']->log("Unable to read config file contents: " . $_CONF['config_path'], "ERROR");
        $_CMS['logger']->log($e->getMessage(), "ERROR");
    }

    $line = strtok($config_file_contents, "\r\n");

    while($line != false) {

        $kv = explode('=', $line);

        $retrieved_value = retrieve_cache('config_' . $kv[0]);

        if($retrieved_value != false) {
            $_CONF[$kv[0]] = $retrieved_value;
        } else {
            store_cache('config_' . $kv[0], $kv[1], 20);
            $_CONF[$kv[0]] = $kv[1];
        }

        $line = strtok("\r\n");
    }

    function get_config_value($key) {
        global $_CONF;

        return (isset($_CONF[$key]) && !empty($_CONF[$key])) ? $_CONF[$key] : null;
    }

?>
