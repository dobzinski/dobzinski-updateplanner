<?php

if (!isset($_SESSION['user']['role'])) {
    exit("<p style=\"margin-top: 20px;\">". $_alert['noaccess'] ."</p>\n");
}

$delayed = sqlSelect(
  "SELECT
      COUNT(c.id_calendar) AS total
  FROM
      tb_calendar c
  WHERE
      c.fl_complete = ? AND
      DATE_FORMAT(c.dt_schedule, '%Y%m%d%H%i') < ?",
  array('N', date('YmdHi'))
);

$scheduled = sqlSelect(
  "SELECT
      COUNT(c.id_calendar) AS total
  FROM
      tb_calendar c
  WHERE
      c.fl_complete = ? AND
      DATE_FORMAT(c.dt_schedule, '%Y%m%d%H%i') >= ?",
  array('N', date('YmdHi'))
);

$activities = sqlSelect(
  "SELECT
      COUNT(i.id_calendar_item) AS total
  FROM
      tb_calendar_item i JOIN tb_calendar c ON (i.id_calendar = c.id_calendar)
  WHERE
      c.fl_complete = ?",
  array('N')
);

$updates = sqlSelect(
    "SELECT
        DATE_FORMAT(v.dt_record, '%Y-%m-%d') AS t_record,
        r.tx_resource AS t_resource,
        v.tx_version AS t_version,
        v.fl_script as t_script,
        v.id_resource_version AS t_idversion,
        r.id_resource AS id
    FROM
        tb_resource r JOIN tb_resource_version v ON (v.id_resource = r.id_resource 
                AND v.id_resource_version = (SELECT MAX(v2.id_resource_version) FROM tb_resource_version v2 WHERE v2.id_resource = r.id_resource))
            LEFT JOIN tb_calendar c ON (v.id_resource_version = c.id_resource_version)
    WHERE
        v.id_resource_version NOT IN (SELECT DISTINCT(c3.id_resource_version) FROM tb_calendar c3 JOIN tb_resource_version v3 ON (v3.id_resource_version = c3.id_resource_version) WHERE v3.id_resource = r.id_resource) AND
        v.fl_active = ? AND
        r.fl_active = ?
    ORDER BY
        t_record DESC, t_resource ASC, t_version DESC",
    array('Y', 'Y')
);

htmlLine('begin');
htmlColumn('begin', 3);
htmlCardNumber('Delayed', $delayed[0]['total'], ($delayed[0]['total'] > 0 ? 'danger' : 'secondary'));
htmlColumn('end');
htmlColumn('begin', 3);
htmlCardNumber('Scheduled', $scheduled[0]['total'], ($scheduled[0]['total'] > 0 ? 'info' : 'secondary'));
htmlColumn('end');
htmlColumn('begin', 3);
htmlCardNumber('Activities', $activities[0]['total'], ($activities[0]['total'] > 0 ? 'info' : 'secondary'));
htmlColumn('end');
htmlColumn('begin', 3);
htmlCardNumber('Updates', count($updates), (count($updates) > 0 ? 'warning' : 'secondary'));
htmlColumn('end');
htmlLine('end');
htmlAccordion('begin', 'init');
htmlAccordion('open','init', 'one', 'Graph', false);
htmlChartArea(date('Y'), date('m'));
htmlAccordion('close');
htmlAccordion('open','init', 'two', 'Latest updates available', (count($updates) > 0 ? false : true));
htmlTable('t-updates', array('t_record'=>'Date', 't_resource'=>'Resources', 't_version'=>'Versions', 't_script'=>'Bot'), 'id', $updates, array(), array(), array('t_script'));
htmlAccordion('close');
htmlAccordion('end');