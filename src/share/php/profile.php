<?php

if (!isset($data)) {
    $data = array(
        'login'=>(isset($_SESSION['user']['login']) ? $_SESSION['user']['login'] : ''),
        'fullname'=>(isset($_SESSION['user']['fullname']) ? $_SESSION['user']['fullname'] : ''),
        'role'=>(isset($_SESSION['user']['role']) ? $_roles[$_SESSION['user']['role']] : ''),
        'email'=>(isset($_SESSION['user']['email']) ? $_SESSION['user']['email'] : ''),
        'theme'=>(isset($_SESSION['user']['theme']) ? $_SESSION['user']['theme'] : ''),
        'page'=>(isset($_SESSION['user']['page']) ? $_SESSION['user']['page'] : ''),
    );
}

$pages = array(
    '10'=>'10',
    '25'=>'25',
    '50'=>'50',
    '100'=>'100',
);

alertMessage();
htmlForm('begin');
htmlHidden('collection', $collection);
htmlAccordion('begin', 'init');
htmlAccordion('open','init', 'one', 'Form', false);
htmlText('v-login', 'Login', $data['login'], '', '', 'Your login.', false);
htmlText('v-role', 'Role', $data['role'], '', '', 'Your role.', false);
htmlText('v-fullname', 'Name', $data['fullname'], '', '', 'Your full name.', false);
htmlText('v-email', 'Email <i>(optional)</i>', $data['email'], 100, '', 'Enter your email, it can be used in alerts.', ($_SESSION['user']['ldap'] == 'N' ? true : false));
htmlSelect('v-theme', 'Theme', $_themes, $data['theme'], 'Select your preferred theme.', false);
htmlSelect('v-page', 'Page Items', $pages, $data['page'], 'Select the number of items per page in the table.', false);
htmlButtonSubmit('action');
htmlButtonCancel($collection);
htmlAccordion('close');
htmlAccordion('end');
htmlForm('end');