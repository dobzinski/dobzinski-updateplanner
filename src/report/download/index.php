<?php

require '../../etc/config.php';
require '../../etc/session.php';
require '../../etc/function.php';
require '../../etc/conn_open.php';
require '../../etc/var.php';
require '../../etc/permission.php';

if (isset($_POST['resource'])) {
    if ($_POST['resource'] == 'portal') {
        if (isset($_SESSION['user'])) {
            if (isset($_POST['v-file'])) {
                $filename = controlSpecialChars($_POST['v-file'], '[^a-zA-Z0-9\_]');
                $pdf = "../../data/pdf/". $filename .".pdf";
                if (is_file($pdf)) {
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="'. basename($pdf) .'"');
                    header('Content-Transfer-Encoding: binary');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Pragma: public');
                    header('Content-Length: '. filesize($pdf));
                    ob_clean();
                    flush();
                    readfile($pdf); 
                } else {
                    exit('PDF not found!');
                }
            }
        }
    }
}

require '../../etc/conn_close.php';