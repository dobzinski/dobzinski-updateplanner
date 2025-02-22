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
        'case'=>'',
        'product'=>'',
        'priority'=>'',
        'environment'=>'',
        'subject'=>'',
        'description'=>'',
        'conclusion'=>'',
        'report'=>'',
        'open'=>'',
        'close'=>'',
    );
}

$result = sqlSelect(
    "SELECT
        id_product AS id,
        tx_product AS t_product
    FROM
        tb_product
    WHERE
        fl_active = 'Y'
    ORDER BY
        t_product ASC"
);
$products = array();
if (count($result)) {
    foreach($result as $v) {
        $products[$v['id']] = $v['t_product'];
    }
    $result = array();
    unset($result);
}

$result = sqlSelect(
    "SELECT
        id_priority AS id,
        tx_priority AS t_priority
    FROM
        tb_priority
    WHERE
        fl_active = 'Y'
    ORDER BY
        id ASC"
);
$priorities = array();
if (count($result)) {
    foreach($result as $v) {
        $priorities[$v['id']] = $v['t_priority'];
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
        fl_support = 'Y' AND
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

if (isset($_POST['action'])) {
    if (!empty($id) && $_POST['action'] == 'edit') {
        $result = sqlGet($table,
            array(
                't_case'=>'id_case',
                't_product'=>'id_product',
                't_priority'=>'id_priority',
                't_environment'=>'id_environment',
                't_subject'=>'tx_subject',
                't_description'=>'tx_description',
                't_conclusion'=>'tx_conclusion',
                't_report'=>'tx_report',
                't_open'=>'DATE_FORMAT(dt_open, \'%Y-%m-%d %H:%i\')',
                't_close'=>'DATE_FORMAT(dt_close, \'%Y-%m-%d %H:%i\')',
            ),
            array($key=>$id)
        );
        if (count($result)) {
            $data = array(
                'case'=>$result['t_case'],
                'product'=>$result['t_product'],
                'priority'=>$result['t_priority'],
                'environment'=>$result['t_environment'],
                'subject'=>$result['t_subject'],
                'description'=>$result['t_description'],
                'report'=>$result['t_report'],
                'conclusion'=>$result['t_conclusion'],
                'open'=>$result['t_open'],
                'close'=>$result['t_close'],
            );
        } else {
            $_message['type'] = 'danger';
            $_message['message'] = $_alert['notfound'];
        }
    }
}

$result = sqlSelect(
    "SELECT
        c.id_case AS id,
        p.tx_product AS t_product,
        t.tx_priority AS t_priority,
        DATE_FORMAT(c.dt_open, '%Y-%m-%d') AS t_open,
        DATE_FORMAT(c.dt_close, '%Y-%m-%d') AS t_close,
        c.tx_subject AS t_subject,
        c.tx_description AS t_description,
        c.tx_conclusion AS t_conclusion,
        c.tx_report AS t_report,
        e.fl_production AS t_production,
        c.dt_record AS t_record
    FROM
        tb_case c JOIN tb_product p ON (c.id_product = p.id_product)
             JOIN tb_priority t ON (c.id_priority = t.id_priority)
             LEFT JOIN tb_environment e ON (c.id_environment = e.id_environment AND e.fl_support = 'Y')
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
    htmlNumber('v-case', 'Case', $data['case'], 18, '', 'Enter a case number, you can add multiple cases.');
    htmlTextDateTime('v-open', 'Open', $data['open'], 16, 'Enter the date and time the case was opened.');
    htmlSelect('v-product', 'Product', $products, $data['product'], 'Select the product reference.');
    htmlSelect('v-priority', 'Priority', $priorities, $data['priority'], 'Select the priority reference.');
    htmlSelect('v-environment', 'Environment <i>(optional)</i>', $environments, $data['environment'], 'Select the environment reference.');
    htmlText('v-subject', 'Subject', $data['subject'], 100, '', 'Enter a subject about the case that was opened.');
    htmlTextArea('v-description', 'Description', $data['description'], 'Describe the text when the case was opened.');
    htmlTextDateTime('v-close', 'Close <i>(optional)</i>', $data['close'], 16, 'Enter the date and time the case was closed.');
    htmlTextArea('v-conclusion', 'Conclusion <i>(optional)</i>', $data['conclusion'], 'Describe the conclusion when the case was closed.');
    htmlTextArea('v-report', 'Report <i>(optional for new topic)</i>', $data['report'], 'Describe the report will inserting in document.');
    htmlButtonSubmit('action');
    htmlButtonCancel($collection);
    htmlAccordion('close');
}
htmlAccordion('open','init', 'two', 'Data', (isset($accordion) ? $accordion['data'] : false));
htmlTable('t-product', array('id'=>'Id', 't_product'=>'Product', 't_priority'=>'Priority', 't_open'=>'Open', 't_close'=>'Close', 't_subject'=> 'Subject', 't_description'=> 'Description', 't_conclusion'=> 'Conclusion', 't_report'=> 'Report', 't_production'=>'Prod'), 'id', $result, array('1', 'desc'), ($permission == 'W' ? array('edit', 'delete') : array()), array('t_production'), array(), array(), array('t_subject', 't_description', 't_conclusion', 't_report'));
htmlAccordion('close');
htmlAccordion('end');
htmlForm('end');