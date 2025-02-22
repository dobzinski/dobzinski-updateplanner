<?php

require '../etc/config.php';
require '../etc/session.php';
require '../etc/function.php';
require '../etc/conn_open.php';
require '../etc/var.php';
require '../etc/permission.php';

if (isset($_GET['resource'])) {
    if ($_GET['resource'] == 'portal') {
        if (isset($_SERVER['REQUEST_URI'])) {
            if (strpos($_SERVER['REQUEST_URI'], '/api/login.php?resource=portal')) {
                if (isset($_POST['action'])) {
                    if ($_POST['action'] == 'submit') {
                        $login = controlSpecialChars($_POST['val-user'], $_regex['controllogin']);
                        $password = controlSpecialChars($_POST['val-password'], $_regex['controlpassword']);
                        if (!empty($login) && !empty($password)) {
                            $result = authLogin($login, $password);
                            if ($result == 200) {
                                htmlJs('setTimeout(location.reload.bind(location), 2000);');
                                $_message['type'] = 'success';
                                $_message['message'] = $_alert['authsuccess'];
                            } else if (count($result)) {
                                $_message['type'] = 'danger';
                                $_message['message'] = $result['msg'];
                            }
                        } else {
                            $_message['type'] = 'danger';
                            $_message['message'] = $_alert['loginpasswordrequired'];
                        }
                    }
                }
            }
        }
        if (!isset($_message) || $_message['type'] != 'success') {
            include '../share/php/login.php';
        } else {
            alertMessage();
            echo "<div class=\"pt-4\"><div class=\"spinner-border\" role=\"status\"></div></div>\n";
        }
    }
}

require '../etc/conn_close.php';