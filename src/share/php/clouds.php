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
        'cloud'=>'',
        'active'=>'Y',
    );
}

if (isset($_POST['action'])) {
    if (!empty($id) && $_POST['action'] == 'edit') {
        $result = sqlGet($table,
            array(
                't_cloud'=>'tx_cloud',
                't_active'=>'fl_active',
            ),
            array($key=>$id)
        );
        if (count($result)) {
            $data = array(
                'cloud'=>$result['t_cloud'],
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
        id_cloud AS id,
        tx_cloud AS t_cloud,
        fl_active AS t_active,
        dt_record AS t_record
    FROM
        tb_cloud
    ORDER BY
        t_cloud ASC"
);

alertMessage();
htmlForm('begin');
htmlHidden('collection', $collection);
htmlHidden('v-id', $id);
htmlAccordion('begin', 'init');
if ($permission == 'W') {
    htmlAccordion('open','init', 'one', 'Form', (isset($accordion) ? $accordion['form'] : true));
    htmlText('v-cloud', 'Name', $data['cloud'], 30, '', 'Enter a cloud name, you can add multiple clouds.');
    htmlCheckbox('v-active', 'Active', ($data['active'] == 'Y' ? true : false));
    htmlButtonSubmit('action');
    htmlButtonCancel($collection);
    htmlAccordion('close');
}
htmlAccordion('open','init', 'two', 'Data', (isset($accordion) ? $accordion['data'] : false));
htmlTable('t-cloud', array('t_cloud'=>'Cloud', 't_active'=>'Act'), 'id', $result, array('1', 'asc'), ($permission == 'W' ? array('edit', 'delete') : array()), array('t_active'));
htmlAccordion('close');
htmlAccordion('end');
htmlForm('end');