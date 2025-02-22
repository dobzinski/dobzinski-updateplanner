<?php

if (isset($_SESSION['user']['role'])) {
    if ($_SESSION['user']['role'] != 'A') {
        if (isset($_SESSION['permission'][$collection])) {
            $permission = ($_SESSION['permission'][$collection] == 'W' ? 'W' : 'R');
        }
    } else {
        $permission = 'W';
    }
} else {
    exit("<p style=\"margin-top: 20px;\">". $_alert['noaccess'] ."</p>\n");
}

if (!isset($data)) {
    $data = array(
        'calendar'=>'',
        'comment'=>'',
        'percent'=>'0',
        'start'=>'',
        'end'=>'',
        'status'=>'N',
    );
}

$status = array();
if (count($_status) && count($_statusconclusion)) {
    foreach($_status as $k=>$v) {
        $status[$k] = array(
            (!in_array($k, $_statusconclusion) ? 'On Track' : 'Conclusion'),
            $_status[$k],
        );
    }
}

if (isset($_POST['action'])) {
    if (!empty($id) && $_POST['action'] == 'edit') {
        $result = sqlGet($table,
            array(
                't_calendar'=>'id_calendar',
                't_comment'=>'tx_comment',
                't_percent'=>'nu_percent',
                't_status'=>'tp_status',
                't_start'=>'DATE_FORMAT(dt_start, \'%Y-%m-%d %H:%i\')',
                't_end'=>'DATE_FORMAT(dt_end, \'%Y-%m-%d %H:%i\')',
            ),
            array($key=>$id)
        );
        if (count($result)) {
            $data = array(
                'calendar'=>$result['t_calendar'],
                'comment'=>$result['t_comment'],
                'percent'=>$result['t_percent'],
                'status'=>$result['t_status'],
                'start'=>$result['t_start'],
                'end'=>$result['t_end'],
            );
        } else {
            $_message['type'] = 'danger';
            $_message['message'] = $_alert['notfound'];
        }
    }
}

