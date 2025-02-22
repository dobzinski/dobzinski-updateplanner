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
        'url'=>'',
        'eol'=>'',
        'eom'=>'',
        'active'=>'Y',
    );
}

if (isset($_POST['action'])) {
    if (!empty($id) && $_POST['action'] == 'edit') {
        $result = sqlGet($table,
            array(
                't_resource'=>'tx_resource',
                't_url'=>'tx_url',
                't_eol'=>'dt_eol',
                't_eom'=>'dt_eom',
                't_active'=>'fl_active',
            ),
            array($key=>$id)
        );
        if (count($result)) {
            $data = array(
                'resource'=>$result['t_resource'],
                'url'=>$result['t_url'],
                'eol'=>$result['t_eol'],
                'eom'=>$result['t_eom'],
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
        r.id_resource AS id,
        r.tx_resource AS t_resource,
        DATE_FORMAT(r.dt_eol, '%Y-%m-%d') AS t_eol,
        DATE_FORMAT(r.dt_eom, '%Y-%m-%d') AS t_eom,
        COUNT(v.id_resource_version) AS t_total,
        r.fl_active AS t_active,
        r.tx_url AS t_url,
        r.dt_record AS t_record
    FROM
        tb_resource r LEFT JOIN tb_resource_version v ON (r.id_resource = v.id_resource)
    GROUP BY
        id, t_resource, t_eol, t_eom, t_active, t_url, t_record
    ORDER BY
        t_resource ASC"
);

alertMessage();
htmlForm('begin');
htmlHidden('collection', $collection);
htmlHidden('v-id', $id);
htmlAccordion('begin', 'init');
if ($permission == 'W') {
    htmlAccordion('open','init', 'one', 'Form', (isset($accordion) ? $accordion['form'] : true));
    htmlText('v-resource', 'Name', $data['resource'], 30, '', 'Enter a resource name, you can add multiple resources.');
    htmlText('v-url', 'URL <i>(optional)</i>', $data['url'], 255, 'https://github.com/owner/repository', 'Enter the project URL to fetch the latest version.');
    htmlTextDateTime('v-eol', 'EOL <i>(optional)</i>', $data['eol'], 10, 'Enter the <i>end of life</i> date.', 'yyyy-mm-dd');
    htmlTextDateTime('v-eom', 'EOM <i>(optional)</i>', $data['eom'], 10, 'Enter the <i>end of maintenance</i> date.', 'yyyy-mm-dd');
    htmlCheckbox('v-active', 'Active', ($data['active'] == 'Y' ? true : false));
    htmlButtonSubmit('action');
    htmlButtonCancel($collection);
    htmlAccordion('close');
}
htmlAccordion('open','init', 'two', 'Data', (isset($accordion) ? $accordion['data'] : false));
htmlTable('t-resource', array('t_resource'=>'Resource', 't_eol'=>'EOL', 't_eom'=>'EOM', 't_total'=>'Amt', 't_active'=>'Act', 't_url'=>'URL'), 'id', $result, array('1', 'asc'), ($permission == 'W' ? array('edit', 'delete') : array()), array('t_active'), array(), array('t_url'));
htmlAccordion('close');
htmlAccordion('end');
htmlForm('end');