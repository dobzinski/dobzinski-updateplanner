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
        'environment'=>'',
        'color'=>'',
        'production'=>'',
        'support'=>'',
        'active'=>'Y',
    );
}

if (isset($_POST['action'])) {
    if (!empty($id) && $_POST['action'] == 'edit') {
        $result = sqlGet($table,
            array(
                't_environment'=>'tx_environment',
                't_color'=>'tx_color',
                't_production'=>'fl_production',
                't_support'=>'fl_support',
                't_active'=>'fl_active',
            ),
            array($key=>$id)
        );
        if (count($result)) {
            $data = array(
                'environment'=>$result['t_environment'],
                'color'=>"#". $result['t_color'],
                'production'=>$result['t_production'],
                'support'=>$result['t_support'],
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
        id_environment AS id,
        tx_environment AS t_environment,
        tx_color AS t_color,
        fl_production AS t_production,
        fl_support AS t_support,
        fl_active AS t_active,
        dt_record AS t_record
    FROM
        tb_environment
    ORDER BY
        t_environment ASC"
);

alertMessage();
htmlForm('begin');
htmlHidden('collection', $collection);
htmlHidden('v-id', $id);
htmlAccordion('begin', 'init');
if ($permission == 'W') {
    htmlAccordion('open','init', 'one', 'Form', (isset($accordion) ? $accordion['form'] : true));
    htmlCheckbox('v-support', 'Support', ($data['support'] == 'Y' ? true : false), '', true, 'To enable and use in support cases.');
    htmlText('v-environment', 'Name', $data['environment'], 30, '', 'Enter a environment name, you can add multiple environments.');
    htmlColorPicker('v-color', 'Color', $data['color'], 'Pay attention to black and white colors, depending on the background used, it can be difficult to see.<br>Please do <u>not use</u> <b>'. (isset($_defaults['defaultcolorname']) ? $_defaults['defaultcolorname'] : 'shades of gray') .'</b>, it is reserved for standalone resources that are updated.');
    htmlCheckbox('v-production', 'Production environment', ($data['production'] == 'Y' ? true : false));
    htmlCheckbox('v-active', 'Active', ($data['active'] == 'Y' ? true : false));
    htmlButtonSubmit('action');
    htmlButtonCancel($collection);
    htmlAccordion('close');
}
htmlAccordion('open','init', 'two', 'Data', (isset($accordion) ? $accordion['data'] : false));
htmlTable('t-environment', array('t_environment'=>'Environment', 't_color'=>'Color', 't_production'=>'Prod', 't_support'=>'Sup', 't_active'=>'Act'), 'id', $result, array('1', 'asc'), ($permission == 'W' ? array('edit', 'delete') : array()), array('t_production', 't_support', 't_active'), array('t_color'));
htmlAccordion('close');
htmlAccordion('end');
htmlForm('end');