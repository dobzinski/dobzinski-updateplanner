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
        'type'=>'Y',
        'public'=>'Y',
        'depends'=>'',
        'cluster'=>'',
        'environment'=>'',
        'version'=>'',
        'title'=>'',
        'description'=>'',
        'schedule'=>'',
        'complete'=>'',
    );
}

$lastactivity = array();

$result = sqlSelect(
    "SELECT
        c.id_calendar AS id,
        CONCAT(DATE_FORMAT(c.dt_schedule, '%H:%i'), \" - \", tx_title) AS t_depends,
        DATE_FORMAT(c.dt_schedule, '%Y-%m-%d') AS t_schedule
    FROM
        tb_calendar c
    WHERE
        c.fl_complete = ? AND
        c.id_calendar <> ?
    ORDER BY
        t_schedule DESC, t_depends ASC",
    array('N', $id)
);
$depends = array();
if (count($result)) {
    foreach($result as $v) {
        $depends[$v['id']] = array($v['t_schedule'], $v['t_depends']);
    }
    $result = array();
    unset($result);
}

$result = sqlSelect(
    "SELECT
        c.id_cluster AS id,
        c.tx_cluster AS t_cluster,
        e.tx_environment AS t_environment
    FROM
        tb_cluster c JOIN tb_environment e ON (c.id_environment = e.id_environment AND e.fl_active = 'Y')
    WHERE
        c.fl_active = 'Y'
    ORDER BY
        t_environment ASC, t_cluster ASC"
);
$clusters = array();
if (count($result)) {
    foreach($result as $v) {
        $clusters[$v['id']] = array($v['t_environment'], $v['t_cluster']);
    }
    $result = array();
    unset($result);
}

$result = sqlSelect(
    "SELECT
        id_environment AS id,
        tx_environment AS t_environment
    FROM
        tb_environment
    WHERE
        fl_active = 'Y'
    ORDER BY
        t_environment ASC"
);
$environments = array();
if (count($result)) {
    foreach($result as $v) {
        $environments[$v['id']] = $v['t_environment'];
    }
    $result = array();
    unset($result);
}

$result = sqlSelect(
    "SELECT
        v.id_resource_version AS id,
        v.tx_version AS t_version,
        r.tx_resource AS t_resource
    FROM
        tb_resource_version v JOIN tb_resource r ON (v.id_resource = r.id_resource AND r.fl_active = 'Y')
    WHERE
        v.fl_active = 'Y'
    ORDER BY
        t_resource ASC, id DESC"
);
$versions = array();
if (count($result)) {
    foreach($result as $v) {
        $versions[$v['id']] = array($v['t_resource'], $v['t_version']);
    }
    $result = array();
    unset($result);
}

if (isset($table) && isset($_POST['action'])) {
    if (!empty($id) && $_POST['action'] == 'edit') {
        $result = sqlGet($table,
            array(
                't_depends'=>'id_calendar_depends',
                't_cluster'=>'id_cluster',
                't_environment'=>'id_environment',
                't_version'=>'id_resource_version',
                't_title'=>'tx_title',
                't_description'=>'tx_description',
                't_public'=>'fl_public',
                't_schedule'=>'DATE_FORMAT(dt_schedule, \'%Y-%m-%d %H:%i\')',
                't_complete'=>'DATE_FORMAT(dt_complete, \'%Y-%m-%d %H:%i\')',
            ),
            array($key=>$id)
        );
        if (count($result)) {
            $data = array(
                'type'=>(!empty($result['t_cluster']) ? 'Y' : 'N'),
                'public'=>(!empty($result['t_public']) ? 'Y' : 'N'),
                'depends'=>$result['t_depends'],
                'cluster'=>$result['t_cluster'],
                'environment'=>$result['t_environment'],
                'version'=>$result['t_version'],
                'title'=>$result['t_title'],
                'description'=>$result['t_description'],
                'schedule'=>$result['t_schedule'],
                'complete'=>$result['t_complete'],
            );
        } else {
            $_message['type'] = 'danger';
            $_message['message'] = $_alert['notfound'];
        }
    }
}

if (!empty($id)) {
    $check_activity = sqlGet("tb_calendar_item i JOIN tb_calendar c ON(i.id_calendar = c.id_calendar
            AND DATE_FORMAT(i.dt_end, '%Y-%m-%d %H:%i') = (SELECT MAX(DATE_FORMAT(i2.dt_end, '%Y-%m-%d %H:%i')) FROM tb_calendar_item i2 WHERE i2.id_calendar = c.id_calendar))",
        array(
            'laststatus'=>"i.tp_status",
            'lastdate'=>"DATE_FORMAT(i.dt_end, '%Y-%m-%d %H:%i')",
        ),
        array('i.id_calendar'=>$id)
    );
    if (!empty($check_activity)) {
        if (in_array($check_activity['laststatus'], $_statusconclusion)) {
            $lastactivity = array(
                'status'=>(isset($_status[$check_activity['laststatus']]) ? $_status[$check_activity['laststatus']] : ""),
                'date'=>$check_activity['lastdate'],
            );
        } else {
            $lastactivity = array(
                'status'=>(isset($_status[$check_activity['laststatus']]) ? $_status[$check_activity['laststatus']] : ""),
            );
        }
    }
}

