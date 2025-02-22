<?php

function controlInt($numeric) {
    return preg_replace('/\D/', '', $numeric);
}

function controlSpecialChars($text, $pattern='[^a-zA-Z0-9]', $replacement='') {
    return preg_replace('/'. $pattern .'/', $replacement, $text);
}

function checkSpecialChars($text, $pattern='^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9]).{4,}$') {
    if (preg_match('/'. $pattern .'/', $text)) {
        return true;
    } else {
        return false;
    }
}

function checkDateTime($date, $format) {
    global $_regex;
    if (!empty($date)) {
        switch ($format) {
            case 'Y-m-d H:i':
                $check = 'checkdatetime';
            break;
            case 'Y-m-d':
                $check = 'checkdate';
            break;
            case 'H:i':
                $check = 'checktime';
            break;
        }
        if (preg_match('/'. $_regex[$check] .'/', $date)) {
            $d = DateTime::createFromFormat($format, $date);
            if ($d->format($format) == $date) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    return false;
}

function controlDateTimeToInt($datetime) {
    $datetime = str_replace('-', '', $datetime);
    $datetime = str_replace(':', '', $datetime);
    $datetime = str_replace(' ', '', $datetime);
    return (int)$datetime;
}

function calcHours($datestart, $dateend){
	$start = strtotime($datestart);
	$end = strtotime($dateend);
	$diff = round(abs($end - $start)/3600, 2);
	return $diff;
}

function getReports($folder) {
    global $files;
    if (!empty($folder)) {
        if (is_dir($folder)) {
            $arr = scandir($folder, 1);
            foreach($arr as $v) {
                if (!empty($v)) {
                    if ($v != '.' && $v != '..') {
                        if (is_dir($folder ."/". $v)) {
                            getReports($folder ."/". $v);
                        } else if (is_file($folder ."/". $v)) {
                            if (strpos($v, '.')) {
                                $file = explode('.', $v);
                                $split = explode('_', $file[0]);
                                $report = array(
                                    'id'=>$file[0],
                                    'date'=>$split[0],
                                    'report'=>ucfirst($split[1]),
                                    'start'=>(isset($split[2]) ? $split[2] : ''),
                                    'end'=>(isset($split[3]) ? $split[3] : ''),
                                    'extension'=>$file[1],
                                    'folder'=>$folder,
                                );
                                $files[] = $report;
                            }
                        }
                    }
                }
            }
        }
    }
    return;
}

function escapeLatex($text) {
    $replacements = [
        '\\' => '\textbackslash ',
        '^' => '\textasciicircum ',
        '~' => '\textasciitilde ',
        '&' => '\&',
        '%' => '\%',
        '$' => '\$',
        '#' => '\#',
        '_' => '\_',
        '{' => '\{',
        '}' => '\}',
    ];
    return str_replace(array_keys($replacements), array_values($replacements), $text);
}

function getLatestVersion($type='github', $url) {
    $version = '';
    if (!empty($url)) {
        switch ($type) {
            case 'github':
                $result = file_get_contents($url .'/releases/latest');
                $rows = explode("\n", $result);
                if (count($rows)) {
                    foreach($rows as $r) {
                        if (!strpos($r, '<title>Releases ·') && strpos($r, '<title>') !== 0) {
                            if (strpos($r, '<title>') || strpos($r, '<title>') === 0) {
                                $title = preg_replace('/<[^<]*>Release/', '', $r);
                                $title = trim(preg_replace('/·.*<\/title>$/', '', $title));
                            }
                        }
                        if (strpos($r, '<h1 data-view-component="true"') || strpos($r, '<h1 data-view-component="true"') === 0) {
                            $version = trim(preg_replace('/<h1[^<]*>/', '', $r));
                            $version = preg_replace('/<\/h1>$/', '', $version);
                            if (!empty($title)) {
                                if ($title == $version) {
                                    return $version;
                                } else if (!empty($version)) {
                                    return $version;
                                } else if (!empty($title)) {
                                    return $title;
                                }
                            } else if (!empty($version)) {
                                return $version;
                            }
                        }
                        if (strpos($r, '<h2 data-view-component="true"') || strpos($r, '<h2 data-view-component="true"') === 0) {
                            $norelease = trim(preg_replace('/<h2[^<]*>/', '', $r));
                            $norelease = preg_replace('/<\/h2>$/', '', $norelease);
                            if ($norelease == 'There aren’t any releases here') {
                                return;
                            }
                        }
                    }
                    if (isset($title)) {
                        if (!empty($title)) {
                            return $title;
                        }
                    }
                }
            break;
        }
    }
    return;
}

function createGlobalSettings($data) {
    global $_jsonglobal;
    if (!empty($data)) {
        $json = '';
        $total = count($data);
        if ($total > 0) {
            $json = '{"settings":{';
            $i = 1;
            foreach($data as $k=>$v) {
                $json .= '"'. $k .'":'. (is_int($v) ? $v : '"'. $v .'"') . ($total > $i ? "," : "");
                $i++;
            }
            $json .= '}}';
        }
        if (!empty($json)) {
            file_put_contents($_jsonglobal, $json, LOCK_EX);
        }
    }
    return;
}

function sqlSelect($query, $values=array()) {
    global $_conn;
    $sth = $_conn->prepare($query);
    if (count($values)) {
        $sth->execute($values);
    } else {
        $sth->execute();
    }
    $result = $sth->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

function sqlGet($table, $cols, $values) {
    global $_conn, $_message;
    $query = "SELECT ";
    if (count($cols)) {
        $i = 1;
        foreach($cols as $x=>$v) {
            $query .= $v ." AS ". $x .(count($cols) > $i ? ", " : "");
            $i++;
        }
    }
    $query .= " FROM ". $table;
    $query .= " WHERE ";
    if (count($values)) {
        $i = 1;
        foreach($values as $x=>$v) {
            $query .= $x . " = ? " .(count($values) > $i ? "AND " : "");
            $data[] = $v;
            $i++;
        }
    }
    //print $query; exit;
    $sth = $_conn->prepare($query);
    if (count($data)) {
        $sth->execute($data);
    }
    $result = $sth->fetch(PDO::FETCH_ASSOC);
    return $result;
}

function sqlInsert($table, $values) {
    global $_conn;
    $data = array();
    $query = "INSERT INTO ". $table;
    if (count($values)) {
        $query .= " (";
        $prepare = "";
        $i = 1;
        foreach($values as $k=>$v) {
            $query .= $k .(count($values) > $i ? ", " : "");
            $prepare .= "?" .(count($values) > $i ? ", " : "");
            $data[] = $v;
            $i++;
        }
        $query .= " ) VALUES ( ";
        $query .= $prepare;
        $query .= " )";
    }
    //var_dump($data);
    //print $query; exit;
    $_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sth = $_conn->prepare($query);
    if (count($data)) {
        $sth->execute($data);
    }
    $info = $sth->errorInfo();
    $result = (is_array($info) ? (isset($info[2]) ? $info[2] : NULL) : NULL);
    return $result;
}

function sqlUpdate($table, $values, $id) {
    global $_conn;
    $key = array_pop($values);
    $data = array();
    $query = "UPDATE ". $table ." SET ";
    if (count($values)) {
        $i = 1;
        foreach($values as $k=>$v) {
            $query .= $k .(count($values) > $i ? " = ?, " : " = ? ");
            $data[] = $v;
            $i++;
        }
        array_push($data, $key);
        $query .= " WHERE ". $id ." = ? ";
    }
    //var_dump($data);
    //print $query; exit;
    $_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sth = $_conn->prepare($query);
    if (count($data)) {
        $sth->execute($data);
    }
    $info = $sth->errorInfo();
    $result = (is_array($info) ? (isset($info[2]) ? $info[2] : NULL) : NULL);
    return $result;
}

function sqlDelete($table, $values) {
    global $_conn;
    $data = array();
    $query = "DELETE FROM ". $table ." WHERE ";
    if (count($values)) {
        $i = 1;
        foreach($values as $k=>$v) {
            $query .= $k .(count($values) > $i ? " = ?, " : " = ? ");
            $data[] = $v;
            $i++;
        }
    }
    $_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sth = $_conn->prepare($query);
    if (count($data)) {
        $sth->execute($data);
    }
    $info = $sth->errorInfo();
    $result = (is_array($info) ? (isset($info[2]) ? $info[2] : NULL) : NULL);
    return $result;
}

function alertMessage() {
    global $_message;
    if (isset($_message['message']) && isset($_message['type'])) {
        echo "<div class=\"alert alert-". $_message['type'] ." alert-dismissible fade show\" role=\"alert\">";
        echo $_message['message'];
        echo "<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>";
        echo "</div>\n";
    }
}

function htmlForm($value, $id='', $class='', $action='', $target='') {
    if ($value == 'begin') {
        echo "<form method=\"POST\"". (!empty($id) ? " id=\"". $id ."\"" : "") . (!empty($class) ? " class=\"". $class ."\"" : "") . (!empty($action) ? " action=\"". $action ."\"" : "") . (!empty($target) ? " target=\"". $target ."\"" : "") .">";
    } else if ($value == 'end') {
        echo "</form>";
    }
    echo "\n";
    return;
}

function htmlTitle($label) {
    echo "<div class=\"d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom\"><h1 class=\"h2\">". $label ."</h1></div>";
    return;
}

function htmlAccordion($value, $accordion='', $id='', $label='', $colapsed=false, $progressbar=false) {
    if ($value == 'open') {
        echo "<div class=\"accordion-item\">";
        echo "<h2 class=\"accordion-header\"><button class=\"accordion-button". ($colapsed == false ? "" : " collapsed") ."\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapse-". $id ."\" aria-expanded=\"true\" aria-controls=\"collapse-". $id ."\">". $label ."</h2>";
        echo "<div id=\"collapse-". $id ."\" class=\"accordion-collapse collapse". ($colapsed == false ? " show" : "") ."\">";
        if ($progressbar == true) {
            echo "<div id=\"progress-bar-". $id ."\" class=\"progress-bar-accordion\"></div>";
        }
        echo "<div class=\"accordion-body". ($label == 'Form' || $label == 'Authentication' ? " bg-form" : "") ."\">";
    } else if ($value == 'close') {
        echo "</div></div></div>";
    } else if ($value == 'begin') {
        echo "<div class=\"accordion\" id=\"accordion-". $accordion ."\">";
    } else if ($value == 'end') {
        echo "</div>";
    }
    echo "\n";
    return;
}

function htmlHidden($id, $value='') {
    global $_form;
    $_form[] = $id;
    echo "<input id=\"". $id ."\" name=\"". $id ."\" type=\"hidden\" value=\"". htmlentities($value) ."\">";
    return;
}

function htmlText($id, $label, $value='', $length='', $placeholder='', $help='', $enable=true) {
    global $_form;
    $_form[] = $id;
    echo "<div class=\"mb-3\">";
    echo "<label for=\"". $id ."\" class=\"form-label bg-form-text\">". $label ."</label>";
    echo "<input id=\"". $id ."\" type=\"text\" value=\"". htmlentities($value) ."\" class=\"form-control\"". (!empty($length) ? " maxlength=\"". $length ."\"" : "") . (!empty($placeholder) ? " placeholder=\"". $placeholder ."\"" : "") . (!empty($help) ? " aria-describedby=\"". $id ."-help\"" : "") . ($enable == true ? "" : " disabled=\"disabled\"") ." autocomplete=\"off\">";
    if (!empty($help)) {
        echo "<div id=\"". $id ."-help\" class=\"form-text bg-form-text\">". $help ."</div>";
    }
    echo "</div>\n";
    return;
}

function htmlNumber($id, $label, $value='', $length='', $placeholder='', $help='', $min='', $max='', $step='') {
    global $_form;
    $_form[] = $id;
    echo "<div class=\"mb-3\">";
    echo "<label for=\"". $id ."\" class=\"form-label bg-form-text\">". $label ."</label>";
    echo "<input id=\"". $id ."\" type=\"number\" value=\"". htmlentities($value) ."\" class=\"form-control\"". (!empty($length) ? " maxlength=\"". $length ."\"" : "") . (!empty($placeholder) ? " placeholder=\"". $placeholder ."\"" : "") . (!empty($help) ? " aria-describedby=\"". $id ."-help\"" : "") . ($min >= 0 ? " min=\"". $min ."\"" : "") . (!empty($max) ? " max=\"". $max ."\"" : "") . (!empty($step) ? " step=\"". $step ."\"" : "") ."\" autocomplete=\"off\">";
    if (!empty($help)) {
        echo "<div id=\"". $id ."-help\" class=\"form-text bg-form-text\">". $help ."</div>";
    }
    echo "</div>\n";
    return;
}

function htmlTextDateTime($id, $label, $value='', $length='', $help='', $placeholder='yyyy-mm-dd hh:ii') {
    global $_form, $_defaults;
    $weeks = array(
        'Sunday'=>'0',
        'Monday'=>'1',
        'Tuesday'=>'2',
        'Wednesday'=>'3',
        'Thursday'=>'4',
        'Friday'=>'5',
        'Saturday'=>'6'
    );
    $_form[] = $id;
    echo "<div class=\"mb-3\">";
    echo "<label for=\"". $id ."\" class=\"form-label bg-form-text\">". $label ."</label>";
    //echo "<div class=\"input-group\">";
    echo "<input id=\"". $id ."\" type=\"text\" value=\"". htmlentities($value) ."\" class=\"form-control\"". (!empty($length) ? " maxlength=\"". $length ."\"" : "") . (!empty($placeholder) ? " placeholder=\"". $placeholder ."\"" : "") . (!empty($help) ? " aria-describedby=\"". $id ."-help\"" : "") ." autocomplete=\"off\" data-target=\"#". $id ."\">";
    /*echo '<div class="input-group-append" data-target="#'. $id .'" data-toggle="datetimepicker">
          <span class="input-group-text">
           <i class=\"bi bi-calendar\"></i>
         </span></div></div>';*/
    if (!empty($help)) {
        echo "<div id=\"". $id ."-help\" class=\"form-text bg-form-text\">". $help ."</div>";
    }
    echo "</div>\n";
    echo "<script type=\"text/javascript\">$('#". $id ."').datetimepicker({". ($placeholder != 'yyyy-mm-dd' ? "" : "minView: 2, format: 'yyyy-mm-dd', ") ."weekStart: ". (isset($weeks[$_defaults['startweek']]) ? $weeks[$_defaults['startweek']] : '0') .", todayHighlight: true, autoclose: true}).on('show', function(e){";
    //echo "<script type=\"text/javascript\">$('#". $id ."').datetimepicker({autoclose: true, todayHighlight: true, clearBtn: true}).on('show', function(e){";
    echo "$('.prev').text('<<');$('.next').text('>>');});";
    echo "</script>\n";
    return;
}

function htmlTextArea($id, $label, $value='', $help='') {
    global $_form;
    $_form[] = $id;
    echo "<div class=\"mb-3\">";
    echo "<label for=\"". $id ."\" class=\"form-label bg-form-text\">". $label ."</label>";
    echo "<textarea  id=\"". $id ."\" class=\"form-control\"". (!empty($help) ? " aria-describedby=\"". $id ."-help\"" : "") ." rows=\"4\">". htmlentities($value) ."</textarea>";
    if (!empty($help)) {
        echo "<div id=\"". $id ."-help\" class=\"form-text bg-form-text\">". $help ."</div>";
    }
    echo "</div>\n";
    return;
}

function htmlPassword($id, $label, $help='', $keypress='') {
    global $_form;
    $_form[] = $id;
    echo "<div class=\"mb-3\">";
    echo "<label for=\"". $id ."\" class=\"form-label bg-form-text\">". $label ." <span id=\"extra-label-". $id ."\"></span></label>";
    echo "<input id=\"". $id ."\" type=\"password\" class=\"form-control\"". (!empty($help) ? " aria-describedby=\"". $id ."-help\"" : "") . (!empty($keypress) ? " onkeyup=\"return ". $keypress ."\" onblur=\"return ". $keypress ."\" onchange=\"return ". $keypress ."\"" : "") ." autocomplete=\"off\">";
    if (!empty($help)) {
        echo "<div id=\"". $id ."-help\" class=\"form-text bg-form-text\">". $help ."</div>";
    }
    echo "</div>\n";
    return;
}

function htmlSelect($id, $label, $value=array(), $selected='', $help='', $select=true) {
    global $_form;
    $_form[] = $id;
    echo "<div class=\"mb-3\">";
    echo "<label for=\"". $id ."\" class=\"form-label bg-form-text\">". $label ."</label>";
    //echo "<select id=\"". $id ."\" class=\"form-control form-select\" onfocus=\"this.size=4;\" onblur=\"this.size=0;\" onchange=\"this.size=1; this.blur();\">";
    echo "<select id=\"". $id ."\" class=\"form-select\"". (!empty($help) ? " aria-describedby=\"". $id ."-help\"" : "") .">";
    if ($select == true) {
        echo "<option value=\"\">Select...</option>";
    }
    if (count($value) > 0) {
        $group = array();
        foreach($value as $k=>$v) {
            if (!is_array($v)) {
                echo "<option value=\"". $k ."\"". ($selected == $k ? " selected" : "") .">". htmlentities($v) ."</option>\n";
            } else {
                if (!in_array($v[0], $group)) {
                    $title = htmlentities($v[0]);
                    $group[] = $title;
                    echo "<optgroup label=\"". $title ."\">\n";
                }
                echo "<option value=\"". $k ."\"". ($selected == $k ? " selected" : "") .">". htmlentities($v[1]) ."</option>\n";
            }
        }
    }
    echo "</select>\n";
    if (!empty($help)) {
        echo "<div id=\"". $id ."-help\" class=\"form-text bg-form-text\">". $help ."</div>";
    }
    echo "</div>\n";
    return;
}

function htmlCheckbox($id, $label, $checked=false, $actionjs='', $enable=true, $help='') {
    global $_form;
    $_form[] = $id;
    echo "<div class=\"". (empty($help) ? "mb-3 " :"") ."form-check form-switch\">";
    echo "<input id=\"". $id ."\" type=\"checkbox\" role=\"switch\" class=\"form-check-input\"". (!empty($actionjs)? " onchange=\"return ". $actionjs ."\"" : "") . ($checked == true? " checked" : "") . ($enable == true ? "" : " disabled=\"disabled\"") .">";
    echo "<label for=\"". $id ."\" id=\"". $id ."-label\" class=\"form-check-label bg-form-text\">". $label ."</label>";
    echo "</div>\n";
    if (!empty($help)) {
        echo "<div id=\"". $id ."-help\" class=\"mb-3 form-text bg-form-text\">". $help ."</div>";
    }
    return;
}

function htmlColorPicker($id, $label, $value, $help='') {
    global $_form;
    $_form[] = $id;
    echo "<div class=\"mb-3\">";
    echo "<label for=\"". $id ."\" class=\"form-label bg-form-text\">". $label ."</label>";
    echo "<input type=\"color\" class=\"form-control form-control-color\" id=\"". $id ."\" value=\"". $value ."\" title=\"Choose a color\">";
    if (!empty($help)) {
        echo "<div id=\"". $id ."-help\" class=\"form-text bg-form-text\">". $help ."</div>";
    }
    echo "</div>\n";
    return;
}

function htmlRange($id, $label, $value, $help='', $min=0, $max=100, $step=5, $text=false) {
    global $_form;
    $_form[] = $id;
    echo "<div class=\"mb-3\">";
    echo "<label for=\"". $id ."\" class=\"form-label bg-form-text\">". $label ."</label>";
    if ($text == true) {
        echo "<div class=\"input-group\"><input id=\"". $id ."\" type=\"number\" class=\"form-control\" value=\"". $value ."\" min=\"". $min ."\" max=\"". $max ."\" step=\"". $step ."\" onchange=\"return controlRangeText('". $id ."', 'text');\"><span class=\"input-group-text\">%</span></div>";
    } else {
        echo "<div class=\"form-text bg-form-text\"><em><span id=\"range_value_". $id ."\">". $value ."</span>%</em></div>";
        echo "<input type=\"hidden\" id=\"". $id ."\" value=\"". $value ."\">";
    }
    echo "<input type=\"range\" id=\"range_". $id ."\" class=\"form-range customRange\" value=\"". $value ."\" min=\"". $min ."\" max=\"". $max ."\" step=\"". $step ."\" onchange=\"return controlRange". ($text == true ? "Text" : "") ."('". $id ."'". ($text == true ? ", 'range'" : "") .");\"><div class=\"d-flex justify-content-between\"><span>". $min ."</span><span>". $max ."</span></div>";
    if (!empty($help)) {
        echo "<div id=\"". $id ."-help\" class=\"form-text bg-form-text\">". $help ."</div>";
    }
    echo "</div>\n";
    return;
}

function htmlButtonSubmit($api, $type="button") {
    global $_form;
    $form = "";
    if (count($_form) > 0) {
        foreach($_form as $f) {
            $form .= "'". $f ."',";
        }
    }
    echo "<button type=\"". $type ."\" class=\"btn btn-primary\" onclick=\"return postApi('". $api ."', 'submit', [". $form ."]);\">Submit</button>\n";
    $_form = array();
    return;
}

function htmlButtonCancel($api) {
    global $_form;
    $form = "";
    if (count($_form) > 0) {
        foreach($_form as $f) {
            $form .= "'". $f ."',";
        }
    }
    echo "<button type=\"button\" class=\"btn btn-secondary\" onclick=\"return openCollection('". $api ."');\">Cancel</button>\n";
    $_form = array();
    return;
}

function htmlBox($value, $id='', $border=false, $space='') {
    if ($value == 'begin') {
        echo "<div id=\"". $id ."\" class=\"". ($border == true ? " border" : "") . (!empty($space) ? " p-". $space : "") ."\">";
    } else {
        echo "</div>\n";
    }
    return;
}

function htmlLine($value) {
    if ($value == 'begin') {
        echo "<div class=\"row\">";
    } else {
        echo "</div>\n";
    }
    return;
}

function htmlColumn($value, $number=1) {
    if ($value == 'begin') {
        echo "<div class=\"col-lg-". $number ." pt-1 pb-1\">";
    } else {
        echo "</div>\n";
    }
    return;
}

function htmlCardNumber($title, $value, $color='info') {
    echo "<div class=\"alert alert-". $color ."\" role=\"alert\"\">";
    if (!empty($title)) {
        echo "<div class=\"alert-heading\">". $title ."</div>";
        echo "<hr>";
    }
    echo "<div class=\"text-center\"><p class=\"h1\">". number_format($value) ."</p></div>";
    echo "</div>\n";
    return;
}

function htmlJs($value) {
    echo "<script type=\"text/javascript\">";
    echo $value;
    echo "</script>\n";
    return;
}

function htmlCopyright(){
    global $_version;
    echo "<div class=\"pt-4\"><div class=\"pt-2 my-4 text-end text-muted border-top\">Version: ". $_version ."</div></div>\n";
    return;
}

function htmlCalendarArea($year, $month) {
    echo "<div class=\"mb-3\"><div id=\"area2\"></div></div>\n";
    echo "<script type=\"text/javascript\">openCalendar('". $year ."', '". $month ."');</script>\n";
    return;
}

function htmlChartArea($year, $month) {
    echo "<div class=\"mb-3\"><div id=\"area3\"></div></div>\n";
    echo "<script type=\"text/javascript\">openGraph('". $year ."', '". $month ."');</script>\n";
    return;
}

function htmlActivitiesMap($data, $id=0, $level=0) {
    $map = array();
    if (count($data)) {
        foreach($data as $k=>$v) {
            if ($id != $v['depends']) {
                /*if (isset($map[$v['depends']]['level'])) {
                    $map[$k]['level'] = $map[$v['depends']]['level'] + 1;
                    $map[$v['depends']]['index'][] = $k;
                } else {
                    return 'broken';
                }*/
                if (!isset($map[$v['depends']]['level'])) {
                    $map[$k]['level'] = 0;
                    $map[$v['depends']]['index'][] = $k;
                } else {
                    $map[$k]['level'] = $map[$v['depends']]['level'] + 1;
                    $map[$v['depends']]['index'][] = $k;
                }
            } else if ($v['depends'] == 0) {
                $map[$k]['level'] = 0;
            }
        }
    }
    return $map;
}

function htmlActivitiesItem($id, $min, $max) {
    global $_defaults, $_status, $activities, $map, $used, $hours;
    $item = "";
    if (!in_array($id, $used)) {
        $used[] = $id;
        if (isset($activities[$id])) {
            $hours = 0;
            $now = date('YmdHi');
            $calendar = str_replace('-', '', str_replace(':', '', str_replace(' ', '', $activities[$id]['calendar'])));
            $item .= "<div class=\"row row-cols-1 row-cols-lg-2 mb-1 gantt-line". ($map[$id]['level'] > 0 ? "" : " gantt-divisor") ."\">";
            $item .= "<div class=\"col-lg-3 bg-body-tertiary pt-1 pb-1\">";
            $item .= $activities[$id]['calendar'];
            if ($calendar < $now) {
                $item .= "&nbsp;<span class=\"badge blinkme bg-warning text-dark\">Delayed</span>";
            }
            $item .= "<br><b>". htmlentities($activities[$id]['title']) ."</b>";
            if (!empty($activities[$id]['cluster']) && !empty($activities[$id]['downstream'])) {
                $item .= "<br><i>". htmlentities($activities[$id]['cluster']) ." / ". ($activities[$id]['downstream'] == 'Y' ? "Downstream" : "Upstream") ."</i>";
            }
            $item .= "<br>". (!empty($activities[$id]['environment']) ? "<i>". $activities[$id]['environment'] ."</i>" : "<i>". $_defaults['defaultcolortitle'] ."</i>");
            $item .= "</div>";
            $item .= "<div class=\"col-lg-9\"". ($map[$id]['level'] > 0 ? " style=\"padding-left: ". ($map[$id]['level'] * 40) ."px;\"" : "") .">\n";
            if (isset($activities[$id]['data'])) {
                foreach($activities[$id]['data'] as $v) {
                    $hours += (calcHours($v['start'], $v['end']) * ($v['percent'] / 100));
                    $tooltip = "Start: ". htmlentities($v['start']);
                    $tooltip .= "<br>End: ". htmlentities($v['end']);
                    $tooltip .= "<br>Status: ". htmlentities($v['status']);
                    if (!empty($v['comment'])) {
                        $tooltip .= "<br><i>&quot;". htmlentities($v['comment']) ."&quot;</i>";
                    }
                    $status = "";
                    if ($v['status'] == $_status['W']) {
                        $status = " progress-bar-striped progress-bar-animated";
                    } else if ($v['status'] == $_status['D']) {
                        $status = " progress-bar halfvisible";
                    } else if ($v['status'] == $_status['S']) {
                        $status = " progress-bar-striped";
                    }
                    $item .= "<div class=\"progress mt-2 mb-2\" role=\"progressbar\" aria-label=\"\" aria-valuenow=\"". $v['percent'] ."\" aria-valuemin=\"". $min ."\" aria-valuemax=\"". $max ."\" data-toggle=\"tooltip\" data-placement=\"top\" data-bs-html=\"true\" title=\"". $tooltip ."\" style=\"height: 30px; border: 1px solid #". $activities[$id]['color'] .";\"><div class=\"progress-bar". $status ."\" style=\"". ($v['percent'] > 0 ? "background-color: #". $activities[$id]['color'] ."; width: ". $v['percent'] ."%;" : "background-color: transparent; color: #". $activities[$id]['color'] .";")."\">". $v['percent'] ."%</div></div>\n";
                }
            }
            $item .= "</div>";
            $item .= "<div class=\"col-lg-3 gantt-hours pt-1 pb-1\">Total: ". $hours ." hours</div>";
            $item .= "</div>";
            if (isset($map[$id]['index'])) {
                foreach($map[$id]['index'] as $i) {
                    $item .= htmlActivitiesItem($i, $min, $max);
                }
            }
        }
    }
    return $item;
}

function htmlActivities($id, $min=0, $max=100) {
    global $_alert, $activities, $map, $used;
    if (count($map) && count($activities)) {
        $html = "<div class=\"container gantt-box\">";
        foreach($map as $k=>$v) {
            $html .= htmlActivitiesItem($k, $min, $max);
        }
        $html .= "</div>\n";
    } else {
        $html = "<div class=\"p-3 mb-2\">". $_alert['noactivity'] ."</div>";
    }
    echo $html;
    return;
}

function htmlCalendarMonth($id, $year, $month, $data=array(), $start='Sunday', $weekend=array('Sunday', 'Saturday')) {
    global $_defaults, $_status;
    $week = array('Sunday','Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
    $daytoday = date('d');
    $monthtoday = date('m');
    $yeartoday = date('Y');
    $begin = array();
    $end = array();
    foreach($week as $k=>$v) {
        if ($v == $start) {
            $enable = true;
        }
        if (isset($enable)) {
            $begin[$k] = $v;
        } else {
            $end[$k] = $v;
        }
    }
    $week = $begin + $end;
    unset($enable);
    unset($begin);
    unset($end);
    $strtotime = strtotime($year ."-". $month ."-01");
    $monthtext = date('F', $strtotime);
    $dayweek = date('l', $strtotime);
    $lastday = date('t', $strtotime);
    echo "<div class=\"mt-3 mb-3\">";
    echo "<div class=\"table-responsive\"><table id=\"". $id ."\" class=\"table table-bordered\" border=\"0\" style=\"width:100%\">";
    echo "<thead>";
    echo "<tr><td class=\"calendar-title text-center fw-bold fs-5\" colspan=\"7\">". $monthtext ." / ". $year ."</td></tr>";
    echo "<tr>";
    $prepare = "";
    $count = 0;
    $weeknow = array();
    $i = 1;
    foreach($week as $w) {
        $weeknow[$i] = $w;
        echo "<th class=\"calendar-title\">". $w ."</th>";
        if (!isset($stop)) {
            if ($dayweek != $w) {
                $prepare .= "<td class=\"". (isset($_defaults['weekends']) ? ($_defaults['weekends'] == true ? "calendar-weekend" : "" ) : "calendar-weekend") ."\">&nbsp;</td>";
                $count++;
            } else {
                $stop = true;
            }
        }
        $i++;
    }
    unset($stop);
    echo "</tr>\n";
    echo "</thead>\n";
    echo "<tbody>\n";
    echo "<tr>";
    echo $prepare;
    for ($i=1;$i<=$lastday; $i++) {
        if ($count < 7) {
            $count++;
        } else {
            echo "</tr><tr>\n";
            $count = 1;
        }
        if ($yeartoday == $year && $monthtoday == $month) {
            $current = true;
        }
        $day = str_pad($i, 2, "0", STR_PAD_LEFT);
        $enableweekend = (!in_array($weeknow[$count], $weekend) ? "" : " class=\"". (isset($_defaults['weekends']) ? ($_defaults['weekends'] == true ? "calendar-weekend" : "" ) : "calendar-weekend") ."\"");
        echo "<td". (!isset($current) ? $enableweekend : ($daytoday != $day ? $enableweekend : " class=\"calendar-today\"")) ." style=\"width:14%; height: 80px;\">";
        echo "<div class=\"d-flex justify-content-between\">";
        echo "<div class=\"\">";
        echo "<span>". $day ."</span>";
        echo "</div>";
        if (!empty($data)) {
            if (isset($data[$day])) {
                echo "<div class=\"\">";
                foreach($data[$day] as $v) {
                    $color = (!empty($v['color']) ? $v['color'] : $_defaults['defaultcolorcode']);
                    $tooltip = "";
                    if (!empty($v['status'])) {
                        switch($v['status']) {
                            case 'C': $status = $_status['C']; break;
                            case 'W': $status = $_status['W']; break;
                            case 'R': $status = $_status['R']; break;
                            case 'S': $status = $_status['S']; break;
                            case 'D': $status = $_status['D']; break;
                            default: $status = $_status['N'];
                        }
                        $tooltip .= "[". $status ."]<br>";
                    }
                    $tooltip .= "<b>". htmlentities($v['title']) ."</b>";
                    if (!empty($v['cluster']) && !empty($v['downstream'])) {
                        $tooltip .= "<br>". htmlentities($v['cluster']) ." / ". ($v['downstream'] == 'Y' ? "Downstream" : "Upstream");
                    }
                    if (!empty($v['environment'])) {
                        $tooltip .= "<br>". htmlentities($v['environment']) ."";
                    }
                    if (!empty($v['description'])) {
                        $tooltip .= "<br><i>&quot;". htmlentities($v['description']) ."&quot;</i>";
                    }
                    $style = "";
                    if ($v['complete'] == 'N') {
                        $style = "background-color: transparent; border: 2px solid #". $color ."; color: #". $color .";";
                    } else {
                        $style = "background-color: #". $color ."; ". (($v['status'] != 'R' && $v['status'] != 'D') ? "text-decoration: line-through; font-style: italic;" : "") ."color: #FFFFFF;";
                    }
                    echo "<span class=\"badge". (($v['status'] != 'D') ? ($v['complete'] == 'N' && (intval($yeartoday . str_pad($monthtoday, 2, "0", STR_PAD_LEFT) . str_pad($daytoday, 2, "0", STR_PAD_LEFT)) > intval($year . str_pad($month, 2, "0", STR_PAD_LEFT) . str_pad($i, 2, "0", STR_PAD_LEFT)) && (empty($v['status']) || $v['status'] == 'N' || $v['status'] == 'W' || $v['status'] == 'S')) ? " blinkme" : "") : " halfvisible") ."\" style=\"cursor: default; ". $style ."\" data-toggle=\"tooltip\" data-placement=\"top\" data-bs-html=\"true\" title=\"". $tooltip ."\">". $v['time'] ."</span><br>";
                }
                echo "</div>";
            }
        }
        echo "</div>";
        echo "</td>\n";
    }
    for ($i=$count;$i<7; $i++) {
        if ($count < 7) {
            echo "<td class=\"". (isset($_defaults['weekends']) ? ($_defaults['weekends'] == true ? "calendar-weekend" : "" ) : "calendar-weekend") ."\">&nbsp;</td>";
            $count++;
        } else {
            echo "</tr>\n";
            unset($count);
            break;
        }
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
    echo "</tbody>";
    echo "</table></div>\n";
    echo "</div>\n";
    echo "<div class=\"row mt-3 mb-3\">";
    echo "<div class=\"col text-start text-nowrap\">";
    echo "<button type=\"button\" class=\"btn btn-primary btn-sm\" onclick=\"return openCalendar('". ($year - 1) ."', '". $month ."');\"><svg class=\"bi\"><use xlink:href=\"#bi-chevron-double-left\"/></svg></button>";
    echo "&nbsp;<button type=\"button\" class=\"btn btn-primary btn-sm\" onclick=\"return openCalendar('". $prevyear ."', '". $prevmonth ."');\"><svg class=\"bi\"><use xlink:href=\"#bi-chevron-left\"/></svg></button>";
    echo "&nbsp;<button type=\"button\" class=\"btn btn-primary btn-sm\" onclick=\"return openCalendar('". $nextyear ."', '". $nextmonth ."');\"><svg class=\"bi\"><use xlink:href=\"#bi-chevron-right\"/></svg></button>";
    echo "&nbsp;<button type=\"button\" class=\"btn btn-primary btn-sm\" onclick=\"return openCalendar('". ($year + 1) ."', '". $month ."');\"><svg class=\"bi\"><use xlink:href=\"#bi-chevron-double-right\"/></svg></button>";
    echo "</div>";
    echo "<div class=\"col text-end\"><div class=\"input-group text-nowrap\">";
    echo "<input class=\"form-control form-control-sm\" list=\"datalist-month\" id=\"calendar-month\" value=\"". str_pad($month , 2, "0", STR_PAD_LEFT) ."\" maxlength=\"2\" placeholder=\"Month\"><datalist id=\"datalist-month\">";
    for ($i=1; $i<=12; $i++) {
        echo "<option value=\"". str_pad($i, 2, "0", STR_PAD_LEFT) ."\">";
    }
    echo "</datalist>";
    echo "<input class=\"form-control form-control-sm\" list=\"datalist-year\" id=\"calendar-year\" value=\"". $year ."\" maxlength=\"4\" placeholder=\"Year\"><datalist id=\"datalist-year\">";
    for ($i=$_defaults['startyear']; $i<=(date('Y')+4); $i++) {
        echo "<option value=\"". $i ."\">";
    }
    echo "</datalist>";
    echo "<button type=\"button\" class=\"btn btn-primary btn-sm\" onclick=\"return openCalendar($('#calendar-year').val(), $('#calendar-month').val());\">Go!</button>";
    echo "</div></div>";
    echo "</div>\n";
    return;
}

function htmlTable($id, $cols, $key, $data, $order=array(), $control=array(), $boolean=array(), $color=array(), $url=array(), $hidden=array(), $icon=array()) {
    global $collection;
    $invisible = array();
    echo "<div class=\"table-responsive\"><table id=\"". $id ."\" class=\"table table-striped\" border=\"0\" style=\"width:100%\">\n";
    if (count($cols)) {
        echo "<thead>";
        echo "<tr>";
        echo "<th>#</th>";
        $i = 1;
        foreach($cols as $c=>$col) {
            echo "<th>". $col ."</th>";
            if (in_array($c, $hidden)) {
                $invisible[] = $i;
            }
            $i++;
        }
        if (count($control)) {
            echo "<th>Ctrl</th>";
        }
        echo "</tr>";
        echo "</thead>\n";
        echo "<tbody>\n";
        $i = 1;
        foreach($data as $d) {
            echo "<tr>";
            echo "<td>". $i ."</td>";
            $c = 1;
            foreach($d as $k=>$v) {
                if (isset($cols[$k])) {
                    if (!in_array($k, $boolean) && !in_array($k, $color) && !in_array($k, $url) && !in_array($k, $icon)) {
                        if (!empty($v) || $v === '0') {
                            echo "<td style=\"white-space: nowrap\">". htmlentities($v) ."</td>";
                        } else {
                            echo "<td>&nbsp;</td>";
                        }
                    } else if (in_array($k, $color)) {
                        if (!empty($v)) {
                            echo "<td><span style=\"cursor: default; background-color: #". $v ."\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>";
                        } else {
                            echo "<td>&nbsp;</td>";
                        }
                    } else if (in_array($k, $icon)) {
                         if (!empty($v)) {
                            echo "<td><svg class=\"bi\" title=\"". $v ."\"><use xlink:href=\"#". strtolower($v) ."\"/></svg></td>";
                        } else {
                            echo "<td>&nbsp;</td>";
                        }
                    } else if (in_array($k, $url)) {
                        if (!empty($v)) {
                            if (!strpos($v, '@')) {
                                echo "<td><a href=\"". $v ."\" target=\"_blank\"><svg class=\"bi btn-control-table\"><use xlink:href=\"#bi-folder-symlink-fill\"/></svg></a></td>";
                            } else {
                                echo "<td><a href=\"mailto:". $v ."\" target=\"_blank\"><svg class=\"bi btn-control-table\"><use xlink:href=\"#bi-envelope-fill\"/></svg></a></td>";
                            }
                        } else {
                            echo "<td>&nbsp;</td>";
                        }
                    } else if (in_array($k, $boolean)) {
                        if (!empty($v)) {
                            if ($v == 'Y') {
                                echo "<td><svg class=\"bi\" title=\"Yes\"><use xlink:href=\"#check-circle-fill\"/></svg></td>";
                            } else {
                                echo "<td><svg class=\"bi\" title=\"No\"><use xlink:href=\"#dash-circle-fill\"/></svg></td>";
                            }
                        } else {
                            echo "<td>&nbsp;</td>";
                        }
                    }
                }
                $c++;
            }
            if (count($control)) {
                echo "<td style=\"white-space: nowrap\">";
                $n = 1;
                foreach ($control as $v) {
                    if ($v != 'complete') {
                        if ($collection != 'activities') {
                            if ($v != 'delete') {
                                echo "<a href=\"#\" title=\"". ucwords($v) ."\" onclick=\"return ". ($v != 'download' ? "actionApi('". $collection ."','". $v ."','". $d[$key] ."')" : "postDownload('". $d[$key] ."')") .";\"><svg class=\"bi btn-control-table\"><use xlink:href=\"#". $v ."\"/></svg></a>". (count($control) > $n ? "&nbsp;&nbsp;&nbsp;" : "");
                            } else {
                                echo "<a href=\"#\" title=\"Delete\" onclick=\"return prepareDelete('". $id ."','". $d[$key] ."');\" data-bs-toggle=\"modal\" data-bs-target=\"#". $id ."-delete-modal\"><svg class=\"bi btn-control-table\"><use xlink:href=\"#delete\"/></svg></a>". (count($control) > $n ? "&nbsp;&nbsp;&nbsp;" : "");
                            }
                        } else {
                            if (empty($d['t_complete'])) {
                                if ($v != 'delete') {
                                    echo "<a href=\"#\" title=\"". ucwords($v) ."\" onclick=\"return ". ($v != 'download' ? "actionApi('". $collection ."','". $v ."','". $d[$key] ."')" : "postDownload('". $d[$key] ."')") .";\"><svg class=\"bi btn-control-table\"><use xlink:href=\"#". $v ."\"/></svg></a>". (count($control) > $n ? "&nbsp;&nbsp;&nbsp;" : "");
                                } else {
                                    echo "<a href=\"#\" title=\"Delete\" onclick=\"return prepareDelete('". $id ."','". $d[$key] ."');\" data-bs-toggle=\"modal\" data-bs-target=\"#". $id ."-delete-modal\"><svg class=\"bi btn-control-table\"><use xlink:href=\"#delete\"/></svg></a>". (count($control) > $n ? "&nbsp;&nbsp;&nbsp;" : "");
                                }
                            } else {
                                if ($v != 'delete') {
                                    echo "<svg class=\"bi btn-control-table-unlink\"><use xlink:href=\"#". $v ."\"/></svg>". (count($control) > $n ? "&nbsp;&nbsp;&nbsp;" : "");
                                } else {
                                    echo "<svg class=\"bi btn-control-table-unlink\"><use xlink:href=\"#delete\"/></svg>". (count($control) > $n ? "&nbsp;&nbsp;&nbsp;" : "");
                                }
                            }
                        }
                    } else {
                        if ($d['t_complete'] == 'Y') {
                            echo "<a href=\"#\" title=\"Complete\" onclick=\"return actionApi('". $collection ."','complete','". $d[$key] ."');\"><svg class=\"bi btn-control-table\"><use xlink:href=\"#complete\"/></svg></a>". (count($control) > $n ? "&nbsp;&nbsp;&nbsp;" : "");
                        } else {
                            echo "<svg class=\"bi btn-control-table-unlink\"><use xlink:href=\"#complete\"/></svg>". (count($control) > $n ? "&nbsp;&nbsp;&nbsp;" : "");
                        }
                    }
                    $n++;
                }
                echo "</td>";
            }
            echo "</tr>";
            $i++;
        }
        echo "</tbody>\n";
    }
    echo "</table></div>\n";
    echo "<div class=\"modal fade\" id=\"". $id ."-delete-modal\" tabindex=\"-1\" aria-labelledby=\"". $id ."-delete-modal-label\" aria-hidden=\"true\"><div class=\"modal-dialog\"><div class=\"modal-content\"><div class=\"modal-header\"><h1 class=\"modal-title fs-5\" id=\"". $id ."-delete-modal-label\">Alert!</h1><button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button></div><div class=\"modal-body\">Do you confirm the deletion?</div><div class=\"modal-footer\"><input type=\"hidden\" id=\"". $id ."-delete\" value=\"\"><button type=\"button\" class=\"btn btn-secondary\" data-bs-dismiss=\"modal\">Cancel</button><button type=\"button\" class=\"btn btn-primary\" onclick=\"return actionDeleteApi('". $collection ."', '". $id ."');\" data-bs-dismiss=\"modal\">Yes, I confirm</button></div></div></div></div>\n";
    echo "<script type=\"text/javascript\">";
    echo "new DataTable('#". $id ."', { responsive: true, pageLength: ". (isset($_SESSION['user']['page']) ? $_SESSION['user']['page'] : 10) .",";
    if (count($order)) {
        echo " order: [[". $order[0] .", '". $order[1] ."']],";
    }
    echo " \"oLanguage\": { sLengthMenu: \"_MENU_\", sSearch: \"\", sSearchPlaceholder: \"Search in Table\" },";
    echo " \"columnDefs\": [";
    echo " { \"targets\": 0, \"searchable\": true },";
    $total = (count($cols) - count($boolean) - count($color) - count($url) - count($icon));
    for ($i=1; $i<=$total; $i++) {
        if (!in_array($i, $invisible)) {
            echo " { \"targets\": ". $i.", render: function ( data, type, row ) { return type === 'display' && data.length > 20 ? data.substr( 0, 20 ) +'…' : data;} },";
        } else {
            echo " { \"targets\": ". $i .", \"searchable\": true, \"visible\": false },";
        }
    }
    $remaining = ($total + count($boolean) + count($color) + count($url) + count($icon) + (count($control) ? 1 : 0));
    $i2 = $total;
    for ($i=($total+1); $i<=$remaining; $i++) {
        //echo " { \"targets\": ". $i .", \"searchable\": false, \"orderable\": false,". (count($control) ? ($i < $remaining ? " \"className\": 'dt-center'" : " \"className\": 'dt-right'") : "") ." },";
        echo " { \"targets\": ". $i .", \"searchable\": false, \"orderable\": false, \"className\": 'dt-center' },";
    }
    echo "]});";
    echo "</script>\n";
    return;
}

function ldapGetCN($dn) {
    if (!empty($dn)) {
        if (strpos($dn, '=') !== false) {
            preg_match('/=(.*?)(?:,|$)/', $dn, $matchs);
            return (isset($matchs[1]) ? $matchs[1] : '');
        } else {
            return $dn;
        }
    }
    return;
}

function ldapSearchAuth($login, $password='') {
    $ldapconn = @ldap_connect(PRJ_LDAP_SERVER, PRJ_LDAP_PORT)
        or die('Could not connect to LDAP server!');
    ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
    $ldapbind = @ldap_bind($ldapconn, PRJ_LDAP_BIND_DN, PRJ_LDAP_BIND_PASSWORD)
        or die('LDAP bind failed!');
    $parameters = array('cn', 'displayname', 'sn', 'givenname', 'mail', 'dn', 'uid', 'memberof');
    $result = @ldap_search($ldapconn, PRJ_LDAP_BASE_DN, "(". PRJ_LDAP_FILTER ."=". $login .")", $parameters)
        or die("Error in search query: ". ldap_error($ldapconn));
    $data = @ldap_get_entries($ldapconn, $result);
    if(!empty($data)) {
        if (is_array($data)) {
            if ($data['count'] == 1) {
                $user_login = $login;
                $user_cn = (!empty($data[0]['cn']) ? (is_array($data[0]['cn']) ? ldapGetCN($data[0]['cn'][0]) : ldapGetCN($data[0]['cn'])) : '');
                $user_sn = (!empty($data[0]['sn']) ? (is_array($data[0]['sn']) ? $data[0]['sn'][0] : $data[0]['sn']) : '');
                $user_givenname = (!empty($data[0]['givenname']) ? (is_array($data[0]['givenname']) ? $data[0]['givenname'][0] : $data[0]['givenname']) : '');
                $user_displayname = (!empty($data[0]['displayname']) ? (is_array($data[0]['displayname']) ? $data[0]['displayname'][0] : $data[0]['displayname']) : '');
                $user_fullname = (!empty($user_displayname) ? $user_displayname : (!empty($user_givenname) && !empty($user_sn) ? $user_givenname ." ". $user_sn : $user_cn));
                $user_mail = (!empty($data[0]['mail']) ? (is_array($data[0]['mail']) ? $data[0]['mail'][0] : $data[0]['mail']) : '');
                $user_dn = (!empty($data[0]['dn']) ? (is_array($data[0]['dn']) ? $data[0]['dn'][0] : $data[0]['dn']) : '');
                $user_uid = (!empty($data[0]['uid']) ? (is_array($data[0]['uid']) ? $data[0]['uid'][0] : $data[0]['uid']) : '');
                $group = false;
                if (!empty(PRJ_LDAP_GROUP_NAME)) {
                    // ad
                    $user_adgroups = (!empty($data[0]['memberof']) ? $data[0]['memberof'] : array());
                    if (count($user_adgroups)) {
                        foreach($user_adgroups as $g) {
                            if (strtolower(PRJ_LDAP_GROUP_NAME) == strtolower($g) || strtolower('cn='. PRJ_LDAP_GROUP_NAME .','. PRJ_LDAP_GROUPS_BASE_DN) == strtolower($g)) {
                                $group = true;
                                break;
                            }
                        }
                    }
                    // openldap
                    if (!$group && !empty(PRJ_LDAP_GROUPS_BASE_DN)) {
                        $userdn_escaped = ldap_escape($user_dn, "", LDAP_ESCAPE_DN);
                        $search = @ldap_search($ldapconn, "ou=". PRJ_LDAP_GROUP_NAME .",". PRJ_LDAP_GROUPS_BASE_DN, "(uniquemember=". $userdn_escaped .")");
                        if (@ldap_count_entries($ldapconn, $search) > 0) {
                            $group = true;
                        }
                    }
                } else {
                    $group = true;
                }
                if ($group) {
                    if (empty($password)) {
                        ldap_unbind($ldapconn);
                        return array(
                            'login'=>$user_login,
                            'fullname'=>$user_fullname,
                            'mail'=>$user_mail,
                        );
                    } else {
                        if (@ldap_bind($ldapconn, $user_dn, $password)) {
                            ldap_unbind($ldapconn);
                            return true;
                        }
                    }
                }
            }
        }
    }
    ldap_unbind($ldapconn);
    return;
}

function authLogin($login, $password) {
    global $_alert;
    if (!empty($login) && !empty($password)) {
        $user = sqlGet('tb_user',
            array(
                'id'=>'id_user',
                't_login'=>'tx_login',
                't_fullname'=>'tx_fullname',
                't_password'=>'tx_password',
                't_email'=>'tx_email',
                't_page'=>'nu_page',
                't_theme'=>'tp_theme',
                't_role'=>'tp_role',
                't_ldap'=>'fl_ldap',
                't_active'=>'fl_active',
                't_record'=>'dt_record',
            ),
            array('tx_login'=>$login)
        );
        if (isset($user['id'])) {
            if ($user['t_ldap'] == 'N') {
                $md5password = md5($password);
                if ($md5password == $user['t_password']) {
                    if ($user['t_active'] == 'Y') {
                        $_SESSION['user'] = array(
                            'id'=>$user['id'],
                            'login'=>$user['t_login'],
                            'fullname'=>$user['t_fullname'],
                            'email'=>$user['t_email'],
                            'page'=>$user['t_page'],
                            'theme'=>$user['t_theme'],
                            'role'=>$user['t_role'],
                            'ldap'=>$user['t_ldap'],
                            //'record'=>$user['record'],
                            //'strtotime'=>strtotime('now'),
                        );
                        return 200;
                    } else {
                        return array('msg'=>$_alert['loginblocked']);
                    }
                } else {
                    return array('msg'=>$_alert['loginpasswordinvalid']);
                }
            } else {
                $ldapuser = ldapSearchAuth($user['t_login'], $password);
                if (!empty($ldapuser)) {
                    if ($user['t_active'] == 'Y') {
                        $_SESSION['user'] = array(
                            'id'=>$user['id'],
                            'login'=>$user['t_login'],
                            'fullname'=>$user['t_fullname'],
                            'email'=>$user['t_email'],
                            'page'=>$user['t_page'],
                            'theme'=>$user['t_theme'],
                            'role'=>$user['t_role'],
                            'ldap'=>$user['t_ldap'],
                            //'record'=>$user['record'],
                            //'strtotime'=>strtotime('now'),
                        );
                        return 200;
                    } else {
                        return array('msg'=>$_alert['loginblocked']);
                    }
                } else {
                    return array('msg'=>$_alert['loginpasswordinvalid']);
                }
            }
        } else {
            return array('msg'=>$_alert['loginpasswordinvalid']);
        }        
    } else {
        return array('msg'=>$_alert['loginpasswordrequired']);
    }
    return;
}

$_form = array();