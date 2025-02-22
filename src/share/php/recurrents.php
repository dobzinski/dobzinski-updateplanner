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
        'title'=>'',
        'report'=>'',
        'active'=>'Y',
    );
}

if (isset($_POST['action'])) {
    if (!empty($id) && $_POST['action'] == 'edit') {
        $result = sqlGet($table,
            array(
                't_title'=>'tx_title',
                't_report'=>'tx_report',
                't_active'=>'fl_active',
            ),
            array($key=>$id)
        );
        if (count($result)) {
            $data = array(
                'title'=>$result['t_title'],
                'report'=>$result['t_report'],
                'active'=>$result['t_active'],
            );
        } else {
            $_message['type'] = 'danger';
            $_message['message'] = $_alert['notfound'];
        }
    }
}

$result = sqlSelect(
    "SELECT
        id_recurrent AS id,
        tx_title AS t_title,
        tx_report AS t_report,
        fl_active AS t_active,
        dt_record AS t_record
    FROM
        tb_recurrent
    ORDER BY
        t_title ASC"
);

alertMessage();
htmlForm('begin');
htmlHidden('collection', $collection);
htmlHidden('v-id', $id);
htmlAccordion('begin', 'init');
if ($permission == 'W') {
    htmlAccordion('open','init', 'one', 'Form', (isset($accordion) ? $accordion['form'] : true));
    htmlText('v-title', 'Title', $data['title'], 30, '', 'Enter a title, you can add multiple recurring items at the beginning of the document.');
    htmlTextArea('v-report', 'Report', $data['report'], 'Describe the report that will be inserted into the document topic.');
    htmlCheckbox('v-active', 'Active', ($data['active'] == 'Y' ? true : false));
    htmlButtonSubmit('action');
    htmlButtonCancel($collection);
    htmlAccordion('close');
}
htmlAccordion('open','init', 'two', 'Data', (isset($accordion) ? $accordion['data'] : false));
htmlTable('t-recurrent', array('t_title'=>'Title', 't_report'=>'Report', 't_active'=>'Act'), 'id', $result, array('1', 'asc'), ($permission == 'W' ? array('edit', 'delete') : array()), array('t_active'), array(), array(), array('t_report'));
htmlAccordion('close');
htmlAccordion('end');
htmlForm('end');