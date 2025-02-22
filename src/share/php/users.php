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
        'role'=>'',
        'login'=>'',
        'fullname'=>'',
        'email'=>'',
        'ldap'=>(!$_defaults['ldap'] ? 'N' : 'Y'),
        'active'=>'Y',
        'updatepassword'=>'N',
    );
}

$ldapcontrol = (!empty($id) ? false : $_defaults['ldap']);

if (isset($_POST['action'])) {
    if (!empty($id) && $_POST['action'] == 'edit') {
        $result = sqlGet($table,
            array(
                't_role'=>'tp_role',
                't_login'=>'tx_login',
                't_fullname'=>'tx_fullname',
                't_email'=>'tx_email',
                't_ldap'=>'fl_ldap',
                't_active'=>'fl_active',
            ),
            array($key=>$id)
        );
        if (count($result)) {
            $data = array(
                'role'=>$result['t_role'],
                'login'=>$result['t_login'],
                'fullname'=>$result['t_fullname'],
                'email'=>$result['t_email'],
                'ldap'=>$result['t_ldap'],
                'active'=>$result['t_active'],
                'enableldap'=>($result['t_ldap'] == 'N' ? 'N' : 'Y'),
                'updatepassword'=>'N',
            );
        } else {
            $_message['type'] = 'danger';
            $_message['message'] = $_alert['notfound'];
        }
    }
}

$result = sqlSelect(
    "SELECT
        u.id_user AS id,
        u.tx_fullname AS t_fullname,
        u.tx_login AS t_login,
        (CASE
            WHEN u.tp_role = 'A' THEN '". $_roles['A'] ."'
            WHEN u.tp_role = 'M' THEN '". $_roles['M'] ."'
            WHEN u.tp_role = 'O' THEN '". $_roles['O'] ."'
            WHEN u.tp_role = 'S' THEN '". $_roles['S'] ."'
            WHEN u.tp_role = 'G' THEN '". $_roles['G'] ."'
            ELSE 'None'
        END) AS t_role,
        DATE_FORMAT(u.dt_record, '%Y-%m-%d') AS t_record,
        u.fl_ldap AS t_ldap,
        u.fl_active AS t_active,
        u.tx_email AS t_email
    FROM
        tb_user u
    ORDER BY
        t_fullname ASC"
);

alertMessage();
htmlForm('begin');
htmlHidden('collection', $collection);
htmlHidden('v-id', $id);
htmlAccordion('begin', 'init');
if ($permission == 'W') {
    htmlAccordion('open','init', 'one', 'Form', (isset($accordion) ? $accordion['form'] : true));
    htmlText('v-login', 'Login', $data['login'], 20, '', 'Enter a login for the user.', (empty($id) ? true : false));
    htmlSelect('v-role', 'Role', $_roles, $data['role'], 'Select the user role.');
    htmlCheckbox('v-ldap', 'Ldap Authentication'. (!$ldapcontrol ? ' <i>(disabled)</i>' : ''), ($data['ldap'] == 'Y' ? true : false), 'controlLdap();', (!$ldapcontrol ? false : true));
    htmlBox('begin','v-box-internaluser');
    htmlText('v-fullname', 'Full Name', $data['fullname'], 100, '', 'Enter a full name for the user.');
    htmlText('v-email', 'Email <i>(optional)</i>', $data['email'], 100, '', 'Enter a email for the user.');
    if (!empty($id)) {
        htmlCheckbox('v-updatepassword', 'Update Password', ($data['updatepassword'] == 'Y' ? true : false), 'controlPassword();');
        htmlJs('controlPassword();');
    }
    htmlBox('begin','v-box-password');
    htmlPassword('v-password', 'Password', $tips['password'], 'controlValidPassword();');
    htmlPassword('v-confirm', 'Confirm Password', 'Repeat password to confirm.', 'controlConfirmPassword();');
    htmlBox('end');
    htmlBox('end');
    htmlCheckbox('v-active', 'Active', ($data['active'] == 'Y' ? true : false));
    htmlButtonSubmit('action');
    htmlButtonCancel($collection);
    htmlAccordion('close');
}
htmlAccordion('open','init', 'two', 'Data', (isset($accordion) ? $accordion['data'] : false));
htmlTable('t-user', array('t_fullname'=>'Name', 't_login'=>'Login', 't_role'=>'Role', 't_record'=>'Join', 't_ldap'=>'Ldap', 't_active'=>'Act', 't_email'=>'Email'), 'id', $result, array('1', 'asc'), ($permission == 'W' ? array('edit', 'delete') : array()), array('t_ldap', 't_active'), array(), array('t_email'));
htmlAccordion('close');
htmlAccordion('end');
htmlForm('end');
if ($permission == 'W') {
    htmlJs("controlLdap();");
}