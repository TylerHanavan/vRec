<?php

    $hookman->add_hook('add_export_table', array('url' => '/admin/export', 'layer' => 'page_load_pre', 'logged_in' => true));

    function add_export_table($data) {
        $_GET = $data['_GET'];
        $_POST = $data['_POST'];

        if($data['_CMS']['path'] == '/admin/export') {
            if(!isset($_GET['table']) || $_GET['table'] == null || $_GET['table'] == '') {
                graceful_exit();
            }

            $compress_flag = !array_key_exists('compress', $_GET) ? false : ($_GET['compress'] == 'true' ? true : false);

            // Create filename
            $filename = 'SediCMS_export_' . $_GET['table'] . date( 'Y-m-d_H-i-s' ) . '.json';

            if($compress_flag)
                $filename = $filename . '.gz';

            // Force download .json file with JSON in it
            //header("Content-type: application/vnd.ms-excel");
            header("Content-Type: application/force-download");
            header("Content-Type: application/download");
            header("Content-disposition: " . $filename);
            header("Content-disposition: filename=" . $filename);

            $contents = export_table($_GET['table']);

            if($compress_flag)
                $contents = gzencode($contents, 9);

            print $contents;

            graceful_exit();
        }
    }


?>