$scheduled = sqlSelect(
    "SELECT
        c.id_calendar AS id,
        DATE_FORMAT(c.dt_schedule, '%Y-%m-%d') AS t_date_schedule,
        DATE_FORMAT(c.dt_schedule, '%H:%i') AS t_time_schedule,
        c.tx_title AS t_title,
        t.tx_cluster AS t_cluster,
        (CASE
            WHEN e.tx_environment IS NOT NULL THEN e.tx_environment
            WHEN e2.tx_environment IS NOT NULL THEN e2.tx_environment
            ELSE '". $_defaults['defaultcolortitle'] ."'
        END) AS t_environment,
        r.tx_resource AS t_resource,
        (CASE
            WHEN c.dt_complete IS NOT NULL THEN 'Y'
            ELSE 'N'
        END) AS t_complete,
        (SELECT c2.tx_title FROM tb_calendar c2 WHERE c2.id_calendar = c.id_calendar_depends) as t_depends,
        v.tx_version AS t_version,
        c.tx_description AS t_description,
        c.fl_public as t_public,
        c.dt_record AS t_record
    FROM
        tb_calendar c
            JOIN tb_resource_version v ON (c.id_resource_version = v.id_resource_version)
            JOIN tb_resource r ON (v.id_resource = r.id_resource)
            LEFT JOIN tb_cluster t ON (c.id_cluster = t.id_cluster)
            LEFT JOIN tb_environment e ON (e.id_environment = t.id_environment)
            LEFT JOIN tb_environment e2 ON (c.id_environment = e2.id_environment)
    WHERE
        DATE_FORMAT(c.dt_schedule, '%Y%m%d%H%i') >= DATE_FORMAT(CURRENT_TIMESTAMP, '%Y%m%d%H%i') AND
        c.fl_complete = ?
    ORDER BY
        t_date_schedule DESC, t_time_schedule DESC",
    array('N')
);

