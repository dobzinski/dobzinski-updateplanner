<?php

require __DIR__ .'/../etc/config.php';
require __DIR__ .'/../etc/function.php';
require __DIR__ .'/../etc/conn_open.php';
require __DIR__ .'/../etc/var.php';

$resources = sqlSelect(
    "SELECT
        r.id_resource AS id,
        r.tx_resource AS t_resource,
        r.tx_url AS t_url,
        DATE_FORMAT(r.dt_eof, '%Y%m%d') AS t_eof,
        v.tx_version AS t_version
    FROM
        tb_resource r LEFT JOIN tb_resource_version v ON (v.id_resource = r.id_resource 
            AND v.id_resource_version = (SELECT MAX(v2.id_resource_version) FROM tb_resource_version v2 WHERE v2.id_resource = r.id_resource))
    WHERE
        r.fl_active = ?
    ORDER BY
        t_resource ASC",
    array('Y')
);

if ($_defaults['checklatest']) {
    if (count($resources)) {
        foreach ($resources as $v) {
            if (!empty($v)) {
                //curl -i https://github.com/{owner}/{repo}/releases/latest
                if (strpos($v['t_url'], 'https://github.com') === 0) {
                    if (empty($v['t_eof']) || intval($v['t_eof']) >= intval(date('Ymd'))) {
                        $latest = getLatestVersion('github', $v['t_url']);
                        if (!empty($latest)) {
                            if ($v['t_version'] != $latest) {
                                $check = sqlGet('tb_resource_version',
                                    array(
                                        'id'=>'id_resource_version',
                                    ),
                                    array('tx_version'=>$latest)
                                );
                                if (empty($check['id'])) {
                                    $data = array(
                                        'id_resource'=>$v['id'],
                                        'tx_version'=>$latest,
                                    );
                                    if (!$result = sqlInsert('tb_resource_version', $data)) {
                                        echo "[Script Version] - ". date('Y-m-d H:i:s') ." - Inserted in resource: ". $v['t_resource'] ." - version: ". $latest ."\n"; 
                                    } else {
                                        echo "[Script Version] - ". date('Y-m-d H:i:s') ." - Error inserting resource: ". $v['t_resource'] ." - version: ". $latest ."\n"; 
                                    }
                                }
                            //} else {
                                //debug
                                //echo "[Script Version] - ". date('Y-m-d H:i:s') ." - Alert for resource: ". $v['t_resource'] ." - same version: ". $latest ."\n"; 
                            }
                        }
                    } else {
                        echo "[Script Version] - ". date('Y-m-d H:i:s') ." - Alert EOF for resource: ". $v['t_resource'] .", no more verification version!\n"; 
                    }                   
                }
            }
        }
    }
}

require __DIR__ .'/../etc/conn_close.php';