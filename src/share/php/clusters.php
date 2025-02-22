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
        'platform'=>'',
        'kubernetes'=>'',
        'cloud'=>'',
        'cluster'=>'',
        'node'=>'',
        'downstream'=>'Y',
        'active'=>'Y',
    );
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
        id_platform AS id,
        tx_platform AS t_platform
    FROM
        tb_platform
    WHERE
        fl_active = 'Y'
    ORDER BY
        t_platform ASC"
);
$platforms = array();
if (count($result)) {
    foreach($result as $v) {
        $platforms[$v['id']] = $v['t_platform'];
    }
    $result = array();
    unset($result);
}

$result = sqlSelect(
    "SELECT
        id_k8s AS id,
        tx_k8s AS t_kubernetes
    FROM
        tb_k8s
    WHERE
        fl_active = 'Y'
    ORDER BY
        t_kubernetes ASC"
);
$kubernetes = array();
if (count($result)) {
    foreach($result as $v) {
        $kubernetes[$v['id']] = $v['t_kubernetes'];
    }
    $result = array();
    unset($result);
}

$result = sqlSelect(
    "SELECT
        id_cloud AS id,
        tx_cloud AS t_cloud
    FROM
        tb_cloud
    WHERE
        fl_active = 'Y'
    ORDER BY
        t_cloud ASC"
);
$clouds = array();
if (count($result)) {
    foreach($result as $v) {
        $clouds[$v['id']] = $v['t_cloud'];
    }
    $result = array();
    unset($result);
}

if (isset($_POST['action'])) {
    if (!empty($id) && $_POST['action'] == 'edit') {
        $result = sqlGet($table,
            array(
                't_environment'=>'id_environment',
                't_platform'=>'id_platform',
                't_kubernetes'=>'id_k8s',
                't_cloud'=>'id_cloud',
                't_cluster'=>'tx_cluster',
                't_node'=>'nu_node',
                't_downstream'=>'fl_downstream',
                't_active'=>'fl_active',
            ),
            array($key=>$id)
        );
        if (count($result)) {
            $data = array(
                'environment'=>$result['t_environment'],
                'platform'=>$result['t_platform'],
                'kubernetes'=>$result['t_kubernetes'],
                'cloud'=>$result['t_cloud'],
                'cluster'=>$result['t_cluster'],
                'node'=>$result['t_node'],
                'downstream'=>$result['t_downstream'],
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
        t.id_cluster AS id,
        t.tx_cluster AS t_title,
        t.nu_node AS t_node,
        (CASE
            WHEN t.fl_downstream = 'Y' THEN 'Down'
            ELSE 'Up'
        END) AS t_cluster,
        e.tx_environment AS t_environment,
        p.tx_platform AS t_platform,
        k.tx_k8s AS t_kubernetes,
        c.tx_cloud AS t_cloud,
        t.fl_active AS t_active,
        t.dt_record AS t_record
    FROM
        tb_cluster t JOIN tb_environment e ON (t.id_environment = e.id_environment)
             JOIN tb_platform p ON (t.id_platform = p.id_platform)
             JOIN tb_k8s k ON (t.id_k8s = k.id_k8s)
             LEFT JOIN tb_cloud c ON (t.id_cloud = c.id_cloud)
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
    htmlCheckbox('v-downstream', 'Downstream', ($data['downstream'] == 'Y' ? true : false));
    htmlText('v-cluster', 'Name', $data['cluster'], 40, '', 'Enter a cluster name, you can add multiple clusters.');
    htmlNumber('v-node', 'Nodes', $data['node'], 5, '', 'Enter the number of cluster nodes.', 1);
    htmlSelect('v-environment', 'Environment', $environments, $data['environment'], 'Select the environment reference.');
    htmlSelect('v-platform', 'Platform', $platforms, $data['platform'], 'Select the platform reference.');
    htmlSelect('v-kubernetes', 'Kubernetes', $kubernetes, $data['kubernetes'], 'Select the kubernetes reference.');
    htmlSelect('v-cloud', 'Cloud <i>(optional)</i>', $clouds, $data['cloud'], 'Select the cloud reference.');
    htmlCheckbox('v-active', 'Active', ($data['active'] == 'Y' ? true : false));
    htmlButtonSubmit('action');
    htmlButtonCancel($collection);
    htmlAccordion('close');
}
htmlAccordion('open','init', 'two', 'Data', (isset($accordion) ? $accordion['data'] : false));
htmlTable('t-cluster', array('t_title'=>'Name', 't_node'=>'Nodes', 't_cluster'=>'CL', 't_environment'=>'Environment', 't_platform'=>'Platform', 't_kubernetes'=>'K8s', 't_cloud'=>'Cloud', 't_active'=>'Act'), 'id', $result, array('1', 'asc'), ($permission == 'W' ? array('edit', 'delete') : array()), array('t_active'));
htmlAccordion('close');
htmlAccordion('end');
htmlForm('end');