$delayed = sqlSelect(
    "SELECT
        c.id_calendar AS id,
        DATE_FORMAT(c.dt_schedule, '%Y-%m-%d') AS t_date_schedule,
        DATE_FORMAT(c.dt_schedule, '%H:%i') AS t_time_schedule,
        c.tx_title AS t_title,
        t.tx_cluster AS t_cluster,
        (CASE
            WHEN e.tx_environment IS NOT NULL THEN e.tx_environment
            WHEN e2.tx_environment IS NOT NULL THEN e2.tx_environment
            ELSE '". $_defaults['defaultcolortitle'] ."'
        END) AS t_environment,
        r.tx_resource AS t_resource,
        (CASE
            WHEN c.dt_complete IS NOT NULL THEN 'Y'
            ELSE 'N'
        END) AS t_complete,
        (SELECT c2.tx_title FROM tb_calendar c2 WHERE c2.id_calendar = c.id_calendar_depends) as t_depends,
        v.tx_version AS t_version,
        c.tx_description AS t_description,
        c.fl_public as t_public,
        c.dt_record AS t_record
    FROM
        tb_calendar c
            JOIN tb_resource_version v ON (c.id_resource_version = v.id_resource_version)
            JOIN tb_resource r ON (v.id_resource = r.id_resource)
            LEFT JOIN tb_cluster t ON (c.id_cluster = t.id_cluster)
            LEFT JOIN tb_environment e ON (e.id_environment = t.id_environment)
            LEFT JOIN tb_environment e2 ON (c.id_environment = e2.id_environment)
    WHERE
        DATE_FORMAT(c.dt_schedule, '%Y%m%d%H%i') < DATE_FORMAT(CURRENT_TIMESTAMP, '%Y%m%d%H%i') AND
        c.fl_complete = ?
    ORDER BY
        t_date_schedule DESC, t_time_schedule DESC",
    array('N')
);

$completed = sqlSelect(
    "SELECT
        c.id_calendar AS id,
        DATE_FORMAT(c.dt_schedule, '%Y-%m-%d') AS t_date_schedule,
        DATE_FORMAT(c.dt_schedule, '%H:%i') AS t_time_schedule,
        c.tx_title AS t_title,
        t.tx_cluster AS t_cluster,
        (CASE
            WHEN e.tx_environment IS NOT NULL THEN e.tx_environment
            WHEN e2.tx_environment IS NOT NULL THEN e2.tx_environment
            ELSE '". $_defaults['defaultcolortitle'] ."'
        END) AS t_environment,
        r.tx_resource AS t_resource,
        (CASE
            WHEN c.dt_complete IS NOT NULL THEN 'Y'
            ELSE 'N'
        END) AS t_schedule,
        (SELECT c2.tx_title FROM tb_calendar c2 WHERE c2.id_calendar = c.id_calendar_depends) as t_depends,
        v.tx_version AS t_version,
        c.tx_description AS t_description,
        c.fl_public as t_public,
        c.dt_record AS t_record
    FROM
        tb_calendar c
            JOIN tb_resource_version v ON (c.id_resource_version = v.id_resource_version)
            JOIN tb_resource r ON (v.id_resource = r.id_resource)
            LEFT JOIN tb_cluster t ON (c.id_cluster = t.id_cluster)
            LEFT JOIN tb_environment e ON (e.id_environment = t.id_environment)
            LEFT JOIN tb_environment e2 ON (c.id_environment = e2.id_environment)
    WHERE
        c.fl_complete = ?
    ORDER BY
        t_date_schedule DESC, t_time_schedule DESC",
    array('Y')
);

