
<?php

$_version = "v1.1.1";

$_defaults = array(
    'checklatest'=>true,
    'startweek'=>'Sunday',
    'weekends'=>true,
    'defaultcolorcode'=>'999999',
    'defaultcolorname'=>'shades of gray',
    'defaultcolortitle'=>'No Environment',
    'startyear'=>2024,
    'abstractslastmonth'=>3,
    'ldap'=>false,
);

$_menuitems = array(
    'home'=>'Home',
    'dashboard'=>'Dashboard',
    'planning'=>'Planning',
    'activities'=>'Activities',
    'support'=>'Support',
    'products'=>'Products',
    'cases'=>'Cases',
    'recurrents'=>'Recurrents',
    'abstracts'=>'Abstracts',
    'reports'=>'Reports',
    'settings'=>'Settings',
    'environments'=>'Environments',
    'platforms'=>'Platforms',
    'kubernetes'=>'Kubernetes',
    'clouds'=>'Clouds',
    'clusters'=>'Clusters',
    'resources'=>'Resources',
    'versions'=>'Versions',
    'users'=>'Users',
    'account'=>'Account',
    'profile'=>'Profile',
    'password'=>'Password',
    'logout'=>'Logout',
);

$_status = array(
    'N'=>'New',
    'W'=>'Working',
    'S'=>'Suspended',
    'C'=>'Completed',
    'R'=>'Rollback',
    'D'=>'Cancelled',
);

$_roles = array(
    'A'=>'Administrator',
    'M'=>'Manager',
    'O'=>'Operator',
    'S'=>'Support',
    'G'=>'Guest',
);

$_alert = array(
    'noaccess'=>'Sorry, you don\'t have access!',
    'inserted'=>'Record inserted!',
    'updated'=>'Record updated!',
    'deleted'=>'Record deleted!',
    'completed'=>'Calendar completed!',
    'scheduled'=>'Calendar scheduled!',
    'required'=>'Please enter the values!',
    'notfound'=>'Record not found!',
    'noactivity'=>'No activity!',
    'broken_activities'=>'Relationship is broken! Check depends in Planning.',
    'updatedprofile'=>'User profile updated!',
    'clicktoreload'=>'<br>Please <b><a href="#" onclick="return reloadPortal();">click here</a></b> to reload and apply the theme.',
    'completebefore'=>'You need to enter a "Completed" date for this record!',
    'authsuccess'=>'Success! Please wait...',
    'loginpasswordrequired'=>'Enter your Login and Password!',
    'loginpasswordinvalid'=>'Invalid login or password!',
    'loginblocked'=>'User blocked!',
    'passwordinvalid'=>'Invalid password!',
    'passwordconfirmrequired'=>'Enter the password and confirm!',
    'passworddoesnotmatch'=>'Password does not match confirmation!',
    'passworddoesnotcharsrequired'=>'The password did not pass the criteria, check the rules!',
    'userpassworddoesnotmatch'=>'User password does not match!',
    'updatedpassword'=>'Updated password!',
    'invalidemail'=>'Invalid email!',
    'invalidhttp'=>'Invalid URL address!',
    'reportexists'=>'The report already exists in the queue!',
    'reportwasadd'=>'The report has been added to the queue!',
    'reportnotexist'=>'The report does not exist!',
    'reportdeletederror'=>'Error deleting report!',
    'reportdeletedsuccess'=>'The report was deleted!',
    'usernotfound'=>'User not found!',
    'datetimeinvalid'=>'Error checking date and time',
    'dateinvalid'=>'Error checking the date',
    'timeinvalid'=>'Error checking the time',
    'datesbetweeninvalid'=>'The end date cannot be earlier than the start date!',
    'lastvalidstatusrequired'=>'The last activity has no completion status!',
    'lowerdatelastactivity'=>'Trying to insert a lower date of last activity!',
);

$_permission = array(
    'home'=>array(
        'manager'=>true,
        'operator'=>true,
        'support'=>true,
        'guest'=>true,
    ),
    'settings'=>array(
        'manager'=>true,
        'operator'=>true,
        'support'=>true,
        'guest'=>false,
    ),
    'support'=>array(
        'manager'=>true,
        'operator'=>true,
        'support'=>true,
        'guest'=>false,
    ),
);

$_themes = array(
    'D'=>'Dark',
    'L'=>'Light',
);

$_report = array(
    'cover'=>'/var/www/html/support/report/images/cover.png',
    'logo'=>'/var/www/html/support/report/images/logo.png',
    'betweendates'=>'to',
    'dateformat'=>'Y-m-d',
    'textcover'=>'Business Paper',
    'brandheader'=>'My Company',
    'textheader'=>'mycompany.com',
    'linkheader'=>'https://www.mycompany.com',
    'textfooter'=>'Copyright ©Company',
    'title'=>'Activity \\\\Report',
    'summary'=>'Summary',
    'spaceaftersummary'=>'1cm',
    'textactivity'=>'Activity Status',
    'textactivityenvironment'=>'Environment',
    'textactivitydescription'=>'Description',
    'textactivitydate'=>'Date',
    'textactivitystatus'=>'Status',
    'textcase'=>'Case',
    'textcases'=>'Cases',
    'textcasesnumber'=>'Number',
    'textcasespriority'=>'Priority',
    'textcasesopen'=>'Open',
    'textcasesclose'=>'Closed',
    'texttimeline'=>'Timeline',
    'textprodshortname'=>'Prod',
    'textboolyes'=>'Yes',
    'textboolno'=>'No',
    'rgbheader'=>'0.6, 0.6, 0.6',
    'rgbtitle'=>'0.051, 0.431, 0.992',
);

$_reportstatus = array( // check values in $_status
    'default'=>'On Track', // patch when have an schedule without activity item
    'N'=>'On Track', // patch for new activities
    'W'=>'On Track',
    'S'=>'Suspended',
    'C'=>'Completed',
    'R'=>'Rollback',
    'D'=>'Cancelled',
);

$_reports = array(
    'activities'=>'Activities',
);

$_statusconclusion = array(
    'C', 'R', 'D',
);

$_regex = array(
    'checkdatetime'=>'^\d{4}-\d{2}-\d{2} (?:[01]\d|2[0-3]):[0-5]\d$',
    'checkdate'=>'^\d{4}-\d{2}-\d{2}$',
    'checktime'=>'^(?:[01]\d|2[0-3]):[0-5]\d$',
    'checkhttp'=>'^https?:\/\/[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}(:[0-9]+)?(\/.*)?$',
    'checkemail'=>'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$',
    'checkpassword'=>'^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$',
    'controlpassword'=>'[^a-zA-Z0-9\#\?\!\@\$\%\^\&\*\-]',
    'controllogin'=>'[^a-zA-Z0-9\_\-\+\.\@\$]',
);
