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
        'date'=>'',
        'calendar'=>'',
        'newtitle'=>'',
        'title'=>'',
        'report'=>'',
        'type'=>'Y',
    );
}

$result = sqlSelect(
    "SELECT
        c.id_calendar AS id,
        CONCAT(DATE_FORMAT(c.dt_schedule, '%H:%i'), \" - \", tx_title) AS calendar,
        DATE_FORMAT(c.dt_schedule, '%Y-%m-%d') AS schedule
    FROM
        tb_calendar c
    WHERE
        c.fl_public = ? AND
        (c.fl_complete = ? OR 
        c.dt_complete > DATE_ADD(NOW(), INTERVAL -". $_defaults['abstractslastmonth'] ." MONTH))
    ORDER BY
        schedule DESC, calendar ASC",
    array('Y', 'N')
);
$calendars = array();
if (count($result)) {
    foreach($result as $v) {
        $calendars[$v['id']] = array($v['schedule'], $v['calendar']);
    }
    $result = array();
    unset($result);
}

if (isset($_POST['action'])) {
    if (!empty($id) && $_POST['action'] == 'edit') {
        $result = sqlGet($table,
            array(
                't_date'=>'dt_report',
                't_calendar'=>'id_calendar',
                't_title'=>'tx_newtitle',
                't_report'=>'tx_report',
            ),
            array($key=>$id)
        );
        if (count($result)) {
            $data = array(
                'date'=>$result['t_date'],
                'calendar'=>$result['t_calendar'],
                'newtitle'=>(!empty($result['t_calendar']) ? $result['t_title'] : NULL),
                'title'=>(empty($result['t_calendar']) ? $result['t_title'] : NULL),
                'report'=>$result['t_report'],
                'type'=>(!empty($result['t_calendar']) ? 'Y' : 'N'),
            );
        } else {
            $_message['type'] = 'danger';
            $_message['message'] = $_alert['notfound'];
        }
    }
}

$result = sqlSelect(
    "SELECT
        r.id_calendar_report AS id,
        DATE_FORMAT(r.dt_report, '%Y-%m-%d') AS t_date,
        c.tx_title AS t_calendar,
        r.tx_newtitle AS t_title,
        r.tx_report AS t_report,
        r.dt_record AS t_record
    FROM
        tb_calendar_report r LEFT JOIN tb_calendar c ON (c.id_calendar = r.id_calendar)
    ORDER BY
        t_date DESC"
);

alertMessage();
htmlForm('begin');
htmlHidden('collection', $collection);
htmlHidden('v-id', $id);
htmlAccordion('begin', 'init');
if ($permission == 'W') {
    htmlAccordion('open','init', 'one', 'Form', (isset($accordion) ? $accordion['form'] : true));
    htmlTextDateTime('v-date', 'Date', $data['date'], 10, 'Enter the date of the report.<br><i>If you select a Planning, only the most recent content will be published.</i>', 'yyyy-mm-dd');
    htmlCheckbox('v-type', 'Enable planning selection', ($data['type'] == 'Y' ? true : false), 'controlAbstract();');
    htmlBox('begin','v-box-withplanning');
    htmlSelect('v-calendar', 'Planning', $calendars, $data['calendar'], 'Select the planning that will be reported in the document.<br><i>You are viewing the last 3 month(s) and not completed.</i>');
    htmlText('v-newtitle', 'Title <i>(optional)</i>', $data['newtitle'], 30, '', 'Enter a new title for the document if you want to replace the planning title.');
    htmlBox('end');
    htmlBox('begin','v-box-withoutplanning');
    htmlText('v-title', 'Title', $data['title'], 30, '', 'Enter a title for the document.');
    htmlBox('end');
    htmlTextArea('v-report', 'Report', $data['report'], 'Describe the report that will be inserted into the document topic.');
    htmlButtonSubmit('action');
    htmlButtonCancel($collection);
    htmlAccordion('close');
}
htmlAccordion('open','init', 'two', 'Data', (isset($accordion) ? $accordion['data'] : false));
htmlTable('t-recurrent', array('t_date'=>'Date', 't_calendar'=>'Planning', 't_title'=>'Title', 't_report'=>'Report'), 'id', $result, array('1', 'desc'), ($permission == 'W' ? array('edit', 'delete') : array()), array(), array(), array(), array('t_report'));
htmlAccordion('close');
htmlAccordion('end');
htmlForm('end');
if ($permission == 'W') {
    htmlJs('controlAbstract();');
}