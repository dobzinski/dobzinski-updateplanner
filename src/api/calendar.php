<?php

require '../etc/config.php';
require '../etc/session.php';
require '../etc/function.php';
require '../etc/conn_open.php';
require '../etc/var.php';
require '../etc/permission.php';

if (isset($_SESSION['user'])) {
    if ($_SESSION['user']['role'] != 'A') {
        if (isset($_SESSION['user']['role']) && isset($_SESSION['permission']['planning'])) {
            if ($_SESSION['permission']['planning'] == 'N') {
                exit("<p style=\"margin-top: 20px;\">". $_alert['noaccess'] ."</p>\n");
            }
        } else {
            exit("<p style=\"margin-top: 20px;\">". $_alert['noaccess'] ."</p>\n");
        }
    }
    if (isset($_GET['resource']) && isset($_POST['action'])) {
        if ($_GET['resource'] == 'portal') {
            if ($_POST['action'] == 'view') {
                $data = array();
                $year = (isset($_POST['year']) ? str_pad(controlInt($_POST['year']), 2, '0', STR_PAD_LEFT) : date('Y'));
                $month = (isset($_POST['month']) ? str_pad(controlInt($_POST['month']), 2, '0', STR_PAD_LEFT) : date('m'));
                $result = sqlSelect(
                    "SELECT
                        c.id_calendar AS id,
                        c.tx_title AS t_title,
                        c.tx_description AS t_description,
                        c.id_calendar_depends as t_depends,
                        c.fl_complete AS t_complete,
                        DATE_FORMAT(c.dt_schedule, '%d') AS t_day,
                        DATE_FORMAT(c.dt_schedule, '%H:%i') AS t_time,
                        (CASE
                            WHEN e.tx_environment IS NOT NULL THEN e.tx_environment
                            WHEN e2.tx_environment IS NOT NULL THEN e2.tx_environment
                            ELSE '". $_defaults['defaultcolortitle'] ."'
                        END) AS t_environment,
                        (CASE
                            WHEN e.tx_color IS NOT NULL THEN e.tx_color
                            WHEN e2.tx_color IS NOT NULL THEN e2.tx_color
                            ELSE NULL
                        END) AS t_color,
                        i.tp_status AS t_status,
                        t.tx_cluster AS t_cluster,
                        t.fl_downstream AS t_downstream
                    FROM
                        tb_calendar c LEFT JOIN tb_cluster t ON (t.id_cluster = c.id_cluster)
                            LEFT JOIN tb_calendar_item i ON (i.id_calendar = c.id_calendar 
                                AND DATE_FORMAT(i.dt_end, '%Y-%m-%d %H:%i') = (SELECT MAX(DATE_FORMAT(i2.dt_end, '%Y-%m-%d %H:%i')) FROM tb_calendar_item i2 WHERE i2.id_calendar = i.id_calendar))
                            LEFT JOIN tb_environment e ON (e.id_environment = t.id_environment)
                            LEFT JOIN tb_environment e2 ON (e2.id_environment = c.id_environment)
                    WHERE
                        DATE_FORMAT(c.dt_schedule, '%Y-%m-%d %H:%i:%s') BETWEEN ? AND ?
                    ORDER BY
                        t_day ASC, t_time ASC",
                    array($year ."-". $month ."-01 00:00:00", $year ."-". $month ."-31 23:59:59")
                );
                if (count($result)) {
                    $data = array();
                    foreach($result as $v) {
                        $data[$v['t_day']][] = array('time'=>$v['t_time'], 'color'=>$v['t_color'], 'status'=>$v['t_status'], 'title'=>$v['t_title'], 'description'=>$v['t_description'], 'complete'=>$v['t_complete'], 'environment'=>$v['t_environment'], 'cluster'=>$v['t_cluster'], 'downstream'=>$v['t_downstream']);
                    }
                }
                htmlCalendarMonth('planning', $year , $month, $data, (isset($_defaults['startweek']) ? $_defaults['startweek'] : 'Sunday'));
            }
        }
    }
    htmlJs('$(function(){$(\'[data-toggle="tooltip"]\').tooltip()})');
}
    
require '../etc/conn_close.php';