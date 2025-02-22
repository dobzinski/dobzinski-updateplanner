# dobzinski-updateplanner

A tool for plan updates to environments and kubernetes clusters, with dark or light themes to use.

![Welcome](images/welcome.gif)


## About

If you want to plan your environment upgrade activities, this tool will help you. Get a dashboard with graphs of completed schedules, a large calendar for scheduling, and use the activity items for more details on the schedules.

Use it to enhance your support and add more value with activity reports for delivery to your Customer.

This project is using:

  * Infrastructure
    - PHP 8
    - MariaDB 10
    - Apache 2.4
    - LaTeX

  * Libraries
    - jQuery
    - Boostrap (5.3)
    - Boostrap datatimepicker
    - Boostrap extended
    - DataTables
    - Chart.js


## Screenshots

Check out some of the features available!
![Dashboard](images/dashboard.jpg)
![Dashboard](images/planning.jpg)
![Dashboard](images/activities.jpg)


## Install

I recommend to use Linux OpenSuse Leap 15.6, and let's follow these steps with root.

1. After installing OpenSuse, update your operating system
```
zypper up && zypper dup
```

2. Install web Server and PHP with modules
```
zypper in apache2 apache2-mod_php8 php8 php8-cli php8-mysql php8-pdo php8-ldap
```

3. Install database
```
zypper in mariadb
```

4. Enable and start the database
```
systemctl enable --now mariadb
```

5. Connect to the database and check if you will be connected and your prompt will change to "MariaDB"
```
mysql -u root
```

6. Get ready to run the commands, but first you will need to change "mypassword" to your password
```
CREATE DATABASE updateplanner CHARACTER SET utf8 COLLATE utf8_general_ci;
CREATE USER 'updateplanner'@'localhost' IDENTIFIED BY 'mypassword';
GRANT ALL PRIVILEGES ON updateplanner.* TO 'updateplanner'@'localhost';
FLUSH PRIVILEGES;
\q
```

7. Use "git clone" for this repository or transfer the content folder "src" to "/srv/www/htdocs/updateplanner/" and change the folder owner to the apache user
```
chown -R wwwrun /srv/www/htdocs/updateplanner
```

8. Run the script to load the database structure, you need to enter the password for the updateplanner user 
```
mysql -u updateplanner -p updateplanner < /srv/www/htdocs/updateplanner/db/mysql.sql
```

9. Edit the configuration file to insert the database credentials or just change the new password
```
vi /srv/www/htdocs/updateplanner/etc/config.php
```
```
define('PRJ_DB_HOST', 'localhost');
define('PRJ_DB_NAME', 'updateplanner');
define('PRJ_DB_USER', 'updateplanner');
define('PRJ_DB_PASS', 'mypassword');
```

10. Open the http port in Firewall
```
firewall-cmd --permanent --add-port=80/tcp
firewall-cmd --reload
```

11. Enable and start the web server
```
systemctl enable --now apache2
```

12. Access your browser and try to open the Update Planner, if the Portal has been loaded, use the username and password
```
http://YOUR-ADDRESS/updateplanner
admin/admin
```

*Use [Rancher](https://www.rancher.com/) to manage your Kubernetes clusters, it's the best!*

*Have fun!*

*:-)*

## Variables

You have a lot of customization in the variables file to change, but don't change everything, some are reserved, take a look!
```
cat /srv/www/htdocs/updateplanner/etc/var.php
```

## Cron jobs

Edit crontab to schedule checking of Git Project repositories and routines to check and create reports
```
vi /etc/crontab
```

Add the below line to schedule check at least once a day on Git Projects (e.g. at 10:00 PM)
```
0 22 * * * wwwrun /usr/bin/php /srv/www/htdocs/updateplanner/scripts/github.php > /dev/null
```

For reports, add the line below to check every minute
```
* * * * * wwwrun /usr/bin/php /srv/www/htdocs/updateplanner/scripts/reports.php > /dev/null
```

## How to use LDAP users for authentication

Edit the configuration file and use some example
```
vi /srv/www/htdocs/updateplanner/etc/config.php
```

Active Directory data example
```
define('PRJ_LDAP_SERVER', 'ldap://mydomain.com');
define('PRJ_LDAP_PORT', '389');
define('PRJ_LDAP_BIND_DN', 'cn=ldap,ou=services,ou=company,dc=mydomain,dc=com');
define('PRJ_LDAP_BIND_PASSWORD', 'mypassword');
define('PRJ_LDAP_BASE_DN', 'ou=users,dc=mydomain,dc=com');
define('PRJ_LDAP_FILTER', 'samaccountname');
define('PRJ_LDAP_GROUP_NAME', 'mysecuritygroup');
define('PRJ_LDAP_GROUPS_BASE_DN', 'ou=groups,ou=company,dc=mydomain,dc=com');
```

OpenLdap data example
```
// here is an OpenLdap service for testing, but maybe this example link can be disabled someday
// https://www.forumsys.com/2022/05/10/online-ldap-test-server/
define('PRJ_LDAP_SERVER', 'ldap://ldap.forumsys.com');
define('PRJ_LDAP_PORT', '389');
define('PRJ_LDAP_BIND_DN', 'cn=read-only-admin,dc=example,dc=com');
define('PRJ_LDAP_BIND_PASSWORD', 'password');
define('PRJ_LDAP_BASE_DN', 'dc=example,dc=com');
define('PRJ_LDAP_FILTER', 'uid');
define('PRJ_LDAP_GROUP_NAME', 'scientists');
define('PRJ_LDAP_GROUPS_BASE_DN', 'dc=example,dc=com');
```

After entering Ldap data, enable Ldap feature in the configuration file
```
vi /srv/www/htdocs/updateplanner/etc/var.php
```
```
$_defaults = array(
[...]
    'ldap'=>true,
```

Go to Update Planner and try adding some users!

## Security

To change the access permission of role groups, edit the values ​​in the permission.json file
```
vi /srv/www/htdocs/updateplanner/data/json/permission.json
```

Some recommendations:

  - Configure a reverse proxy for the Update Planner address, or if you don't have one, remember to enable HTTPS on the server

  - In Apache, remember to disable Index Option for don't list files and enable mod_rewrite to not direct open some folders from web browser, like /scripts, /report and /data

