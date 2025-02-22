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
        'resource'=>'',
        'version'=>'',
        'active'=>'Y',
    );
}

$result = sqlSelect(
    "SELECT
        id_resource AS id,
        tx_resource AS t_resource
    FROM
        tb_resource
    WHERE
        fl_active = 'Y'
    ORDER BY
        t_resource ASC"
);
$resources = array();
if (count($result)) {
    foreach($result as $v) {
        $resources[$v['id']] = $v['t_resource'];
    }
    $result = array();
    unset($result);
}

if (isset($_POST['action'])) {
    if (!empty($id) && $_POST['action'] == 'edit') {
        $result = sqlGet($table,
            array(
                't_resource'=>'id_resource',
                't_version'=>'tx_version',
                't_active'=>'fl_active',
            ),
            array($key=>$id)
        );
        if (count($result)) {
            $data = array(
                'resource'=>$result['t_resource'],
                'version'=>$result['t_version'],
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
        v.id_resource_version AS id,
        DATE_FORMAT(v.dt_record, '%Y-%m-%d') AS t_record,
        r.tx_resource AS t_resource,
        v.tx_version AS t_version,
        v.fl_script AS t_script,
        v.fl_active AS t_active,
        r.fl_active AS t_active_resource
    FROM
        tb_resource_version v JOIN tb_resource r ON (v.id_resource = r.id_resource)
    ORDER BY
        id DESC"
);

alertMessage();
htmlForm('begin');
htmlHidden('collection', $collection);
htmlHidden('v-id', $id);
htmlAccordion('begin', 'init');
if ($permission == 'W') {
    htmlAccordion('open','init', 'one', 'Form', (isset($accordion) ? $accordion['form'] : true));
    htmlSelect('v-resource', 'Resource', $resources, $data['resource'], 'Select the resource reference.');
    htmlText('v-version', 'Version', $data['version'], 40, '', 'Enter a release version, you can add multiple versions.');
    htmlCheckbox('v-active', 'Active', ($data['active'] == 'Y' ? true : false));
    htmlButtonSubmit('action');
    htmlButtonCancel($collection);
    htmlAccordion('close');
}
htmlAccordion('open','init', 'two', 'Data', (isset($accordion) ? $accordion['data'] : false));
htmlTable('t-version', array('t_record'=>'Date', 't_resource'=>'Resource', 't_version'=>'Version', 't_script'=>'Bot', 't_active'=>'Act'), 'id', $result, array(), ($permission == 'W' ? array('edit', 'delete') : array()), array('t_script', 't_active'));
htmlAccordion('close');
htmlAccordion('end');
htmlForm('end');