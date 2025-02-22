<?php

require '../etc/config.php';
require '../etc/session.php';
require '../etc/function.php';
require '../etc/conn_open.php';
require '../etc/var.php';
require '../etc/permission.php';

if (isset($_SESSION['user'])) {
    if ($_SESSION['user']['role'] != 'A') {
        if (isset($_SESSION['user']['role']) && isset($_SESSION['permission']['dashboard'])) {
            if ($_SESSION['permission']['dashboard'] == 'N') {
                exit("<p style=\"margin-top: 20px;\">". $_alert['noaccess'] ."</p>\n");
            }
        } else {
            exit("<p style=\"margin-top: 20px;\">". $_alert['noaccess'] ."</p>\n");
        }
    }
    if (isset($_GET['resource']) && isset($_POST['action'])) {
        if ($_GET['resource'] == 'portal') {
            if ($_POST['action'] == 'view') {
                $graph = '';
                $year = (isset($_POST['year']) ? str_pad(controlInt($_POST['year']), 2, '0', STR_PAD_LEFT) : date('Y'));
                $month = (isset($_POST['month']) ? str_pad(controlInt($_POST['month']), 2, '0', STR_PAD_LEFT) : date('m'));
                $environments = sqlSelect(
                    "SELECT
                        tx_environment AS t_environment,
                        tx_color AS t_color
                    FROM
                        tb_environment
                    WHERE
                        fl_active = ?
                    ORDER BY
                        t_environment ASC",
                    array('Y')
                );
                $result = sqlSelect(
                    "SELECT
                        COUNT(c.id_calendar) AS t_total,
                        DATE_FORMAT(c.dt_schedule, '%d') AS t_day,
                        (CASE
                            WHEN e.tx_environment IS NOT NULL THEN e.tx_environment
                            WHEN e2.tx_environment IS NOT NULL THEN e2.tx_environment
                            ELSE '". $_defaults['defaultcolortitle'] ."'
                        END) AS t_environment
                    FROM
                        tb_calendar c LEFT JOIN tb_cluster t ON (t.id_cluster = c.id_cluster)
                            LEFT JOIN tb_calendar_item i ON (i.id_calendar = c.id_calendar 
                                AND DATE_FORMAT(i.dt_end, '%Y-%m-%d %H:%i') = (SELECT MAX(DATE_FORMAT(i2.dt_end, '%Y-%m-%d %H:%i')) FROM tb_calendar_item i2 WHERE i2.id_calendar = i.id_calendar))
                            LEFT JOIN tb_environment e ON (e.id_environment = t.id_environment)
                            LEFT JOIN tb_environment e2 ON (e2.id_environment = c.id_environment)
                    WHERE
                        c.fl_complete = ? AND
                        DATE_FORMAT(c.dt_schedule, '%Y-%m-%d %H:%i:%s') BETWEEN ? AND ?
                    GROUP BY
                        t_day, t_environment
                    ORDER BY
                        t_day ASC, t_environment ASC",
                    array('Y', $year ."-". $month ."-01 00:00:00", $year ."-". $month ."-31 23:59:59")
                );
                $data = array();
                $enabled = array();
                $labels = '[';
                $strtotime = strtotime($year .'-'. $month .'-01');
                $monthtext = date('F', $strtotime);
                $lastday = date('t', $strtotime);
                for($i=1; $i<=$lastday; $i++) {
                    $day = str_pad($i, 2, "0", STR_PAD_LEFT);
                    $days[] = $day;
                    $labels .= "'". $day .($i<$lastday ? "'," : "'");
                }
                $labels .= "],";
                if (isset($_defaults['defaultcolortitle']) && isset($_defaults['defaultcolorcode'])) {
                    array_push($environments, array('t_environment'=>$_defaults['defaultcolortitle'], 't_color'=>$_defaults['defaultcolorcode']));
                }
                if (count($result)) {
                    $data = array();
                    foreach($result as $v) {
                        if ($v['t_total'] > 0) {
                            if (!in_array($v['t_environment'], $enabled)) {
                                $enabled[] = $v['t_environment'];
                            }
                        }
                        $data[$v['t_environment']][$v['t_day']] = $v['t_total'];
                    }
                }
                if (count($environments)) {
                    $graph = "\n"; 
                    $graph .= "var ctx = $('#chart');\n"; 
                    $graph .= "var myChart = new Chart(ctx, { ";
                    $graph .= "type: 'bar', ";
                    $graph .= "data: { ";
                    $graph .= "labels: ". $labels;
                    $graph .= "datasets: [ ";
                    if (count($enabled)) {
                        foreach($environments as $e) {
                            if (in_array($e['t_environment'], $enabled)) {
                                $values = "";
                                for($i=0; $i<count($days); $i++) {
                                    $day = $days[$i];
                                    $values .= (isset($data[$e['t_environment']][$day]) ? $data[$e['t_environment']][$day] : "0") . ($i<(count($days)-1) ? "," : "");
                                }
                                $graph .= "{";
                                $graph .= "data: [". $values ."], ";
                                $graph .= "lineTension: 0, ";
                                $graph .= "label: '". $e['t_environment'] ."', ";
                                $graph .= "backgroundColor: '#". $e['t_color'] ."', ";
                                $graph .= "borderColor: '#". $e['t_color'] ."', ";
                                $graph .= "borderWidth: 4, ";
                                $graph .= "pointBackgroundColor: '#". $e['t_color'] ."'";
                                $graph .= "},";
                            }
                        }
                    } else {
                        $values = "";
                        $graph .= "{";
                        for($i=0; $i<count($days); $i++) {
                            $day = $days[$i];
                            $values .= "0" . ($i<(count($days)-1) ? "," : "");
                        }
                        $graph .= "data: [". $values ."], ";
                        $graph .= "borderWidth: 4, ";
                        $graph .= "},";
                    }
                    $graph .= "]},";
                    $graph .= "options:{responsive:true,scales:{y:{min:0,ticks:{stepSize:1}}},plugins:{title:{display:true,text:'Completed Planning by Environments on ". $monthtext ." of ". $year ."'},legend:{display:". (count($enabled) ? "true" : "false") .",position:'bottom'},tooltip:{boxPadding:3}}}";
                    $graph .= "});\n";
                }
                if (($month - 1) > 0) {
                    $prevmonth = ($month - 1);
                    $prevyear = $year;
                } else {
                    $prevmonth = 12;
                    $prevyear = ($year - 1);
                }
                if (($month + 1) < 13) {
                    $nextmonth = ($month + 1);
                    $nextyear = $year;
                } else {
                    $nextmonth = 1;
                    $nextyear = ($year + 1);
                }
                //echo "<div class=\"border p-3\">";
                echo "<div class=\"row mt-3 mb-3\">";
                echo "<div class=\"col text-start text-nowrap\">";
                echo "<button type=\"button\" class=\"btn btn-primary btn-sm\" onclick=\"return openGraph('". ($year - 1) ."', '". $month ."');\"><svg class=\"bi\"><use xlink:href=\"#bi-chevron-double-left\"/></svg></button>";
                echo "&nbsp;<button type=\"button\" class=\"btn btn-primary btn-sm\" onclick=\"return openGraph('". $prevyear ."', '". $prevmonth ."');\"><svg class=\"bi\"><use xlink:href=\"#bi-chevron-left\"/></svg></button>";
                echo "&nbsp;<button type=\"button\" class=\"btn btn-primary btn-sm\" onclick=\"return openGraph('". $nextyear ."', '". $nextmonth ."');\"><svg class=\"bi\"><use xlink:href=\"#bi-chevron-right\"/></svg></button>";
                echo "&nbsp;<button type=\"button\" class=\"btn btn-primary btn-sm\" onclick=\"return openGraph('". ($year + 1) ."', '". $month ."');\"><svg class=\"bi\"><use xlink:href=\"#bi-chevron-double-right\"/></svg></button>";
                echo "</div>";
                echo "<div class=\"col text-end\"><div class=\"input-group text-nowrap\">";
                echo "<input class=\"form-control form-control-sm\" list=\"datalist-month\" id=\"graph-month\" value=\"". str_pad($month , 2, "0", STR_PAD_LEFT) ."\" maxlength=\"2\" placeholder=\"Month\"><datalist id=\"datalist-month\">";
                for ($i=1; $i<=12; $i++) {
                    echo "<option value=\"". str_pad($i, 2, "0", STR_PAD_LEFT) ."\">";
                }
                echo "</datalist>";
                echo "<input class=\"form-control form-control-sm\" list=\"datalist-year\" id=\"graph-year\" value=\"". $year ."\" maxlength=\"4\" placeholder=\"Year\"><datalist id=\"datalist-year\">";
                for ($i=$_defaults['startyear']; $i<=(date('Y')); $i++) {
                    echo "<option value=\"". $i ."\">";
                }
                echo "</datalist>";
                echo "<button type=\"button\" class=\"btn btn-primary btn-sm\" onclick=\"return openGraph($('#graph-year').val(), $('#graph-month').val());\">Go!</button>";
                echo "</div></div>";
                echo "</div>\n";
                echo "<canvas class=\"my-4 w-100\" id=\"chart\" height=\"400\"></canvas></div>\n";
                //echo "</div>\n";
                htmlJs($graph);
            }
        }
    }
}

require '../etc/conn_close.php';