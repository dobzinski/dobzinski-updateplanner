<?php

require '../etc/config.php';
require '../etc/session.php';
require '../etc/function.php';
require '../etc/conn_open.php';
require '../etc/var.php';
require '../etc/permission.php';

$collection = 'reports';

if (isset($_SESSION['user'])) {
    if (isset($_SESSION['user']['role'])) {
        if ($_SESSION['user']['role'] != 'A') {
            if (isset($_SESSION['permission'][$collection])) {
                if ($_SESSION['permission'][$collection] != 'N') {
                    $permission = ($_SESSION['permission'][$collection] == 'W' ? 'W' : 'R');
                } else {
                    exit("<p style=\"margin-top: 20px;\">". $_alert['noaccess'] ."</p>\n");
                }
            }
        } else {
            $permission = 'W';
        }
    } else {
        exit("<p style=\"margin-top: 20px;\">". $_alert['noaccess'] ."</p>\n");
    }
    if (isset($_GET['resource'])) {
        if ($_GET['resource'] == 'portal') {
            if (isset($_POST['report'])) {
                switch($_POST['report']) {
                    case 'queue':
                        if ($permission == 'W') {
                            $files = array();
                            getReports('../report/queue');
                            $queue = array();
                            if (count($files)) {
                                foreach($files as $i=>$f) {
                                    foreach($f as $k=>$v) {
                                        if ($k == 'date' || $k == 'start' || $k == 'end') {
                                            if (strlen($v) == 12) {
                                                $queue[$i][$k] = preg_replace('/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})/', '$1-$2-$3 $4:$5', $v);
                                            } else if (strlen($v) == 8) {
                                                $queue[$i][$k] = preg_replace('/(\d{4})(\d{2})(\d{2})/', '$1-$2-$3', $v);
                                            } else {
                                                $queue[$i][$k] = $v;
                                            }
                                        } else if ($k == 'extension') {
                                            $queue[$i][$k] = (!empty($v) ? ($v == 'wait' ? 'Prepared' : 'Working') : '');
                                        } else {
                                            $queue[$i][$k] = $v;
                                        }
                                    }
                                }
                            }
                            htmlTable('t-queue', array('date'=> 'Date', 'report'=> 'Report', 'start'=> 'Start', 'end'=> 'End', 'extension'=> 'Status'), 'id', $queue, array('1', 'desc'), array());
                        }
                    break;
                    case 'available':
                        $files = array();
                        getReports('../data/pdf');
                        $pdf = array();
                        if (count($files)) {
                            foreach($files as $i=>$f) {
                                foreach($f as $k=>$v) {
                                    if ($k == 'date' || $k == 'start' || $k == 'end') {
                                        if (strlen($v) == 12) {
                                            $pdf[$i][$k] = preg_replace('/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})/', '$1-$2-$3 $4:$5', $v);
                                        } else if (strlen($v) == 8) {
                                            $pdf[$i][$k] = preg_replace('/(\d{4})(\d{2})(\d{2})/', '$1-$2-$3', $v);
                                        } else {
                                            $pdf[$i][$k] = $v;
                                        }
                                    } else {
                                        $pdf[$i][$k] = $v;
                                    }
                                }
                            }
                        }
                        htmlTable('t-available', array('date'=> 'Date', 'report'=> 'Report', 'start'=> 'Start', 'end'=> 'End'), 'id', $pdf, array('1', 'desc'), ($permission == 'W' ? array('download', 'delete') : array('download')));
                    break;
                }

            }
        }
    }
}

require '../etc/conn_close.php';