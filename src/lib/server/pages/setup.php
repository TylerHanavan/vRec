<?php

    $hookman->add_hook('add_setup', array('url' => '/setup', 'layer' => 'page_load_pre'));

    function add_setup($data) {
        if($data['_CMS']['path'] == '/setup') {
            $tables = list_tables();
            $required_tables = get_required_tables();
            $fix_applied = false;
            for($x = 0; $x < sizeof($required_tables); $x++) {
                $table_name = $required_tables[$x];
                if(!in_array($table_name, $tables)) {
                    echo '<p>You are missing the <b>', $table_name, '</b> table. Generating...</p>';

                    create_table($table_name);

                    $query = read_flat_file($data['_CMS']['www_dir'] . "/sqls/default_$table_name.sql");

                    if($query == null || $query == '' || !isset($query))
                        continue;

                    query($query);

                    $fix_applied = true;
                }
            }
            if(!$fix_applied) {
                echo '<p>Scanned your database, all tables are setup already!</p>';
            }
            graceful_exit();
        }
    }

?>
