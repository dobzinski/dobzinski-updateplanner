<?php

require '../etc/config.php';
require '../etc/session.php';
require '../etc/function.php';
require '../etc/conn_open.php';
require '../etc/var.php';
require '../etc/permission.php';

if (isset($_SESSION['user'])) {
    if (isset($_POST['control'])) {

    }
    if (isset($_GET['resource'])) {
        if ($_GET['resource'] == 'portal') {
            include '../share/php/init.php';
        }
    }
}

require '../etc/conn_close.php';