<?php

require 'etc/config.php';
require 'etc/session.php';
require 'etc/function.php';
require 'etc/conn_open.php';
require 'etc/var.php';
require 'etc/permission.php';
if (isset($_SESSION['user'])) {
    include_once 'share/html/top.php';
    include_once 'share/html/bottom.php';
} else {
    include_once 'share/html/welcome.php';
}
require 'etc/conn_close.php';