if (!empty($data['calendar']) && $_POST['action'] == 'edit') {
    $result = sqlSelect(
        "SELECT
            c.id_calendar AS id,
            CONCAT(DATE_FORMAT(c.dt_schedule, '%H:%i'), \" - \", c.tx_title) AS calendar,
            DATE_FORMAT(c.dt_schedule, '%Y-%m-%d') AS schedule
        FROM
            tb_calendar c
        WHERE
            c.fl_complete = ? OR
            c.id_calendar = ?
        ORDER BY
            schedule DESC, calendar ASC",
        array('N', $data['calendar'])
    );
} else {
    $result = sqlSelect(
        "SELECT
            c.id_calendar AS id,
            CONCAT(DATE_FORMAT(c.dt_schedule, '%H:%i'), \" - \", c.tx_title) AS calendar,
            DATE_FORMAT(c.dt_schedule, '%Y-%m-%d') AS schedule
        FROM
            tb_calendar c
        WHERE
            c.fl_complete = ?
        ORDER BY
            schedule DESC, calendar ASC",
        array('N')
    );
}
$calendars = array();
if (count($result)) {
    foreach($result as $v) {
        $calendars[$v['id']] = array($v['schedule'], $v['calendar']);
    }
    $result = array();
    unset($result);
}

$result = sqlSelect(
    "SELECT
        c.id_calendar AS id,
        c.id_calendar_depends AS t_depends,
        c.tx_title AS t_title,
        DATE_FORMAT(c.dt_schedule, '%Y-%m-%d %H:%i') AS t_calendar,
        i.tx_comment as t_comment,
        i.nu_percent as t_percent,
        (CASE
            WHEN i.tp_status = 'N' THEN '". $_status['N'] ."'
            WHEN i.tp_status = 'W' THEN '". $_status['W'] ."'
            WHEN i.tp_status = 'C' THEN '". $_status['C'] ."'
            WHEN i.tp_status = 'R' THEN '". $_status['R'] ."'
            WHEN i.tp_status = 'S' THEN '". $_status['S'] ."'
            WHEN i.tp_status = 'D' THEN '". $_status['D'] ."'
            ELSE 'Other'
        END) AS t_status,
        DATE_FORMAT(i.dt_start, '%Y-%m-%d %H:%i') AS t_activity_start,
        DATE_FORMAT(i.dt_end, '%Y-%m-%d %H:%i') AS t_activity_end,
        (CASE
            WHEN e.tx_environment IS NOT NULL THEN e.tx_environment
            WHEN e2.tx_environment IS NOT NULL THEN e2.tx_environment
            ELSE '". $_defaults['defaultcolortitle'] ."'
        END) AS t_environment,
        (CASE
            WHEN e.tx_color IS NOT NULL THEN e.tx_color
            WHEN e2.tx_color IS NOT NULL THEN e2.tx_color
            ELSE '". $_defaults['defaultcolorcode'] ."'
        END) AS t_color,
        t.tx_cluster AS t_cluster,
        t.fl_downstream as t_downstream
    FROM
        tb_calendar_item i RIGHT JOIN tb_calendar c ON (i.id_calendar = c.id_calendar)
            LEFT JOIN tb_cluster t ON (c.id_cluster = t.id_cluster)
            LEFT JOIN tb_environment e ON (e.id_environment = t.id_environment)
            LEFT JOIN tb_environment e2 ON (c.id_environment = e2.id_environment)
    WHERE
        c.fl_complete = ?
    ORDER BY
        t_calendar ASC, t_activity_start ASC, t_percent DESC",
    array('N')
);
$map=array();
$used = array();
$hours = 0;
$activities=array();
if (count($result)) {
    foreach($result as $v) {
        if (@!in_array($activities[$v['id']], $activities)) {
            $activities[$v['id']] = array(
                'depends'=>(empty($v['t_depends']) ? 0 : $v['t_depends']),
                'title'=>$v['t_title'],
                'calendar'=>$v['t_calendar'],
                'environment'=>$v['t_environment'],
                'color'=>$v['t_color'],
                'cluster'=>$v['t_cluster'],
                'downstream'=>$v['t_downstream'],
            );
        }
        if (!empty($v['t_activity_start'])) {
            $activities[$v['id']]['data'][] = array(
                'comment'=>$v['t_comment'],
                'percent'=>$v['t_percent'],
                'status'=>$v['t_status'],
                'start'=>$v['t_activity_start'],
                'end'=>$v['t_activity_end'],
            );
        }
    }
    $map = htmlActivitiesMap($activities);
    $result = array();
    unset($result);
}

$actived = sqlSelect(
    "SELECT
        DATE_FORMAT(i.dt_start, '%Y-%m-%d') AS t_start,
        DATE_FORMAT(i.dt_end, '%Y-%m-%d') AS t_end,
        c.tx_title AS t_calendar,
        i.tx_comment AS t_comment,
        i.nu_percent AS t_percent,
        (CASE
            WHEN i.tp_status = 'N' THEN '". $_status['N'] ."'
            WHEN i.tp_status = 'W' THEN '". $_status['W'] ."'
            WHEN i.tp_status = 'C' THEN '". $_status['C'] ."'
            WHEN i.tp_status = 'R' THEN '". $_status['R'] ."'
            WHEN i.tp_status = 'S' THEN '". $_status['S'] ."'
            WHEN i.tp_status = 'D' THEN '". $_status['D'] ."'
        END) AS t_status,
        c.dt_complete AS t_complete,
        i.dt_record AS t_record,
        i.id_calendar_item AS id
    FROM
        tb_calendar_item i JOIN tb_calendar c ON (i.id_calendar = c.id_calendar)
    WHERE
        c.fl_complete = ?
    ORDER BY
        id ASC",
    array('N')
);

$completed = sqlSelect(
    "SELECT
        DATE_FORMAT(i.dt_start, '%Y-%m-%d') AS t_start,
        DATE_FORMAT(i.dt_end, '%Y-%m-%d') AS t_end,
        c.tx_title AS t_calendar,
        i.tx_comment AS t_comment,
        i.nu_percent AS t_percent,
        (CASE
            WHEN i.tp_status = 'N' THEN '". $_status['N'] ."'
            WHEN i.tp_status = 'W' THEN '". $_status['W'] ."'
            WHEN i.tp_status = 'C' THEN '". $_status['C'] ."'
            WHEN i.tp_status = 'R' THEN '". $_status['R'] ."'
            WHEN i.tp_status = 'S' THEN '". $_status['S'] ."'
            WHEN i.tp_status = 'D' THEN '". $_status['D'] ."'
        END) AS t_status,
        i.dt_record AS t_record,
        i.id_calendar_item AS id
    FROM
        tb_calendar_item i JOIN tb_calendar c ON (i.id_calendar = c.id_calendar)
    WHERE
        c.fl_complete = ?
    ORDER BY
        id ASC",
    array('Y')
);

if ($map == 'broken') {
    $_message['type'] = 'danger';
    $_message['message'] = $_alert['broken_activities'];
}

alertMessage();
htmlForm('begin');
htmlHidden('collection', $collection);
htmlHidden('v-id', $id);
htmlAccordion('begin', 'init');
if ($permission == 'W') {
    htmlAccordion('open','init', 'one', 'Form', (isset($accordion) ? $accordion['form'] : (count($activities) ? true : false)));
    htmlSelect('v-calendar', 'Calendar', $calendars, $data['calendar'], 'Select the calendar associated with the activity, you can add multiple activities.');
    htmlTextDateTime('v-start', 'Start', $data['start'], 16, 'Enter date and time to start activity.');
    htmlTextDateTime('v-end', 'End', $data['end'], 16, 'Enter date and time to end activity.');
    htmlSelect('v-status', 'Status', $status, $data['status'], 'Select the status associated with the activity.', false);
    htmlRange('v-percent', 'Percent <i>(optional)</i>', $data['percent'], 'Enter the progress percent value to complete.');
    htmlText('v-comment', 'Comment <i>(optional)</i>', $data['comment'], 255, '', 'Enter a comment about the activity.');
    htmlButtonSubmit('action');
    htmlButtonCancel($collection);
    htmlAccordion('close');
}
htmlAccordion('begin', 'init');
htmlAccordion('open','init', 'two', 'Activities', (isset($accordion) ? $accordion['activities'] : (count($activities) ? false : true)));
if ($map != 'broken') {
    htmlActivities('t-activities');
}
htmlAccordion('close');
htmlAccordion('end');
htmlAccordion('begin', 'init');
htmlAccordion('open','init', 'three', 'On Track ('. count($actived) .')', (isset($accordion) ? $accordion['data'] : true));
htmlTable('t-active', array('t_start'=>'Start', 't_end'=>'End', 't_calendar'=>'Title', 't_comment'=>'Comment', 't_percent'=>'%', 't_status'=>'Status'), 'id', $actived, array('0', 'desc'), ($permission == 'W' ? array('edit', 'delete') : array()), array(), array(), array(), array('t_comment'));
htmlAccordion('close');
htmlAccordion('end');
htmlAccordion('begin', 'init');
htmlAccordion('open','init', 'Four', 'Completed Planning', (isset($accordion) ? $accordion['data'] : true));
htmlTable('t-complete', array('t_start'=>'Start', 't_end'=>'End', 't_calendar'=>'Title', 't_comment'=>'Comment', 't_percent'=>'%', 't_status'=>'Status'), 'id', $completed, array('0', 'desc'), array(), array(), array(), array(), array('t_comment'));
htmlAccordion('close');
htmlAccordion('end');
htmlForm('end');
htmlJs('$(function(){$(\'[data-toggle="tooltip"]\').tooltip()})');