alertMessage();
htmlForm('begin');
htmlHidden('collection', $collection);
htmlHidden('v-id', $id);
htmlAccordion('begin', 'init');
if ($permission == 'W') {
    htmlAccordion('open','init', 'one', 'Form', (isset($accordion) ? $accordion['form'] : true));
    htmlCheckbox('v-public', 'Reporting', ($data['public'] == 'Y' ? true : false), '', true, 'Disable it if you don\'t want to publish the item to the document.');
    htmlText('v-title', 'Title', $data['title'], 40, '', 'Enter a title for the calendar, you can add multiple calendars.');
    htmlTextDateTime('v-schedule', 'Schedule', $data['schedule'], 16, 'Enter date and time for scheduling.');
    htmlSelect('v-version', 'Resource', $versions, $data['version'], 'Select the resource version reference.');
    htmlCheckbox('v-type', 'Schedule for cluster resource', ($data['type'] == 'Y' ? true : false), 'controlCluster();');
    htmlBox('begin','v-box-cluster');
    htmlSelect('v-cluster', 'Cluster', $clusters, $data['cluster'], 'Select the cluster associated with the selected resource.');
    htmlBox('end');
    htmlBox('begin','v-box-environment');
    htmlSelect('v-environment', 'Environment <i>(optional)</i>', $environments, $data['environment'], 'Select the environment associated with the selected resource.');
    htmlBox('end');
    htmlSelect('v-depends', 'Depends <i>(optional)</i>', $depends, $data['depends'], 'Select another schedule if it depends on the completion to begin.');
    htmlText('v-description', 'Description <i>(optional)</i>', $data['description'], 255, '', 'Enter a description about the planning.');
    htmlTextDateTime('v-complete', 'Completed <i>(optional)</i>', $data['complete'], 16, 'Enter the end date and time for the schedule to be completed.'. (!count($lastactivity) ? (empty($id) ? "" : "<br><i>Using a simple planning, no activities items were found!</i>") : (isset($lastactivity['status']) && isset($lastactivity['date']) ? "<br><i>The last activity status: <b>". $lastactivity['status'] ."</b>, to enter the same end date <b><a href=\"#\" onclick=\"return addDateCompleted('". $lastactivity['date'] ."');\">click here</a></b>.</i>" : (isset($lastactivity['status']) ? "<br><i>The last activity \"On Track\" status: <b>". $lastactivity['status'] ."</b>, before entering the date, need to use any \"Completion\" status.</i>" : "")) . "<br><i>After entering the date, all activity items in this planning will be locked!</i>"));
    htmlButtonSubmit('action');
    htmlButtonCancel($collection);
    htmlAccordion('close');
}
htmlAccordion('begin', 'init');
htmlAccordion('open','init', 'two', 'Calendar', (isset($accordion) ? $accordion['calendar'] : false));
htmlCalendarArea(date('Y'), date('m'));
htmlAccordion('close');
htmlAccordion('begin', 'init');
htmlAccordion('open','init', 'three', 'Scheduled ('. count($scheduled) .')', (isset($accordion) ? $accordion['data'] : true));
htmlTable('t-scheduled', array('t_date_schedule'=>'Date', 't_time_schedule'=>'Time', 't_title'=>'Title', 't_environment'=>'Environment', 't_resource'=>'Resource', 't_version'=>'version', 't_depends'=>'depends', 't_description'=>'description', 't_public'=>'Doc'), 'id', $scheduled, array('1', 'desc'), ($permission == 'W' ? array('complete', 'edit', 'delete') : array()), array('t_public'), array(), array(), array('t_version', 't_depends', 't_description'));
htmlAccordion('close');
htmlAccordion('begin', 'init');
htmlAccordion('open','init', 'four', 'Delayed ('. count($delayed) .')', (isset($accordion) ? $accordion['data'] : true));
htmlTable('t-delayed', array('t_date_schedule'=>'Date', 't_time_schedule'=>'Time', 't_title'=>'Title', 't_environment'=>'Environment', 't_resource'=>'Resource', 't_version'=>'version', 't_depends'=>'depends', 't_description'=>'description', 't_public'=>'Doc'), 'id', $delayed, array('1', 'desc'), ($permission == 'W' ? array('complete', 'edit', 'delete') : array()), array('t_public'), array(), array(), array('t_version', 't_depends', 't_description'));
htmlAccordion('close');
htmlAccordion('begin', 'init');
htmlAccordion('open','init', 'five', 'Completed', (isset($accordion) ? $accordion['data'] : true));
htmlTable('t-completed', array('t_date_schedule'=>'Date', 't_time_schedule'=>'Time', 't_title'=>'Title', 't_environment'=>'Environment', 't_resource'=>'Resource', 't_version'=>'version', 't_depends'=>'depends', 't_description'=>'description', 't_public'=>'Doc'), 'id', $completed, array('1', 'desc'), ($permission == 'W' ? array('schedule') : array()), array('t_public'), array(), array(), array('t_version', 't_depends', 't_description'));
htmlAccordion('close');
htmlAccordion('end');
htmlForm('end');
if ($permission == 'W') {
    htmlJs('controlCluster();');
}