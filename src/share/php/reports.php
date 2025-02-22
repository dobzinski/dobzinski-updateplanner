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
        'report'=>'',
        'start'=>'',
        'end'=>'',
    );
}

alertMessage();
htmlForm('begin');
htmlHidden('collection', $collection);
htmlAccordion('begin', 'init');
if ($permission == 'W') {
    htmlAccordion('open','init', 'one', 'Form', (isset($accordion) ? $accordion['form'] : true));
    htmlSelect('v-report', 'Report', $_reports, $data['report'], 'Select the report.');
    htmlTextDateTime('v-start', 'Start', $data['start'], 10, 'Enter the start date.', 'yyyy-mm-dd');
    htmlTextDateTime('v-end', 'End', $data['end'], 10, 'Enter the end date.', 'yyyy-mm-dd');
    htmlButtonSubmit('action');
    htmlButtonCancel($collection);
    htmlAccordion('close');
    htmlForm('end');
    htmlAccordion('open','init', 'two', 'Queue', (isset($accordion) ? $accordion['data'] : false), true);
    htmlBox('begin', 'b-queue', false, '');
    htmlBox('end');
    htmlAccordion('close');
}
htmlAccordion('open','init', 'three', 'Available', (isset($accordion) ? $accordion['data'] : false), true);
htmlBox('begin', 'b-available', false, '');
htmlBox('end');
htmlAccordion('close');
htmlAccordion('end');
htmlForm('begin', 'form-download', '', './report/download/', '_blank');
htmlHidden('resource', 'portal');
htmlHidden('v-file', '');
htmlForm('end');
htmlJs("reloadReports();");