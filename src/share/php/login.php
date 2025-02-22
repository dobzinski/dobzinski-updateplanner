<?php

alertMessage();
htmlForm('begin', 'login');
htmlAccordion('begin', 'init');
htmlAccordion('open','init', 'one', 'Authentication');
htmlText('val-user', 'User', '', '');
htmlPassword('val-password', 'Password', '', '');
htmlButtonSubmit('login', 'submit');
htmlAccordion('close');
htmlAccordion('end');
htmlForm('end');
htmlJS('$(document).ready(()=>{$(\'#login\').on(\'submit\',()=>{return false;});}); $(\'#login\').keypress((e)=>{if (e.which===13){$(\'#login\').submit();}})');
