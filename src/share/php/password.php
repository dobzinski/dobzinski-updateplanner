<?php

if ($_SESSION['user']['ldap'] == 'Y') {
    exit("<p style=\"margin-top: 20px;\">". $_alert['noaccess'] ."</p>\n");
}

if (!isset($data)) {
    $data = array(
        'login'=>(isset($_SESSION['user']['login']) ? $_SESSION['user']['login'] : ''),
    );
}

alertMessage();
htmlForm('begin');
htmlHidden('collection', $collection);
htmlAccordion('begin', 'init');
htmlAccordion('open','init', 'one', 'Form', false);
htmlText('v-login', 'Login', $data['login'], '', '', 'Your login.', false);
if ($_SESSION['user']['ldap'] == 'N') {
    htmlPassword('v-current', 'Password', 'Enter your current password.');
    htmlPassword('v-password', 'New Password', $tips['password'], 'controlValidPassword();');
    htmlPassword('v-confirm', 'Confirm Password', 'Repeat password to confirm.', 'controlConfirmPassword();');
    htmlButtonSubmit('action');
    htmlButtonCancel($collection);
} else {
    
}
htmlAccordion('close');
htmlAccordion('end');
htmlForm('end');