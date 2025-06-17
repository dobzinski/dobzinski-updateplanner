<?php

require __DIR__ .'/../etc/config.php';
require __DIR__ .'/../etc/function.php';
require __DIR__ .'/../etc/conn_open.php';
require __DIR__ .'/../etc/var.php';

//$linebreak = "\r\n";
//$linebreak = "\n";
$files = array();
getReports(__DIR__ .'/../report/queue');
if (count($files)) {
    $diroutput = __DIR__ .'/../report/out/';
    $dirsave = __DIR__ .'/../data/pdf/';
    foreach($files as $k=>$v) {
        if ($v['extension'] == 'wait') {
            switch($v['report']) {

                case 'Activities':

                    $formatchars = controlSpecialChars($_report['dateformat'], '[^a-zA-Z]');
                    $formatdatedb = controlSpecialChars($_report['dateformat'], '(['. $formatchars .'])', '%$1');
                    $start = preg_replace('/(\d{4})(\d{2})(\d{2})/', '$1-$2-$3', $v['start']);
                    $end = preg_replace('/(\d{4})(\d{2})(\d{2})/', '$1-$2-$3', $v['end']);
                    $filename = $v['folder'] ."/". $v['id'] .".wait";
                    echo "[Script Reports] - ". date('Y-m-d H:i:s') ." - Generating file content: ". $v['id'] .".tex\n";
                    
                    $activities = sqlSelect(
                        "SELECT
                            DATE_FORMAT(c.dt_schedule, '". $formatdatedb ."') AS t_schedule,
                            c.tx_title AS t_title,
                            t.tx_cluster AS t_cluster,
                            (CASE
                                WHEN e.tx_environment IS NOT NULL THEN e.tx_environment
                                WHEN e2.tx_environment IS NOT NULL THEN e2.tx_environment
                                ELSE '". $_defaults['defaultcolortitle'] ."'
                            END) AS t_environment,
                            r.tx_resource AS t_resource,
                            v.tx_version AS t_version,
                            c.fl_complete AS t_fcomplete,
                            DATE_FORMAT(c.dt_complete, '%Y-%m-%d') AS t_dcomplete,
                            (CASE
                                WHEN i.tp_status = 'N' THEN '". $_reportstatus['N'] ."'
                                WHEN i.tp_status = 'W' THEN '". $_reportstatus['W'] ."'
                                WHEN i.tp_status = 'C' THEN '". $_reportstatus['C'] ."'
                                WHEN i.tp_status = 'R' THEN '". $_reportstatus['R'] ."'
                                WHEN i.tp_status = 'S' THEN '". $_reportstatus['S'] ."'
                                WHEN i.tp_status = 'D' THEN '". $_reportstatus['D'] ."'
                                ELSE '". $_reportstatus['default'] ."'
                            END) AS t_status
                        FROM
                            tb_calendar c
                                JOIN tb_resource_version v ON (c.id_resource_version = v.id_resource_version)
                                JOIN tb_resource r ON (v.id_resource = r.id_resource)
                                LEFT JOIN tb_cluster t ON (c.id_cluster = t.id_cluster)
                                LEFT JOIN tb_environment e ON (e.id_environment = t.id_environment)
                                LEFT JOIN tb_environment e2 ON (c.id_environment = e2.id_environment)
                                LEFT JOIN tb_calendar_item i ON (i.id_calendar = c.id_calendar 
                                    AND DATE_FORMAT(i.dt_end, '%Y-%m-%d %H:%i') = (SELECT MAX(DATE_FORMAT(i2.dt_end, '%Y-%m-%d %H:%i')) FROM tb_calendar_item i2 WHERE i2.id_calendar = i.id_calendar))
                        WHERE
                            c.fl_public = ? AND
                            ((DATE_FORMAT(c.dt_complete, '%Y-%m-%d %H:%i:%s') BETWEEN ? AND ? AND c.fl_complete = ?) OR
                                (DATE_FORMAT(c.dt_schedule, '%Y-%m-%d %H:%i:%s') BETWEEN ? AND ? AND c.fl_complete = ?) OR
                                (DATE_FORMAT(c.dt_schedule, '%Y-%m-%d %H:%i:%s') < ? AND c.fl_complete = ?))
                        ORDER BY
                            t_environment ASC, t_schedule ASC",
                        array('Y', $start." 00:00:00", $end." 23:59:59", 'Y', $start." 00:00:00", $end." 23:59:59", 'Y', $end." 23:59:59", 'N')
                    );

                    $cases = sqlSelect(
                        "SELECT
                            c.id_case AS t_case,
                            c.tx_subject AS t_subject,
                            c.tx_report AS t_report,
                            p.tx_product AS t_product,
                            t.tx_priority AS t_priority,
                            (CASE
                                WHEN e.fl_production = 'Y' THEN '". $_report['textboolyes'] ."'
                                WHEN e.fl_production = 'N' THEN '". $_report['textboolno'] ."'
                                ELSE '-'
                            END) AS t_production,
                            DATE_FORMAT(c.dt_open, '". $formatdatedb ."') AS t_open,
                            DATE_FORMAT(c.dt_close, '". $formatdatedb ."') AS t_close
                        FROM
                            tb_case c JOIN tb_product p ON (c.id_product = p.id_product)
                                JOIN tb_priority t ON (c.id_priority = t.id_priority)
                                LEFT JOIN tb_environment e ON (c.id_environment = e.id_environment AND e.fl_support = 'Y')
                        WHERE
                            (DATE_FORMAT(c.dt_close, '%Y-%m-%d %H:%i:%s') BETWEEN ? AND ?) OR
                            (DATE_FORMAT(c.dt_open, '%Y-%m-%d %H:%i:%s') BETWEEN ? AND ? AND c.dt_close IS NOT NULL) OR
                            (DATE_FORMAT(c.dt_open, '%Y-%m-%d %H:%i:%s') < ? AND c.dt_close IS NULL)
                        ORDER BY
                            t_case ASC",
                        array($start." 00:00:00", $end." 23:59:59", $start." 00:00:00", $end." 23:59:59", $end." 23:59:59")
                    );
                    
                    $recurrents = sqlSelect(
                        "SELECT
                            tx_title AS t_title,
                            tx_report AS t_report
                        FROM
                            tb_recurrent
                        WHERE
                            fl_active = ?
                        ORDER BY
                            t_title ASC",
                        array('Y')
                    );

                    $abstracts = sqlSelect(
                        "SELECT
                            DATE_FORMAT(r.dt_report, '". $formatdatedb ."') AS t_date,
                            (CASE
                                WHEN r.tx_newtitle IS NOT NULL THEN r.tx_newtitle
                                ELSE c.tx_title
                            END) AS t_title,
                            r.tx_report AS t_report
                        FROM
                            tb_calendar_report r LEFT JOIN tb_calendar c ON (c.id_calendar = r.id_calendar AND c.fl_public = 'Y' AND r.id_calendar_report = (SELECT MAX(r2.id_calendar_report) FROM tb_calendar_report r2 WHERE c.id_calendar = r2.id_calendar))
                        WHERE
                            ((DATE_FORMAT(c.dt_complete, '%Y-%m-%d %H:%i:%s') BETWEEN ? AND ? AND c.fl_complete = ?)
                                    OR (DATE_FORMAT(c.dt_schedule, '%Y-%m-%d %H:%i:%s') BETWEEN ? AND ? AND c.fl_complete = ?)
                                    OR (DATE_FORMAT(c.dt_schedule, '%Y-%m-%d %H:%i:%s') < ? AND c.fl_complete = ?))
                            OR (r.id_calendar IS NULL AND
                                DATE_FORMAT(r.dt_report, '%Y-%m-%d') BETWEEN ? AND ?) 
                        ORDER BY
                            t_date ASC",
                        array($start." 00:00:00", $end." 23:59:59", 'Y', $start." 00:00:00", $end." 23:59:59", 'Y', $end." 23:59:59", 'N', $start, $end)
                    );

                    $today = date($_report['dateformat']);

                    $startformated = date_create($start);
                    $startformated = date_format($startformated, $_report['dateformat']);

                    $endformated = date_create($end);
                    $endformated = date_format($endformated, $_report['dateformat']);

                    $report = '% Generated on '. $today .' - Robson Dobzinski\'s project, please check it out on GitHub, thanks!'. "\n";
                    $template = __DIR__ .'/../report/templates/activities.tex';
                    if (is_file($template)) {
                        $content = file_get_contents($template);
                        if (!empty($content)) {
                            //$rows = explode($linebreak, $content);
                            $rows = preg_split('/\r\n|\r|\n/', $content);
                            if (count($rows)) {
                                $replacing = false;
                                foreach($rows as $r) {
                                    if (strpos($r, '% Start variables') === 0) {
                                        $replacing = true;
                                        $report .= $r . "\n";
                                    } else if (strpos($r, '% End variables') === 0) {
                                        $replacing = false;
                                        $report .= $r . "\n";
                                    } else if (strpos($r, '%') !== 0) {
                                        if ($replacing == true) {
                                            switch($r) {
                                                case '\def\mycover{}':
                                                    $report .= '\def\mycover{'. $_report['cover'] .'}'. "\n";
                                                break;
                                                case '\def\mylogo{}':
                                                    $report .= '\def\mylogo{'. $_report['logo'] .'}'. "\n";
                                                break;
                                                case '\def\mybetweendates{}':
                                                    $report .= '\def\mybetweendates{'. $startformated ." ". $_report['betweendates'] ." ". $endformated .'}'. "\n";
                                                break;
                                                case '\def\mydatetoday{}':
                                                    $report .= '\def\mydatetoday{'. $today .'}'. "\n";
                                                break;
                                                case '\def\mytextcover{}':
                                                    $report .= '\def\mytextcover{'. $_report['textcover'] .'}'. "\n";
                                                break;
                                                case '\def\mybrandheader{}':
                                                    $report .= '\def\mybrandheader{'. $_report['brandheader'] .'}'. "\n";
                                                break;
                                                case '\def\mytextheader{}':
                                                    $report .= '\def\mytextheader{'. $_report['textheader'] .'}'. "\n";
                                                break;
                                                case '\def\mylinkheader{}':
                                                    $report .= '\def\mylinkheader{'. $_report['linkheader'] .'}'. "\n";
                                                break;
                                                case '\def\mytextfooter{}':
                                                    $report .= '\def\mytextfooter{'. $_report['textfooter'] .'}'. "\n";
                                                break;
                                                case '\def\mytitle{}':
                                                    $report .= '\def\mytitle{'. $_report['title'] .'}'. "\n";
                                                break;
                                                case '\def\mysummary{}':
                                                    $report .= '\def\mysummary{'. $_report['summary'] .'}'. "\n";
                                                break;
                                                case '\def\myspaceaftersummary{}':
                                                    $report .= '\def\myspaceaftersummary{'. $_report['spaceaftersummary'] .'}'. "\n";
                                                break;
                                                case '\def\mytextactivity{}':
                                                    $report .= '\def\mytextactivity{'. $_report['textactivity'] .'}'. "\n";
                                                break;
                                                case '\def\mytextactivityenvironment{}':
                                                    $report .= '\def\mytextactivityenvironment{'. $_report['textactivityenvironment'] .'}'. "\n";
                                                break;
                                                case '\def\mytextactivitydescription{}':
                                                    $report .= '\def\mytextactivitydescription{'. $_report['textactivitydescription'] .'}'. "\n";
                                                break;
                                                case '\def\mytextactivitydate{}':
                                                    $report .= '\def\mytextactivitydate{'. $_report['textactivitydate'] .'}'. "\n";
                                                break;
                                                case '\def\mytextactivitystatus{}':
                                                    $report .= '\def\mytextactivitystatus{'. $_report['textactivitystatus'] .'}'. "\n";
                                                break;
                                                case '\def\mytextcases{}':
                                                    $report .= '\def\mytextcases{'. $_report['textcases'] .'}'. "\n";
                                                break;
                                                case '\def\mytextcasesnumber{}':
                                                    $report .= '\def\mytextcasesnumber{'. $_report['textcasesnumber'] .'}'. "\n";
                                                break;
                                                case '\def\mytextcasespriority{}':
                                                    $report .= '\def\mytextcasespriority{'. $_report['textcasespriority'] .'}'. "\n";
                                                break;
                                                case '\def\mytextcasesopen{}':
                                                    $report .= '\def\mytextcasesopen{'. $_report['textcasesopen'] .'}'. "\n";
                                                break;
                                                case '\def\mytextcasesclose{}':
                                                    $report .= '\def\mytextcasesclose{'. $_report['textcasesclose'] .'}'. "\n";
                                                break;
                                                case '\def\mytexttimeline{}':
                                                    $report .= '\def\mytexttimeline{'. $_report['texttimeline'] .'}'. "\n";
                                                break;
                                                case '\def\mytextprodshortname{}':
                                                    $report .= '\def\mytextprodshortname{'. $_report['textprodshortname'] .'}'. "\n";
                                                break;
                                                case '\def\myrgbheader{}':
                                                    $report .= '\def\myrgbheader{'. $_report['rgbheader'] .'}'. "\n";
                                                break;
                                                case '\def\myrgbtitle{}':
                                                    $report .= '\def\myrgbtitle{'. $_report['rgbtitle'] .'}'. "\n";
                                                break;
                                            }
                                        } else {
                                            $report .= $r . "\n";
                                        }
                                    }
                                }
                            }
                        } else {
                            exit('Template is empty!');
                        }
                    } else {
                        exit('Template not found!');
                    }

                    if (count($recurrents) || count($cases) || count($recurrents) || count($recurrents)) {
                        $report .= '\renewcommand{\contentsname}{\mysummary}' . "\n";
                        $report .= '\tableofcontents' . "\n";
                        $report .= '\vspace{\myspaceaftersummary}' . "\n";
                        $report .= '\newpage' . "\n";
                        $report .= "\n";
                    }

                    if (count($recurrents)) {
                        $datafound = true;
                        $report .= '% Introductions' . "\n";
                        //$report .= '\newpage' . "\n";
                        $report .= '\setstretch{1.5}' . "\n";
                        foreach($recurrents as $r) {
                            $report .= '\section{'. escapeLatex($r['t_title']) .'}'. "\n";
                            $report .= '\vspace{0.5cm}' . "\n";
                            $report .= str_replace("\n", " \\\\\n", escapeLatex($r['t_report'])) . "\n";
                            $report .= '\vspace{1cm}' . "\n";
                            $report .= "\n";
                        }
                        unset($r);
                        unset($recurrents);
                    }

                    if (count($cases)) {
                        $datafound = true;
                        $extra = array();
                        $report .= '% Cases' . "\n";
                        //$report .= '\newpage' . "\n";
                        $report .= '\setstretch{1}' . "\n";
                        $report .= '\section{\mytextcases}' . "\n";
                        $report .= '\vspace{0.5cm}' . "\n";
                        $report .= '\begin{center}' . "\n";
                        $report .= '    \renewcommand{\arraystretch}{1.5}' . "\n";
                        $report .= '    \setlength{\tabcolsep}{10pt}' . "\n";
                        $report .= '    \fontsize{10}{12}\selectfont' . "\n";
                        $report .= '    \begin{longtable}{@{}p{0.15\textwidth}p{0.15\textwidth}p{0.15\textwidth}p{0.15\textwidth}p{0.1\textwidth}@{}}' . "\n";
                        $report .= '        \toprule' . "\n";
                        $report .= '        \textbf{\mytextcasesnumber} & \textbf{\mytextcasespriority} & \textbf{\mytextcasesopen} & \textbf{\mytextcasesclose} & \textbf{\mytextprodshortname} \\\\' . "\n";
                        $report .= '        \midrule' . "\n";
                        foreach($cases as $c) {
                            if (!empty($c['t_report'])) {
                                $extra[] = array(
                                    'title'=>$_report['textcase'] .' #'. $c['t_case'] .": ". $c['t_subject'],
                                    'description'=>$c['t_report'],
                                );
                            }
                            $report .= '        ';
                            $report .= escapeLatex($c['t_case']) ." & ";
                            $report .= escapeLatex($c['t_priority']) ." & ";
                            $report .= escapeLatex($c['t_open']) ." & ";
                            $report .= escapeLatex($c['t_close']) ." & ";
                            $report .= escapeLatex($c['t_production']) ." \\\\";
                            $report .= "\n";
                        }
                        unset($c);
                        unset($cases);
                        $report .= '        \bottomrule' . "\n";
                        $report .= '    \end{longtable}' . "\n";
                        $report .= '\end{center}' . "\n";
                        $report .= '\vspace{1cm}' . "\n";
                        $report .= "\n";
                        $report .= '% Cases reports' . "\n";
                        //$report .= '\newpage' . "\n";
                        $report .= '\setstretch{1.5}' . "\n";
                        foreach($extra as $e) {
                            $report .= '\section{'. escapeLatex($e['title']) .'}'. "\n";
                            $report .= '\vspace{0.5cm}' . "\n";
                            $report .= str_replace("\n", " \\\\\n", escapeLatex($e['description'])) . "\n";
                            $report .= '\vspace{1cm}' . "\n";
                            $report .= "\n";
                        }
                        unset($e);
                        unset($extra);
                    }

                    if (count($abstracts)) {
                        $datafound = true;
                        $report .= '% Abstracts' . "\n";
                        //$report .= '\newpage' . "\n";
                        $report .= '\setstretch{1.5}' . "\n";
                        foreach($abstracts as $a) {
                            $report .= '\section{'. escapeLatex($a['t_title']) .'}'. "\n";
                            $report .= '\vspace{0.5cm}' . "\n";
                            $report .= str_replace("\n", " \\\\\n", escapeLatex($a['t_report'])) . "\n";
                            $report .= '\vspace{1cm}' . "\n";
                            $report .= "\n";
                        }
                        unset($a);
                        unset($abstracts);
                    }

                    if (count($activities)) {
                        $datafound = true;
                        $report .= '% Activities' . "\n";
                        //$report .= '\newpage' . "\n";
                        $report .= '\setstretch{1}' . "\n";
                        $report .= '\section{\mytextactivity}' . "\n";
                        $report .= '\vspace{0.5cm}' . "\n";
                        $report .= '\begin{center}' . "\n";
                        $report .= '    \renewcommand{\arraystretch}{1.5}' . "\n";
                        $report .= '    \setlength{\tabcolsep}{10pt}' . "\n";
                        $report .= '    \fontsize{10}{12}\selectfont' . "\n";
                        $report .= '    \begin{longtable}{@{}p{0.2\textwidth}p{0.3\textwidth}p{0.2\textwidth}p{0.2\textwidth}@{}}' . "\n";
                        $report .= '        \toprule' . "\n";
                        $report .= '        \textbf{\mytextactivityenvironment} & \textbf{\mytextactivitydescription} & \textbf{\mytextactivitydate} & \textbf{\mytextactivitystatus} \\\\' . "\n";
                        $report .= '        \midrule' . "\n";
                        foreach($activities as $a) {
                            $report .= '        ';
                            $report .= escapeLatex($a['t_environment']) ." & ";
                            $report .= escapeLatex($a['t_title']) ." & ";
                            $report .= escapeLatex($a['t_schedule']) ." & ";
                            $report .= escapeLatex($a['t_status']) ." \\\\";
                            $report .= "\n";
                        }
                        unset($a);
                        unset($activities);
                        $report .= '        \bottomrule' . "\n";
                        $report .= '    \end{longtable}' . "\n";
                        $report .= '\end{center}' . "\n";
                        //$report .= '\vspace{1cm}' . "\n";
                        $report .= "\n";
                    }

                    if (!isset($datafound)) {
                        //$report .= '\vspace{4cm}' . "\n";
                        $report .= '\noindent' . "\n";
                        $report .= '\textcolor{myheadercolor}{Data not found!}' . "\n";
                        $report .= "\n";
                    }

                    $report .= '\end{document}' . "\n";

                    $filetex = $v['folder'] ."/". $v['id'] .".tex";
                    $filepdf = $diroutput . $v['id'] .".pdf";
                    $filesave = $dirsave . $v['id'] .".pdf";
                    if (is_file($filename)) {
                        $content = file_get_contents($filename);
                        if (empty($content)) {
                            file_put_contents($filename, $report, FILE_APPEND);
                            rename($filename, $filetex);
                            sleep(1);
                            echo "[Script Reports] - ". date('Y-m-d H:i:s') ." - Latex file created: ". $v['id'] .".tex\n";
                        }
                        if (is_file($filetex)) {
                            echo "[Script Reports] - ". date('Y-m-d H:i:s') ." - Generating PDF: ". $v['id'] .".pdf\n";
                            exec('/usr/bin/latexmk -xelatex -quiet --output-directory='. $diroutput .' '. $filetex .' > /dev/null 2>&1', $output, $status);
                            sleep(1);
                            unlink($filetex);
                        }
                        if (is_file($filepdf)) {
                            if (rename($filepdf, $filesave)) {
                                echo "[Script Reports] - ". date('Y-m-d H:i:s') ." - PDF saved!\n";
                            } else {
                                echo "[Script Reports] - ". date('Y-m-d H:i:s') ." - Failed to save PDF!\n"; 
                            }
                        } else {
                            echo "[Script Reports] - ". date('Y-m-d H:i:s') ." - PDF not found!\n"; 
                        }
                    }

                break;
            }
        }
    }

    $files = scandir($diroutput);
    echo "[Script Reports] - ". date('Y-m-d H:i:s') ." - Removing temporary files.\n";
    foreach($files as $f) {
        if (!empty($f)) {
            if ($f != '.' && $f != '..') {
                unlink($diroutput . $f);
            }
        }
    }
    echo "[Script Reports] - ". date('Y-m-d H:i:s') ." - Finished!\n";
    
} else {
    //echo "[Script Reports] - ". date('Y-m-d H:i:s') ." - Nothing to do!\n";
}

require __DIR__ .'/../etc/conn_close.php';
