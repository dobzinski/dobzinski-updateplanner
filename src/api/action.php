<?php

require '../etc/config.php';
require '../etc/session.php';
require '../etc/function.php';
require '../etc/conn_open.php';
require '../etc/var.php';
require '../etc/permission.php';

if (isset($_REQUEST['collection'])) {
    if ($_REQUEST['collection'] != 'logout') {
        switch($_REQUEST['collection']) {
            case 'planning':
                $title = $_menuitems['planning'];
                $collection = 'planning';
                $table = 'tb_calendar';
                $key = 'id_calendar';
            break;
            case 'activities':
                $title = $_menuitems['activities'];
                $collection = 'activities';
                $table = 'tb_calendar_item';
                $key = 'id_calendar_item';
            break;
            case 'environments':
                $title = $_menuitems['environments'];
                $collection = 'environments';
                $table = 'tb_environment';
                $key = 'id_environment';
            break;
            case 'platforms':
                $title = $_menuitems['platforms'];
                $collection = 'platforms';
                $table = 'tb_platform';
                $key = 'id_platform';
            break;
            case 'kubernetes':
                $title = $_menuitems['kubernetes'];
                $collection = 'kubernetes';
                $table = 'tb_k8s';
                $key = 'id_k8s';
            break;
            case 'clouds':
                $title = $_menuitems['clouds'];
                $collection = 'clouds';
                $table = 'tb_cloud';
                $key = 'id_cloud';
            break;
            case 'clusters':
                $title = $_menuitems['clusters'];
                $collection = 'clusters';
                $table = 'tb_cluster';
                $key = 'id_cluster';
            break;
            case 'resources':
                $title = $_menuitems['resources'];
                $collection = 'resources';
                $table = 'tb_resource';
                $key = 'id_resource';
            break;
            case 'versions':
                $title = $_menuitems['versions'];
                $collection = 'versions';
                $table = 'tb_resource_version';
                $key = 'id_resource_version';
            break;
            case 'products':
                $title = $_menuitems['products'];
                $collection = 'products';
                $table = 'tb_product';
                $key = 'id_product';
            break;
            case 'cases':
                $title = $_menuitems['cases'];
                $collection = 'cases';
                $table = 'tb_case';
                $key = 'id_case';
            break;
            case 'recurrents':
                $title = $_menuitems['recurrents'];
                $collection = 'recurrents';
                $table = 'tb_recurrent';
                $key = 'id_recurrent';
            break;
            case 'abstracts':
                $title = $_menuitems['abstracts'];
                $collection = 'abstracts';
                $table = 'tb_calendar_report';
                $key = 'id_calendar_report';
            break;
            case 'reports':
                $title = $_menuitems['reports'];
                $collection = 'reports';
            break;
            case 'users':
                $title = $_menuitems['users'];
                $collection = 'users';
                $table = 'tb_user';
                $key = 'id_user';
            break;
            case 'profile':
                $title = $_menuitems['profile'];
                $collection = 'profile';
                $table = 'tb_user';
                $key = 'id_user';
            break;
            case 'password':
                $title = $_menuitems['password'];
                $collection = 'password';
                $table = 'tb_user';
                $key = 'id_user';
            break;
            default:
                $title = $_menuitems['dashboard'];
                $collection = 'dashboard';
        }
        $id = '';
        $tips = array(
            'password'=>'Follow the rules:<ul><li>At least eight characters long</li><li>At least one upper case</li><li>At least one lower case</li><li>At least one number</li><li>At least one special character (#?!@$%^&*-)</li></ul>',
        );
    } else {
        $_SESSION = array();
        unset($_SESSION);
        htmlJs('window.location.reload(true);');
    }
}

if (isset($_SESSION['user'])) {
    if (isset($_POST['action'])) {
        switch($collection) {
            case 'planning':
                if ($_POST['action'] == 'submit') {
                    if (!empty($_POST['v-title']) && !empty($_POST['v-schedule']) && !empty($_POST['v-version'])
                        && ($_POST['v-type'] == "0" || ($_POST['v-type'] == "1" && !empty($_POST['v-cluster'])))) {
                        if ($_POST['v-type'] == 1) {
                            $post = array(
                                'tx_title'=>$_POST['v-title'],
                                'dt_schedule'=>$_POST['v-schedule'],
                                'id_resource_version'=>$_POST['v-version'],
                                'id_calendar_depends'=>(!empty($_POST['v-depends']) ? $_POST['v-depends'] : NULL),
                                'id_cluster'=>(!empty($_POST['v-cluster']) ? $_POST['v-cluster'] : NULL),
                                'id_environment'=>NULL,
                                'tx_description'=>(!empty($_POST['v-description']) ? $_POST['v-description'] : NULL),
                                'dt_complete'=>(!empty($_POST['v-complete']) ? $_POST['v-complete'] : NULL),
                                'fl_public'=>($_POST['v-public'] == 1 ? 'Y' : 'N'),
                            );
                        } else {
                            $post = array(
                                'tx_title'=>$_POST['v-title'],
                                'dt_schedule'=>$_POST['v-schedule'],
                                'id_resource_version'=>$_POST['v-version'],
                                'id_calendar_depends'=>(!empty($_POST['v-depends']) ? $_POST['v-depends'] : NULL),
                                'id_cluster'=>NULL,
                                'id_environment'=>(!empty($_POST['v-environment']) ? $_POST['v-environment'] : NULL),
                                'tx_description'=>(!empty($_POST['v-description']) ? $_POST['v-description'] : NULL),
                                'dt_complete'=>(!empty($_POST['v-complete']) ? $_POST['v-complete'] : NULL),
                                'fl_public'=>($_POST['v-public'] == 1 ? 'Y' : 'N'),
                            );
                        }
                        if(!checkDateTime($_POST['v-schedule'], 'Y-m-d H:i')) {
                            $_message['type'] = 'danger';
                            $_message['message'] = $_alert['datetimeinvalid'] .": Schedule";
                        }
                        if (!isset($_message['type']) && !empty($_POST['v-complete'])) {
                            if(!checkDateTime($_POST['v-complete'], 'Y-m-d H:i')) {
                                $_message['type'] = 'danger';
                                $_message['message'] = $_alert['datetimeinvalid'] .": Completed ";
                            }
                            if (!isset($_message['type'])) {
                                if(controlDateTimeToInt($_POST['v-schedule']) >= controlDateTimeToInt($_POST['v-complete'])) {
                                    $_message['type'] = 'danger';
                                    $_message['message'] = $_alert['datesbetweeninvalid'];
                                }
                            }
                            if (!isset($_message['type']) && !empty($_POST['v-id'])) {
                                $check_activity = sqlGet("tb_calendar_item i JOIN tb_calendar c ON(i.id_calendar = c.id_calendar
                                        AND DATE_FORMAT(i.dt_end, '%Y-%m-%d %H:%i') = (SELECT MAX(DATE_FORMAT(i2.dt_end, '%Y-%m-%d %H:%i')) FROM tb_calendar_item i2 WHERE i2.id_calendar = c.id_calendar))",
                                    array(
                                        'laststatus'=>"i.tp_status",
                                        'lastdate'=>"DATE_FORMAT(i.dt_end, '%Y-%m-%d %H:%i')",
                                    ),
                                    array('i.id_calendar'=>controlInt($_POST['v-id']))
                                );
                                if (!empty($check_activity)) {
                                    if (in_array($check_activity['laststatus'], $_statusconclusion)) {
                                        if (controlDateTimeToInt($_POST['v-complete']) < controlDateTimeToInt($check_activity['lastdate'])) {
                                            $_message['type'] = 'danger';
                                            $_message['message'] = $_alert['lowerdatelastactivity'];
                                        }
                                    } else {
                                        $_message['type'] = 'danger';
                                        $_message['message'] = $_alert['lastvalidstatusrequired'];
                                    }
                                }
                            }
                        }
                        if (!isset($_message['type'])) {
                            if (empty($_POST['v-id'])) {
                                if (!$result = sqlInsert($table, $post)) {
                                    $_message['type'] = 'success';
                                    $_message['message'] = $_alert['inserted'];
                                    $post = array();
                                    unset($post);
                                } else {
                                    $_message['type'] = 'danger';
                                    $_message['message'] = $result;
                                }
                            } else {
                                if (isset($key)) {
                                    array_push($post, controlInt($_POST['v-id']));
                                    if (!$result = sqlUpdate($table, $post, $key)) {
                                        $_message['type'] = 'success';
                                        $_message['message'] = $_alert['updated'];
                                        $post = array();
                                        unset($post);
                                    } else {
                                        $_message['type'] = 'danger';
                                        $_message['message'] = $result;
                                    }
                                }
                            }
                        }
                    } else {
                        $_message['type'] = 'danger';
                        $_message['message'] = $_alert['required'];
                    }
                } else if ($_POST['action'] == 'complete') {
                    if (!empty($_POST['v-id'])) {
                        $check_complete = sqlGet($table,
                            array(
                                'complete'=>'dt_complete',
                            ),
                            array($key=>controlInt($_POST['v-id']))
                        );
                        if (!empty($check_complete['complete'])) {
                            $check_activity = sqlGet("tb_calendar_item i JOIN tb_calendar c ON(i.id_calendar = c.id_calendar
                                    AND DATE_FORMAT(i.dt_end, '%Y-%m-%d %H:%i') = (SELECT MAX(DATE_FORMAT(i2.dt_end, '%Y-%m-%d %H:%i')) FROM tb_calendar_item i2 WHERE i2.id_calendar = c.id_calendar))",
                                array(
                                    'laststatus'=>"i.tp_status",
                                ),
                                array('i.id_calendar'=>controlInt($_POST['v-id']))
                            );
                            if (!empty($check_activity)) {
                                if (!in_array($check_activity['laststatus'], $_statusconclusion)) {
                                    $_message['type'] = 'danger';
                                    $_message['message'] = $_alert['lastvalidstatusrequired'];
                                }
                            }
                            if (!isset($_message['type'])) {
                                $post = array(
                                    'fl_complete'=>'Y',
                                    $key=>controlInt($_POST['v-id']),
                                );
                                if (!$result = sqlUpdate($table, $post, $key)) {
                                    $_message['type'] = 'success';
                                    $_message['message'] = $_alert['completed'];
                                    $post = array();
                                    unset($post);
                                } else {
                                    $_message['type'] = 'danger';
                                    $_message['message'] = $result;
                                }
                            }
                        } else {
                            $_message['type'] = 'danger';
                            $_message['message'] = $_alert['completebefore'];
                        }
                        $check_complete = array();
                        unset($check_complete);
                    }
                } else if ($_POST['action'] == 'schedule') {
                    if (!empty($_POST['v-id'])) {
                        $post = array(
                            'fl_complete'=>'N',
                            $key=>controlInt($_POST['v-id']),
                        );
                        if (!$result = sqlUpdate($table, $post, $key)) {
                            $_message['type'] = 'success';
                            $_message['message'] = $_alert['scheduled'];
                            $post = array();
                            unset($post);
                        } else {
                            $_message['type'] = 'danger';
                            $_message['message'] = $result;
                        }
                    }
                } else if ($_POST['action'] == 'delete') {
                    if (!empty($_POST['v-id'])) {
                        $post = array(
                            $key=>controlInt($_POST['v-id']),
                        );
                        if (!$result = sqlDelete($table, $post)) {
                            $_message['type'] = 'success';
                            $_message['message'] = $_alert['deleted'];
                            $post = array();
                            unset($post);
                        } else {
                            $_message['type'] = 'danger';
                            $_message['message'] = $result;
                        }
                    }
                }
                if (isset($_message['type'])) {
                    if ($_message['type'] != 'success') {
                        $id = (isset($_POST['v-id']) ? controlInt($_POST['v-id']) : "");
                        $data = array(
                            'title'=>$_POST['v-title'],
                            'schedule'=>$_POST['v-schedule'],
                            'version'=>$_POST['v-version'],
                            'depends'=>(!empty($_POST['v-depends']) ? $_POST['v-depends'] : NULL),
                            'cluster'=>(!empty($_POST['v-cluster']) ? $_POST['v-cluster'] : NULL),
                            'environment'=>(!empty($_POST['v-environment']) ? $_POST['v-environment'] : NULL),
                            'description'=>(!empty($_POST['v-description']) ? $_POST['v-description'] : NULL),
                            'type'=>($_POST['v-type'] == 1 ? 'Y' : 'N'),
                            'public'=>($_POST['v-public'] == 1 ? 'Y' : 'N'),
                            'complete'=>(!empty($_POST['v-complete']) ? $_POST['v-complete'] : NULL),
                        );
                        if ($_POST['action'] == 'submit') {
                            $accordion['form'] = false;
                            $accordion['data'] = true;
                            $accordion['calendar'] = true;
                        }
                    }
                }
            break;
            case 'activities':
                if ($_POST['action'] == 'submit') {
                    if (!empty($_POST['v-calendar']) && !empty($_POST['v-start']) && !empty($_POST['v-end']) && !empty($_POST['v-status'])) {
                        $post = array(
                            'id_Calendar'=>$_POST['v-calendar'],
                            'nu_percent'=>(!empty($_POST['v-percent']) ? $_POST['v-percent'] : 0),
                            'tx_comment'=>(!empty($_POST['v-comment']) ? $_POST['v-comment'] : NULL),
                            'dt_start'=>$_POST['v-start'],
                            'dt_end'=>$_POST['v-end'],
                            'tp_status'=>$_POST['v-status'],
                        );
                        if(!checkDateTime($_POST['v-start'], 'Y-m-d H:i')) {
                            $_message['type'] = 'danger';
                            $_message['message'] = $_alert['datetimeinvalid'] .": Start";
                        }
                        if (!isset($_message['type'])) {
                            if(!checkDateTime($_POST['v-end'], 'Y-m-d H:i')) {
                                $_message['type'] = 'danger';
                                $_message['message'] = $_alert['datetimeinvalid'] .": End";
                            }
                            if (!isset($_message['type'])) {
                                if(controlDateTimeToInt($_POST['v-start']) >= controlDateTimeToInt($_POST['v-end'])) {
                                    $_message['type'] = 'danger';
                                    $_message['message'] = $_alert['datesbetweeninvalid'];
                                }
                            }
                        }
                        if (!isset($_message['type'])) {
                            if (empty($_POST['v-id'])) {
                                if (!$result = sqlInsert($table, $post)) {
                                    $_message['type'] = 'success';
                                    $_message['message'] = $_alert['inserted'];
                                    $post = array();
                                    unset($post);
                                } else {
                                    $_message['type'] = 'danger';
                                    $_message['message'] = $result;
                                }
                            } else {
                                if (isset($key)) {
                                    array_push($post, controlInt($_POST['v-id']));
                                    if (!$result = sqlUpdate($table, $post, $key)) {
                                        $_message['type'] = 'success';
                                        $_message['message'] = $_alert['updated'];
                                        $post = array();
                                        unset($post);
                                    } else {
                                        $_message['type'] = 'danger';
                                        $_message['message'] = $result;
                                    }
                                }
                            }
                        }
                    } else {
                        $_message['type'] = 'danger';
                        $_message['message'] = $_alert['required'];
                    }
                } else if ($_POST['action'] == 'delete') {
                    if (!empty($_POST['v-id'])) {
                        $post = array(
                            $key=>controlInt($_POST['v-id']),
                        );
                        if (!$result = sqlDelete($table, $post)) {
                            $_message['type'] = 'success';
                            $_message['message'] = $_alert['deleted'];
                            $post = array();
                            unset($post);
                        } else {
                            $_message['type'] = 'danger';
                            $_message['message'] = $result;
                        }
                    }
                }
                if (isset($_message['type'])) {
                    if ($_message['type'] != 'success') {
                        $id = (isset($_POST['v-id']) ? controlInt($_POST['v-id']) : "");
                        $data = array(
                            'calendar'=>$_POST['v-calendar'],
                            'percent'=>(!empty($_POST['v-percent']) ? $_POST['v-percent'] : NULL),
                            'comment'=>(!empty($_POST['v-comment']) ? $_POST['v-comment'] : NULL),
                            'start'=>$_POST['v-start'],
                            'end'=>$_POST['v-end'],
                            'status'=>$_POST['v-status'],
                        );
                        if ($_POST['action'] == 'submit') {
                            $accordion['form'] = false;
                            $accordion['data'] = true;
                            $accordion['activities'] = true;
                        }
                    }
                }
            break;
            case 'environments':
                if ($_POST['action'] == 'submit') {
                    if (!empty($_POST['v-environment']) && !empty($_POST['v-color'])) {
                        $post = array(
                            'tx_environment'=>$_POST['v-environment'],
                            'tx_color'=>strtoupper(substr($_POST['v-color'], 1)),
                            'fl_production'=>($_POST['v-production'] == 1 ? 'Y' : 'N'),
                            'fl_support'=>($_POST['v-support'] == 1 ? 'Y' : 'N'),
                            'fl_active'=>($_POST['v-active'] == 1 ? 'Y' : 'N'),
                        );
                        if (empty($_POST['v-id'])) {
                            if (!$result = sqlInsert($table, $post)) {
                                $_message['type'] = 'success';
                                $_message['message'] = $_alert['inserted'];
                                $post = array();
                                unset($post);
                            } else {
                                $_message['type'] = 'danger';
                                $_message['message'] = $result;
                            }
                        } else {
                            if (isset($key)) {
                                array_push($post, controlInt($_POST['v-id']));
                                if (!$result = sqlUpdate($table, $post, $key)) {
                                    $_message['type'] = 'success';
                                    $_message['message'] = $_alert['updated'];
                                    $post = array();
                                    unset($post);
                                } else {
                                    $_message['type'] = 'danger';
                                    $_message['message'] = $result;
                                }
                            }
                        }
                    } else {
                        $_message['type'] = 'danger';
                        $_message['message'] = $_alert['required'];
                    }
                } else if ($_POST['action'] == 'delete') {
                    if (!empty($_POST['v-id'])) {
                        $post = array(
                            $key=>controlInt($_POST['v-id']),
                        );
                        if (!$result = sqlDelete($table, $post)) {
                            $_message['type'] = 'success';
                            $_message['message'] = $_alert['deleted'];
                            $post = array();
                            unset($post);
                        } else {
                            $_message['type'] = 'danger';
                            $_message['message'] = $result;
                        }
                    }
                }
                if (isset($_message['type'])) {
                    if ($_message['type'] != 'success') {
                        $id = (isset($_POST['v-id']) ? controlInt($_POST['v-id']) : "");
                        $data = array(
                            'environment'=>$_POST['v-environment'],
                            'color'=>$_POST['v-color'],
                            'production'=>($_POST['v-production'] == 1 ? 'Y' : 'N'),
                            'support'=>($_POST['v-support'] == 1 ? 'Y' : 'N'),
                            'active'=>($_POST['v-active'] == 1 ? 'Y' : 'N'),
                        );
                        if ($_POST['action'] == 'submit') {
                            $accordion['form'] = false;
                            $accordion['data'] = true;
                        }
                    }
                }
            break;
            case 'platforms':
                if ($_POST['action'] == 'submit') {
                    if (!empty($_POST['v-platform'])) {
                        $post = array(
                            'tx_platform'=>$_POST['v-platform'],
                            'fl_active'=>($_POST['v-active'] == 1 ? 'Y' : 'N'),
                        );
                        if (empty($_POST['v-id'])) {
                            if (!$result = sqlInsert($table, $post)) {
                                $_message['type'] = 'success';
                                $_message['message'] = $_alert['inserted'];
                                $post = array();
                                unset($post);
                            } else {
                                $_message['type'] = 'danger';
                                $_message['message'] = $result;
                            }
                        } else {
                            if (isset($key)) {
                                array_push($post, controlInt($_POST['v-id']));
                                if (!$result = sqlUpdate($table, $post, $key)) {
                                    $_message['type'] = 'success';
                                    $_message['message'] = $_alert['updated'];
                                    $post = array();
                                    unset($post);
                                } else {
                                    $_message['type'] = 'danger';
                                    $_message['message'] = $result;
                                }
                            }
                        }
                    } else {
                        $_message['type'] = 'danger';
                        $_message['message'] = $_alert['required'];
                    }
                } else if ($_POST['action'] == 'delete') {
                    if (!empty($_POST['v-id'])) {
                        $post = array(
                            $key=>controlInt($_POST['v-id']),
                        );
                        if (!$result = sqlDelete($table, $post)) {
                            $_message['type'] = 'success';
                            $_message['message'] = $_alert['deleted'];
                            $post = array();
                            unset($post);
                        } else {
                            $_message['type'] = 'danger';
                            $_message['message'] = $result;
                        }
                    }
                }
                if (isset($_message['type'])) {
                    if ($_message['type'] != 'success') {
                        $id = (isset($_POST['v-id']) ? controlInt($_POST['v-id']) : "");
                        $data = array(
                            'platform'=>$_POST['v-platform'],
                            'active'=>($_POST['v-active'] == 1 ? 'Y' : 'N'),
                        );
                        if ($_POST['action'] == 'submit') {
                            $accordion['form'] = false;
                            $accordion['data'] = true;
                        }
                    }
                }
            break;
            case 'kubernetes':
                if ($_POST['action'] == 'submit') {
                    if (!empty($_POST['v-kubernetes'])) {
                        $post = array(
                            'tx_k8s'=>$_POST['v-kubernetes'],
                            'fl_active'=>($_POST['v-active'] == 1 ? 'Y' : 'N'),
                        );
                        if (empty($_POST['v-id'])) {
                            if (!$result = sqlInsert($table, $post)) {
                                $_message['type'] = 'success';
                                $_message['message'] = $_alert['inserted'];
                                $post = array();
                                unset($post);
                            } else {
                                $_message['type'] = 'danger';
                                $_message['message'] = $result;
                            }
                        } else {
                            if (isset($key)) {
                                array_push($post, controlInt($_POST['v-id']));
                                if (!$result = sqlUpdate($table, $post, $key)) {
                                    $_message['type'] = 'success';
                                    $_message['message'] = $_alert['updated'];
                                    $post = array();
                                    unset($post);
                                } else {
                                    $_message['type'] = 'danger';
                                    $_message['message'] = $result;
                                }
                            }
                        }
                    } else {
                        $_message['type'] = 'danger';
                        $_message['message'] = $_alert['required'];
                    }
                } else if ($_POST['action'] == 'delete') {
                    if (!empty($_POST['v-id'])) {
                        $post = array(
                            $key=>controlInt($_POST['v-id']),
                        );
                        if (!$result = sqlDelete($table, $post)) {
                            $_message['type'] = 'success';
                            $_message['message'] = $_alert['deleted'];
                            $post = array();
                            unset($post);
                        } else {
                            $_message['type'] = 'danger';
                            $_message['message'] = $result;
                        }
                    }
                }
                if (isset($_message['type'])) {
                    if ($_message['type'] != 'success') {
                        $id = (isset($_POST['v-id']) ? controlInt($_POST['v-id']) : "");
                        $data = array(
                            'kubernetes'=>$_POST['v-kubernetes'],
                            'active'=>($_POST['v-active'] == 1 ? 'Y' : 'N'),
                        );
                        if ($_POST['action'] == 'submit') {
                            $accordion['form'] = false;
                            $accordion['data'] = true;
                        }
                    }
                }
            break;
            case 'clouds':
                if ($_POST['action'] == 'submit') {
                    if (!empty($_POST['v-cloud'])) {
                        $post = array(
                            'tx_cloud'=>$_POST['v-cloud'],
                            'fl_active'=>($_POST['v-active'] == 1 ? 'Y' : 'N'),
                        );
                        if (empty($_POST['v-id'])) {
                            if (!$result = sqlInsert($table, $post)) {
                                $_message['type'] = 'success';
                                $_message['message'] = $_alert['inserted'];
                                $post = array();
                                unset($post);
                            } else {
                                $_message['type'] = 'danger';
                                $_message['message'] = $result;
                            }
                        } else {
                            if (isset($key)) {
                                array_push($post, controlInt($_POST['v-id']));
                                if (!$result = sqlUpdate($table, $post, $key)) {
                                    $_message['type'] = 'success';
                                    $_message['message'] = $_alert['updated'];
                                    $post = array();
                                    unset($post);
                                } else {
                                    $_message['type'] = 'danger';
                                    $_message['message'] = $result;
                                }
                            }
                        }
                    } else {
                        $_message['type'] = 'danger';
                        $_message['message'] = $_alert['required'];
                    }
                } else if ($_POST['action'] == 'delete') {
                    if (!empty($_POST['v-id'])) {
                        $post = array(
                            $key=>controlInt($_POST['v-id']),
                        );
                        if (!$result = sqlDelete($table, $post)) {
                            $_message['type'] = 'success';
                            $_message['message'] = $_alert['deleted'];
                            $post = array();
                            unset($post);
                        } else {
                            $_message['type'] = 'danger';
                            $_message['message'] = $result;
                        }
                    }
                }
                if (isset($_message['type'])) {
                    if ($_message['type'] != 'success') {
                        $id = (isset($_POST['v-id']) ? controlInt($_POST['v-id']) : "");
                        $data = array(
                            'cloud'=>$_POST['v-cloud'],
                            'active'=>($_POST['v-active'] == 1 ? 'Y' : 'N'),
                        );
                        if ($_POST['action'] == 'submit') {
                            $accordion['form'] = false;
                            $accordion['data'] = true;
                        }
                    }
                }
            break;
            case 'resources':
                if ($_POST['action'] == 'submit') {
                    if (!empty($_POST['v-resource'])) {
                        $post = array(
                            'tx_resource'=>$_POST['v-resource'],
                            'tx_url'=>(!empty($_POST['v-url']) ? $_POST['v-url'] : NULL),
                            'dt_eol'=>(!empty($_POST['v-eol']) ? $_POST['v-eol'] : NULL),
                            'dt_eom'=>(!empty($_POST['v-eom']) ? $_POST['v-eom'] : NULL),
                            'fl_script'=>'N',
                            'fl_active'=>($_POST['v-active'] == 1 ? 'Y' : 'N'),
                        );
                        if (!empty($_POST['v-url'])) {
                            if(!checkSpecialChars($_POST['v-url'], $_regex['checkhttp'])) {
                                $_message['type'] = 'danger';
                                $_message['message'] = $_alert['invalidhttp'];
                            }
                        }
                        if (!isset($_message['type']) && !empty($_POST['v-eol'])) {
                            if(!checkDateTime($_POST['v-eol'], 'Y-m-d')) {
                                $_message['type'] = 'danger';
                                $_message['message'] = $_alert['dateinvalid'] .": EOL";
                            }
                        }
                        if (!isset($_message['type']) && !empty($_POST['v-eom'])) {
                            if(!checkDateTime($_POST['v-eom'], 'Y-m-d')) {
                                $_message['type'] = 'danger';
                                $_message['message'] = $_alert['dateinvalid'] .": EOM";
                            }
                        }
                        if (!isset($_message['type'])) {
                            if (empty($_POST['v-id'])) {
                                if (!$result = sqlInsert($table, $post)) {
                                    $_message['type'] = 'success';
                                    $_message['message'] = $_alert['inserted'];
                                    $post = array();
                                    unset($post);
                                } else {
                                    $_message['type'] = 'danger';
                                    $_message['message'] = $result;
                                }
                            } else {
                                if (isset($key)) {
                                    array_push($post, controlInt($_POST['v-id']));
                                    if (!$result = sqlUpdate($table, $post, $key)) {
                                        $_message['type'] = 'success';
                                        $_message['message'] = $_alert['updated'];
                                        $post = array();
                                        unset($post);
                                    } else {
                                        $_message['type'] = 'danger';
                                        $_message['message'] = $result;
                                    }
                                }
                            }
                        }
                    } else {
                        $_message['type'] = 'danger';
                        $_message['message'] = $_alert['required'];
                    }
                } else if ($_POST['action'] == 'delete') {
                    if (!empty($_POST['v-id'])) {
                        $post = array(
                            $key=>controlInt($_POST['v-id']),
                        );
                        if (!$result = sqlDelete($table, $post)) {
                            $_message['type'] = 'success';
                            $_message['message'] = $_alert['deleted'];
                            $post = array();
                            unset($post);
                        } else {
                            $_message['type'] = 'danger';
                            $_message['message'] = $result;
                        }
                    }
                }
                if (isset($_message['type'])) {
                    if ($_message['type'] != 'success') {
                        $id = (isset($_POST['v-id']) ? controlInt($_POST['v-id']) : "");
                        $data = array(
                            'resource'=>$_POST['v-resource'],
                            'url'=>(!empty($_POST['v-url']) ? $_POST['v-url'] : NULL),
                            'eol'=>(!empty($_POST['v-eol']) ? $_POST['v-eol'] : NULL),
                            'eom'=>(!empty($_POST['v-eom']) ? $_POST['v-eom'] : NULL),
                            'active'=>($_POST['v-active'] == 1 ? 'Y' : 'N'),
                        );
                        if ($_POST['action'] == 'submit') {
                            $accordion['form'] = false;
                            $accordion['data'] = true;
                        }
                    }
                }
            break;
            case 'versions':
                if ($_POST['action'] == 'submit') {
                    if (!empty($_POST['v-resource']) && !empty($_POST['v-version'])) {
                        $post = array(
                            'id_resource'=>$_POST['v-resource'],
                            'tx_version'=>$_POST['v-version'],
                            'fl_script'=>'N',
                            'fl_active'=>($_POST['v-active'] == 1 ? 'Y' : 'N'),
                        );
                        if (empty($_POST['v-id'])) {
                            if (!$result = sqlInsert($table, $post)) {
                                $_message['type'] = 'success';
                                $_message['message'] = $_alert['inserted'];
                                $post = array();
                                unset($post);
                            } else {
                                $_message['type'] = 'danger';
                                $_message['message'] = $result;
                            }
                        } else {
                            if (isset($key)) {
                                array_push($post, controlInt($_POST['v-id']));
                                if (!$result = sqlUpdate($table, $post, $key)) {
                                    $_message['type'] = 'success';
                                    $_message['message'] = $_alert['updated'];
                                    $post = array();
                                    unset($post);
                                } else {
                                    $_message['type'] = 'danger';
                                    $_message['message'] = $result;
                                }
                            }
                        }
                    } else {
                        $_message['type'] = 'danger';
                        $_message['message'] = $_alert['required'];
                    }
                } else if ($_POST['action'] == 'delete') {
                    if (!empty($_POST['v-id'])) {
                        $post = array(
                            $key=>controlInt($_POST['v-id']),
                        );
                        if (!$result = sqlDelete($table, $post)) {
                            $_message['type'] = 'success';
                            $_message['message'] = $_alert['deleted'];
                            $post = array();
                            unset($post);
                        } else {
                            $_message['type'] = 'danger';
                            $_message['message'] = $result;
                        }
                    }
                }
                if (isset($_message['type'])) {
                    if ($_message['type'] != 'success') {
                        $id = (isset($_POST['v-id']) ? controlInt($_POST['v-id']) : "");
                        $data = array(
                            'resource'=>$_POST['v-resource'],
                            'version'=>$_POST['v-version'],
                            'active'=>($_POST['v-active'] == 1 ? 'Y' : 'N'),
                        );
                        if ($_POST['action'] == 'submit') {
                            $accordion['form'] = false;
                            $accordion['data'] = true;
                        }
                    }
                }
            break;
            case 'users':
                if ($_POST['action'] == 'submit') {
                    if (!$_POST['v-ldap']) {
                        if (!empty($_POST['v-login']) && !empty($_POST['v-fullname']) && !empty($_POST['v-role'])) {
                            $post = array(
                                'tx_login'=>controlSpecialChars($_POST['v-login'], $_regex['controllogin']),
                                'tx_fullname'=>$_POST['v-fullname'],
                                'tx_email'=>(!empty($_POST['v-email']) ? $_POST['v-email'] : NULL),
                                'tp_role'=>$_POST['v-role'],
                                'fl_ldap'=>'N',
                                'fl_active'=>($_POST['v-active'] == 1 ? 'Y' : 'N'),
                            );
                            if (!empty($_POST['v-email'])) {
                                if(!checkSpecialChars($_POST['v-email'], $_regex['checkemail'])) {
                                    $_message['type'] = 'danger';
                                    $_message['message'] = $_alert['invalidemail'];
                                }
                            }
                            if (!isset($_message['type'])) {
                                if (empty($_POST['v-id'])) {
                                    if (!empty($_POST['v-password']) && !empty($_POST['v-confirm'])) {
                                        if (checkSpecialChars($_POST['v-password'], $_regex['checkpassword']) == true) {
                                            $password = controlSpecialChars($_POST['v-password'], $_regex['controlpassword']);
                                            $confirm = controlSpecialChars($_POST['v-confirm'], $_regex['controlpassword']);
                                            if ($password == $confirm) {
                                                $post = array_merge($post, array('tx_password'=>md5($password)));
                                            } else {
                                                $_message['type'] = 'danger';
                                                $_message['message'] = $_alert['passworddoesnotmatch'];
                                            }
                                        } else {
                                            $_message['type'] = 'danger';
                                            $_message['message'] = $_alert['passworddoesnotcharsrequired'];
                                        }
                                    } else {
                                        $_message['type'] = 'danger';
                                        $_message['message'] = $_alert['passwordconfirmrequired'];
                                    }
                                    if (!isset($_message['type'])) {
                                        if (!$result = sqlInsert($table, $post)) {
                                            $_message['type'] = 'success';
                                            $_message['message'] = $_alert['inserted'];
                                            $post = array();
                                            unset($post);
                                        } else {
                                            $_message['type'] = 'danger';
                                            $_message['message'] = $result;
                                        }
                                    }
                                } else {
                                    if (isset($key)) {
                                        if ($_POST['v-updatepassword'] == 1) {
                                            if (!empty($_POST['v-password']) && !empty($_POST['v-confirm'])) {
                                                if (checkSpecialChars($_POST['v-password'], $_regex['checkpassword']) == true) {
                                                    $password = controlSpecialChars($_POST['v-password'], $_regex['controlpassword']);
                                                    $confirm = controlSpecialChars($_POST['v-confirm'], $_regex['controlpassword']);
                                                    if ($password == $confirm) {
                                                        $post = array_merge($post, array('tx_password'=>md5($password)));
                                                    } else {
                                                        $_message['type'] = 'danger';
                                                        $_message['message'] = $_alert['passworddoesnotmatch'];
                                                    }
                                                } else {
                                                    $_message['type'] = 'danger';
                                                    $_message['message'] = $_alert['passworddoesnotcharsrequired'];
                                                }
                                            } else {
                                                $_message['type'] = 'danger';
                                                $_message['message'] = $_alert['passwordconfirmrequired'];
                                            }
                                        }
                                        if (!isset($_message['type'])) {
                                            array_push($post, controlInt($_POST['v-id']));
                                            if (!$result = sqlUpdate($table, $post, $key)) {
                                                $_message['type'] = 'success';
                                                $_message['message'] = $_alert['updated'];
                                                $post = array();
                                                unset($post);
                                            } else {
                                                $_message['type'] = 'danger';
                                                $_message['message'] = $result;
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            $_message['type'] = 'danger';
                            $_message['message'] = $_alert['required'];
                        }
                    } else {
                        if (!empty($_POST['v-login']) && !empty($_POST['v-role'])) {
                            $login = controlSpecialChars($_POST['v-login'], $_regex['controllogin']);
                            $ldapuser = ldapSearchAuth($login);
                            if (!empty($ldapuser)) {
                                $post = array(
                                    'tx_login'=>controlSpecialChars($ldapuser['login'], $_regex['controllogin']),
                                    'tx_fullname'=>$ldapuser['fullname'],
                                    'tx_email'=>(!empty($ldapuser['mail']) ? $ldapuser['mail'] : NULL),
                                    'tp_role'=>$_POST['v-role'],
                                    'fl_ldap'=>'Y',
                                    'fl_active'=>($_POST['v-active'] == 1 ? 'Y' : 'N'),
                                );
                                if (empty($_POST['v-id'])) {
                                    if (!$result = sqlInsert($table, $post)) {
                                        $_message['type'] = 'success';
                                        $_message['message'] = $_alert['inserted'];
                                        $post = array();
                                        unset($post);
                                    } else {
                                        $_message['type'] = 'danger';
                                        $_message['message'] = $result;
                                    }
                                } else {
                                    if (isset($key)) {
                                        array_push($post, controlInt($_POST['v-id']));
                                        if (!$result = sqlUpdate($table, $post, $key)) {
                                            $_message['type'] = 'success';
                                            $_message['message'] = $_alert['updated'];
                                            $post = array();
                                            unset($post);
                                        } else {
                                            $_message['type'] = 'danger';
                                            $_message['message'] = $result;
                                        }
                                    }
                                }
                            } else {
                                $_message['type'] = 'danger';
                                $_message['message'] = $_alert['usernotfound'];
                            }
                        } else {
                            $_message['type'] = 'danger';
                            $_message['message'] = $_alert['required'];
                        }
                    }
                } else if ($_POST['action'] == 'delete') {
                    if (!empty($_POST['v-id'])) {
                        $post = array(
                            $key=>controlInt($_POST['v-id']),
                        );
                        if (!$result = sqlDelete($table, $post)) {
                            $_message['type'] = 'success';
                            $_message['message'] = $_alert['deleted'];
                            $post = array();
                            unset($post);
                        } else {
                            $_message['type'] = 'danger';
                            $_message['message'] = $result;
                        }
                    }
                }
                if (isset($_message['type'])) {
                    if ($_message['type'] != 'success') {
                        $id = (isset($_POST['v-id']) ? controlInt($_POST['v-id']) : "");
                        $data = array(
                            'role'=>$_POST['v-role'],
                            'login'=>$_POST['v-login'],
                            'fullname'=>$_POST['v-fullname'],
                            'email'=>(!empty($_POST['v-email']) ? $_POST['v-email'] : NULL),
                            'ldap'=>($_POST['v-ldap'] == 1 ? 'Y' : 'N'),
                            'active'=>($_POST['v-active'] == 1 ? 'Y' : 'N'),
                            'updatepassword'=>(isset($_POST['v-updatepassword']) ? ($_POST['v-updatepassword'] == 1 ? 'Y' : 'N') : 'N'),
                        );
                        if ($_POST['action'] == 'submit') {
                            $accordion['form'] = false;
                            $accordion['data'] = true;
                        }
                    }
                }
            break;
            case 'clusters':
                if ($_POST['action'] == 'submit') {
                    if (!empty($_POST['v-environment']) && !empty($_POST['v-platform']) && !empty($_POST['v-kubernetes']) && !empty($_POST['v-cluster']) && !empty($_POST['v-node'])) {
                        $post = array(
                            'id_environment'=>$_POST['v-environment'],
                            'id_platform'=>$_POST['v-platform'],
                            'id_k8s'=>$_POST['v-kubernetes'],
                            'id_cloud'=>(!empty($_POST['v-cloud']) ? $_POST['v-cloud'] : NULL),
                            'tx_cluster'=>$_POST['v-cluster'],
                            'nu_node'=>$_POST['v-node'],
                            'fl_downstream'=>($_POST['v-downstream'] == 1 ? 'Y' : 'N'),
                            'fl_active'=>($_POST['v-active'] == 1 ? 'Y' : 'N'),
                        );
                        if (empty($_POST['v-id'])) {
                            if (!$result = sqlInsert($table, $post)) {
                                $_message['type'] = 'success';
                                $_message['message'] = $_alert['inserted'];
                                $post = array();
                                unset($post);
                            } else {
                                $_message['type'] = 'danger';
                                $_message['message'] = $result;
                            }
                        } else {
                            if (isset($key)) {
                                array_push($post, controlInt($_POST['v-id']));
                                if (!$result = sqlUpdate($table, $post, $key)) {
                                    $_message['type'] = 'success';
                                    $_message['message'] = $_alert['updated'];
                                    $post = array();
                                    unset($post);
                                } else {
                                    $_message['type'] = 'danger';
                                    $_message['message'] = $result;
                                }
                            }
                        }
                    } else {
                        $_message['type'] = 'danger';
                        $_message['message'] = $_alert['required'];
                    }
                } else if ($_POST['action'] == 'delete') {
                    if (!empty($_POST['v-id'])) {
                        $post = array(
                            $key=>controlInt($_POST['v-id']),
                        );
                        if (!$result = sqlDelete($table, $post)) {
                            $_message['type'] = 'success';
                            $_message['message'] = $_alert['deleted'];
                            $post = array();
                            unset($post);
                        } else {
                            $_message['type'] = 'danger';
                            $_message['message'] = $result;
                        }
                    }
                }
                if (isset($_message['type'])) {
                    if ($_message['type'] != 'success') {
                        $id = (isset($_POST['v-id']) ? controlInt($_POST['v-id']) : "");
                        $data = array(
                            'cluster'=>$_POST['v-cluster'],
                            'environment'=>$_POST['v-environment'],
                            'platform'=>$_POST['v-platform'],
                            'kubernetes'=>$_POST['v-kubernetes'],
                            'cloud'=>(!empty($_POST['v-cloud']) ? $_POST['v-cloud'] : NULL),
                            'cluster'=>$_POST['v-cluster'],
                            'node'=>$_POST['v-node'],
                            'downstream'=>($_POST['v-downstream'] == 1 ? 'Y' : 'N'),
                            'active'=>($_POST['v-active'] == 1 ? 'Y' : 'N'),
                        );
                        if ($_POST['action'] == 'submit') {
                            $accordion['form'] = false;
                            $accordion['data'] = true;
                        }
                    }
                }
            break;
            case 'products':
                if ($_POST['action'] == 'submit') {
                    if (!empty($_POST['v-product'])) {
                        $post = array(
                            'tx_product'=>$_POST['v-product'],
                            'dt_expire'=>(!empty($_POST['v-expire']) ? $_POST['v-expire'] : NULL),
                            'fl_active'=>($_POST['v-active'] == 1 ? 'Y' : 'N'),
                        );
                        if (!empty($_POST['v-expire'])) {
                            if(!checkDateTime($_POST['v-expire'], 'Y-m-d')) {
                                $_message['type'] = 'danger';
                                $_message['message'] = $_alert['dateinvalid'] .": Limit";
                            }
                        }
                        if (!isset($_message['type'])) {
                            if (empty($_POST['v-id'])) {
                                if (!$result = sqlInsert($table, $post)) {
                                    $_message['type'] = 'success';
                                    $_message['message'] = $_alert['inserted'];
                                    $post = array();
                                    unset($post);
                                } else {
                                    $_message['type'] = 'danger';
                                    $_message['message'] = $result;
                                }
                            } else {
                                if (isset($key)) {
                                    array_push($post, controlInt($_POST['v-id']));
                                    if (!$result = sqlUpdate($table, $post, $key)) {
                                        $_message['type'] = 'success';
                                        $_message['message'] = $_alert['updated'];
                                        $post = array();
                                        unset($post);
                                    } else {
                                        $_message['type'] = 'danger';
                                        $_message['message'] = $result;
                                    }
                                }
                            }
                        }
                    } else {
                        $_message['type'] = 'danger';
                        $_message['message'] = $_alert['required'];
                    }
                } else if ($_POST['action'] == 'delete') {
                    if (!empty($_POST['v-id'])) {
                        $post = array(
                            $key=>controlInt($_POST['v-id']),
                        );
                        if (!$result = sqlDelete($table, $post)) {
                            $_message['type'] = 'success';
                            $_message['message'] = $_alert['deleted'];
                            $post = array();
                            unset($post);
                        } else {
                            $_message['type'] = 'danger';
                            $_message['message'] = $result;
                        }
                    }
                }
                if (isset($_message['type'])) {
                    if ($_message['type'] != 'success') {
                        $id = (isset($_POST['v-id']) ? controlInt($_POST['v-id']) : "");
                        $data = array(
                            'product'=>$_POST['v-product'],
                            'expire'=>(!empty($_POST['v-expire']) ? $_POST['v-expire'] : NULL),
                            'active'=>($_POST['v-active'] == 1 ? 'Y' : 'N'),
                        );
                        if ($_POST['action'] == 'submit') {
                            $accordion['form'] = false;
                            $accordion['data'] = true;
                        }
                    }
                }
            break;
            case 'cases':
                if ($_POST['action'] == 'submit') {
                    if (!empty($_POST['v-case']) && !empty($_POST['v-product']) && !empty($_POST['v-priority']) && !empty($_POST['v-subject']) && !empty($_POST['v-description']) && !empty($_POST['v-open'])) {
                        $post = array(
                            'id_case'=>$_POST['v-case'],
                            'id_product'=>$_POST['v-product'],
                            'id_priority'=>$_POST['v-priority'],
                            'id_environment'=>(!empty($_POST['v-environment']) ? $_POST['v-environment'] : NULL),
                            'tx_subject'=>$_POST['v-subject'],
                            'tx_description'=>$_POST['v-description'],
                            'tx_conclusion'=>(!empty($_POST['v-conclusion']) ? $_POST['v-conclusion'] : NULL),
                            'tx_report'=>(!empty($_POST['v-report']) ? $_POST['v-report'] : NULL),
                            'dt_open'=>$_POST['v-open'],
                            'dt_close'=>(!empty($_POST['v-close']) ? $_POST['v-close'] : NULL),
                        );
                        if(!checkDateTime($_POST['v-open'], 'Y-m-d H:i')) {
                            $_message['type'] = 'danger';
                            $_message['message'] = $_alert['datetimeinvalid'] .": Open";
                        }
                        if (!isset($_message['type']) && !empty($_POST['v-close'])) {
                            if(!checkDateTime($_POST['v-close'], 'Y-m-d H:i')) {
                                $_message['type'] = 'danger';
                                $_message['message'] = $_alert['datetimeinvalid'] .": Close";
                            }
                            if (!isset($_message['type'])) {
                                if(controlDateTimeToInt($_POST['v-open']) >= controlDateTimeToInt($_POST['v-close'])) {
                                    $_message['type'] = 'danger';
                                    $_message['message'] = $_alert['datesbetweeninvalid'];
                                }
                            }
                        }
                        if (!isset($_message['type'])) {
                            if (empty($_POST['v-id'])) {
                                if (!$result = sqlInsert($table, $post)) {
                                    $_message['type'] = 'success';
                                    $_message['message'] = $_alert['inserted'];
                                    $post = array();
                                    unset($post);
                                } else {
                                    $_message['type'] = 'danger';
                                    $_message['message'] = $result;
                                }
                            } else {
                                if (isset($key)) {
                                    array_push($post, controlInt($_POST['v-id']));
                                    if (!$result = sqlUpdate($table, $post, $key)) {
                                        $_message['type'] = 'success';
                                        $_message['message'] = $_alert['updated'];
                                        $post = array();
                                        unset($post);
                                    } else {
                                        $_message['type'] = 'danger';
                                        $_message['message'] = $result;
                                    }
                                }
                            }
                        }
                    } else {
                        $_message['type'] = 'danger';
                        $_message['message'] = $_alert['required'];
                    }
                } else if ($_POST['action'] == 'delete') {
                    if (!empty($_POST['v-id'])) {
                        $post = array(
                            $key=>controlInt($_POST['v-id']),
                        );
                        if (!$result = sqlDelete($table, $post)) {
                            $_message['type'] = 'success';
                            $_message['message'] = $_alert['deleted'];
                            $post = array();
                            unset($post);
                        } else {
                            $_message['type'] = 'danger';
                            $_message['message'] = $result;
                        }
                    }
                }
                if (isset($_message['type'])) {
                    if ($_message['type'] != 'success') {
                        $id = (isset($_POST['v-id']) ? controlInt($_POST['v-id']) : "");
                        $data = array(
                            'case'=>$_POST['v-case'],
                            'product'=>$_POST['v-product'],
                            'priority'=>$_POST['v-priority'],
                            'environment'=>$_POST['v-environment'],
                            'subject'=>$_POST['v-subject'],
                            'description'=>$_POST['v-description'],
                            'conclusion'=>(!empty($_POST['v-conclusion']) ? $_POST['v-conclusion'] : NULL),
                            'report'=>(!empty($_POST['v-report']) ? $_POST['v-report'] : NULL),
                            'open'=>$_POST['v-open'],
                            'close'=>(!empty($_POST['v-close']) ? $_POST['v-close'] : NULL),
                        );
                        if ($_POST['action'] == 'submit') {
                            $accordion['form'] = false;
                            $accordion['data'] = true;
                        }
                    }
                }
            break;
            case 'recurrents':
                if ($_POST['action'] == 'submit') {
                    if (!empty($_POST['v-title']) && !empty($_POST['v-report'])) {
                        $post = array(
                            'tx_title'=>$_POST['v-title'],
                            'tx_report'=>$_POST['v-report'],
                            'fl_active'=>($_POST['v-active'] == 1 ? 'Y' : 'N'),
                        );
                        if (empty($_POST['v-id'])) {
                            if (!$result = sqlInsert($table, $post)) {
                                $_message['type'] = 'success';
                                $_message['message'] = $_alert['inserted'];
                                $post = array();
                                unset($post);
                            } else {
                                $_message['type'] = 'danger';
                                $_message['message'] = $result;
                            }
                        } else {
                            if (isset($key)) {
                                array_push($post, controlInt($_POST['v-id']));
                                if (!$result = sqlUpdate($table, $post, $key)) {
                                    $_message['type'] = 'success';
                                    $_message['message'] = $_alert['updated'];
                                    $post = array();
                                    unset($post);
                                } else {
                                    $_message['type'] = 'danger';
                                    $_message['message'] = $result;
                                }
                            }
                        }
                    } else {
                        $_message['type'] = 'danger';
                        $_message['message'] = $_alert['required'];
                    }
                } else if ($_POST['action'] == 'delete') {
                    if (!empty($_POST['v-id'])) {
                        $post = array(
                            $key=>controlInt($_POST['v-id']),
                        );
                        if (!$result = sqlDelete($table, $post)) {
                            $_message['type'] = 'success';
                            $_message['message'] = $_alert['deleted'];
                            $post = array();
                            unset($post);
                        } else {
                            $_message['type'] = 'danger';
                            $_message['message'] = $result;
                        }
                    }
                }
                if (isset($_message['type'])) {
                    if ($_message['type'] != 'success') {
                        $id = (isset($_POST['v-id']) ? controlInt($_POST['v-id']) : "");
                        $data = array(
                            'title'=>$_POST['v-title'],
                            'report'=>$_POST['v-report'],
                            'active'=>($_POST['v-active'] == 1 ? 'Y' : 'N'),
                        );
                        if ($_POST['action'] == 'submit') {
                            $accordion['form'] = false;
                            $accordion['data'] = true;
                        }
                    }
                }
            break;
            case 'abstracts':
                if ($_POST['action'] == 'submit') {
                    if ((empty($_POST['v-type']) && !empty($_POST['v-date']) && !empty($_POST['v-title']) && !empty($_POST['v-report'])) || (!empty($_POST['v-type']) && !empty($_POST['v-date']) && !empty($_POST['v-calendar']) && !empty($_POST['v-report']))) {
                        if (!empty($_POST['v-type'])) {
                            $post = array(
                                'dt_report'=>$_POST['v-date'],
                                'id_calendar'=>$_POST['v-calendar'],
                                'tx_report'=>$_POST['v-report'],
                                'tx_newtitle'=>(!empty($_POST['v-newtitle']) ? $_POST['v-newtitle'] : NULL),
                            );
                        } else {
                            $post = array(
                                'dt_report'=>$_POST['v-date'],
                                'id_calendar'=>NULL,
                                'tx_report'=>$_POST['v-report'],
                                'tx_newtitle'=>$_POST['v-title'],
                            );
                        }
                        if (!empty($_POST['v-date'])) {
                            if(!checkDateTime($_POST['v-date'], 'Y-m-d')) {
                                $_message['type'] = 'danger';
                                $_message['message'] = $_alert['dateinvalid'] ."!";
                            }
                        }
                        if (!isset($_message['type'])) {
                            if (empty($_POST['v-id'])) {
                                if (!$result = sqlInsert($table, $post)) {
                                    $_message['type'] = 'success';
                                    $_message['message'] = $_alert['inserted'];
                                    $post = array();
                                    unset($post);
                                } else {
                                    $_message['type'] = 'danger';
                                    $_message['message'] = $result;
                                }
                            } else {
                                if (isset($key)) {
                                    array_push($post, controlInt($_POST['v-id']));
                                    if (!$result = sqlUpdate($table, $post, $key)) {
                                        $_message['type'] = 'success';
                                        $_message['message'] = $_alert['updated'];
                                        $post = array();
                                        unset($post);
                                    } else {
                                        $_message['type'] = 'danger';
                                        $_message['message'] = $result;
                                    }
                                }
                            }
                        }
                    } else {
                        $_message['type'] = 'danger';
                        $_message['message'] = $_alert['required'];
                    }
                } else if ($_POST['action'] == 'delete') {
                    if (!empty($_POST['v-id'])) {
                        $post = array(
                            $key=>controlInt($_POST['v-id']),
                        );
                        if (!$result = sqlDelete($table, $post)) {
                            $_message['type'] = 'success';
                            $_message['message'] = $_alert['deleted'];
                            $post = array();
                            unset($post);
                        } else {
                            $_message['type'] = 'danger';
                            $_message['message'] = $result;
                        }
                    }
                }
                if (isset($_message['type'])) {
                    if ($_message['type'] != 'success') {
                        $id = (isset($_POST['v-id']) ? controlInt($_POST['v-id']) : "");
                            if (!empty($_POST['v-type'])) {
                                $data = array(
                                    'date'=>$_POST['v-date'],
                                    'calendar'=>$_POST['v-calendar'],
                                    'report'=>$_POST['v-report'],
                                    'newtitle'=>(!empty($_POST['v-newtitle']) ? $_POST['v-newtitle'] : NULL),
                                    'type'=>($_POST['v-type'] == 1 ? 'Y' : 'N'),
                                );
                            } else {
                                $data = array(
                                'date'=>$_POST['v-date'],
                                'calendar'=>$_POST['v-calendar'],
                                'report'=>$_POST['v-report'],
                                'title'=>(!empty($_POST['v-title']) ? $_POST['v-title'] : NULL),
                                'type'=>($_POST['v-type'] == 1 ? 'Y' : 'N'),
                                );
                            }
                        if ($_POST['action'] == 'submit') {
                            $accordion['form'] = false;
                            $accordion['data'] = true;
                        }
                    }
                }
            break;
            case 'reports':
                if ($_POST['action'] == 'submit') {
                    if (!empty($_POST['v-report']) && !empty($_POST['v-start']) && !empty($_POST['v-end'])) {
                        if(!checkDateTime($_POST['v-start'], 'Y-m-d')) {
                            $_message['type'] = 'danger';
                            $_message['message'] = $_alert['dateinvalid'] .": Start";
                        }
                        if (!isset($_message['type'])) {
                            if(!checkDateTime($_POST['v-end'], 'Y-m-d')) {
                                $_message['type'] = 'danger';
                                $_message['message'] = $_alert['dateinvalid'] .": End";
                            }
                        }
                        if (!isset($_message['type'])) {
                            if(controlDateTimeToInt($_POST['v-start']) >= controlDateTimeToInt($_POST['v-end'])) {
                                $_message['type'] = 'danger';
                                $_message['message'] = $_alert['datesbetweeninvalid'];
                            }
                        }
                        if (!isset($_message['type'])) {
                            $today = date('YmdHi');
                            $report = controlSpecialChars($_POST['v-report'], '[^a-z]');
                            $start = str_replace('-', '', $_POST['v-start']);
                            $end = str_replace('-', '', $_POST['v-end']);
                            $name = $today .'_'. $report .'_'. $start .'_'. $end;
                            $filename = '../report/queue/'. $name .'.wait';
                            if (!is_file($filename)) {
                                if (touch($filename)) {
                                    $_message['type'] = 'success';
                                    $_message['message'] = $_alert['reportwasadd'];
                                }
                            } else {
                                $_message['type'] = 'danger';
                                $_message['message'] = $_alert['reportexists'];
                            }
                        }
                    } else {
                        $_message['type'] = 'danger';
                        $_message['message'] = $_alert['required'];
                    }
                } else if ($_POST['action'] == 'delete') {
                    if (!empty($_POST['v-id'])) {
                        $name = controlSpecialChars($_POST['v-id'], '[^a-zA-Z0-9\_]');
                        $filename = '../data/pdf/'. $name .'.pdf';
                        if (is_file($filename)) {
                            if (unlink($filename)) {
                                $_message['type'] = 'success';
                                $_message['message'] = $_alert['reportdeletedsuccess'];
                            } else {
                                $_message['type'] = 'danger';
                                $_message['message'] = $_alert['reportdeletederror'];
                            }
                        } else {
                            $_message['type'] = 'danger';
                            $_message['message'] = $_alert['reportnotexist'];
                        }
                    }
                }
                if ($_POST['action'] != 'delete' && isset($_message['type'])) {
                    if ($_message['type'] != 'success') {
                        $data = array(
                            'report'=>$_POST['v-report'],
                            'start'=>$_POST['v-start'],
                            'end'=>$_POST['v-end'],
                        );
                        if ($_POST['action'] == 'submit') {
                            $accordion['form'] = false;
                            $accordion['data'] = true;
                        }
                    }
                }
            break;
            case 'profile':
                if ($_POST['action'] == 'submit') {
                    if (!empty($_POST['v-theme']) && !empty($_POST['v-page'])) {
                        $post = array(
                            'tp_theme'=>$_POST['v-theme'],
                            'nu_page'=>$_POST['v-page'],
                        );
                        if ($_SESSION['user']['ldap'] == 'N') {
                            if (!empty($_POST['v-email'])) {
                                if(checkSpecialChars($_POST['v-email'], $_regex['checkemail'])) {
                                    $post = array_merge($post,
                                        array('tx_email'=>(!empty($_POST['v-email']) ? $_POST['v-email'] : NULL))
                                    );
                                } else {
                                    $_message['type'] = 'danger';
                                    $_message['message'] = $_alert['invalidemail'];
                                }
                            }
                        }
                        if (!isset($_message['type'])) {
                            if (isset($_SESSION['user']['id'])) {
                                if (isset($key)) {
                                    array_push($post, controlInt($_SESSION['user']['id']));
                                    if (!$result = sqlUpdate($table, $post, $key)) {
                                        if ($_SESSION['user']['theme'] != $_POST['v-theme']) {
                                            $_SESSION['user']['theme'] = $_POST['v-theme'];
                                            $reload = true;
                                        }
                                        if ($_SESSION['user']['page'] != $_POST['v-page']) {
                                            $_SESSION['user']['page'] = $_POST['v-page'];
                                        }
                                        if ($_SESSION['user']['ldap'] == 'N') {
                                            if ($_SESSION['user']['email'] != $_POST['v-email']) {
                                                $_SESSION['user']['email'] = $_POST['v-email'];
                                            }
                                        }
                                        $_message['type'] = 'success';
                                        $_message['message'] = $_alert['updatedprofile'] . (isset($reload) ? $_alert['clicktoreload'] : "");
                                        $post = array();
                                        unset($post);
                                    } else {
                                        $_message['type'] = 'danger';
                                        $_message['message'] = $result;
                                    }
                                }
                            }
                        }
                    } else {
                        $_message['type'] = 'danger';
                        $_message['message'] = $_alert['required'];
                    }
                }
                if (isset($_message['type'])) {
                    if ($_message['type'] != 'success') {
                        $data = array(
                            'login'=>$_POST['v-login'],
                            'fullname'=>$_POST['v-fullname'],
                            'role'=>$_POST['v-role'],
                            'email'=>(!empty($_POST['v-email']) ? $_POST['v-email'] : NULL),
                            'theme'=>$_POST['v-theme'],
                            'page'=>$_POST['v-page'],
                        );
                    }
                }
            break;
            case 'password':
                if ($_POST['action'] == 'submit') {
                    if (!empty($_POST['v-current']) && !empty($_POST['v-password']) && !empty($_POST['v-confirm'])) {
                        if ($_SESSION['user']['ldap'] == 'N') {
                            $current = controlSpecialChars($_POST['v-current'], $_regex['controlpassword']);
                            $user = sqlGet('tb_user',
                                array('id'=>'id_user'),
                                array('tx_login'=>$_SESSION['user']['login'], 'tx_password'=>md5($current))
                            );
                            if (count($user)) {
                                if ($_SESSION['user']['id'] == $user['id']) {
                                    if (checkSpecialChars($_POST['v-password'], $_regex['checkpassword']) == true) {
                                        $password = controlSpecialChars($_POST['v-password'], $_regex['controlpassword']);
                                        $confirm = controlSpecialChars($_POST['v-confirm'], $_regex['controlpassword']);
                                        if ($password == $confirm) {
                                            $post = array(
                                                'tx_password'=>md5($password),
                                                controlInt($_SESSION['user']['id'])
                                            );
                                            if (isset($_SESSION['user']['id'])) {
                                                if (isset($key)) {
                                                    if (!$result = sqlUpdate($table, $post, $key)) {
                                                        $_message['type'] = 'success';
                                                        $_message['message'] = $_alert['updatedpassword'];
                                                        $post = array();
                                                        unset($post);
                                                    } else {
                                                        $_message['type'] = 'danger';
                                                        $_message['message'] = $result;
                                                    }
                                                }
                                            }
                                        } else {
                                            $_message['type'] = 'danger';
                                            $_message['message'] = $_alert['passworddoesnotmatch'];
                                        }
                                    } else {
                                        $_message['type'] = 'danger';
                                        $_message['message'] = $_alert['passworddoesnotcharsrequired'];
                                    }
                                } else {
                                    $_message['type'] = 'danger';
                                    $_message['message'] = $_alert['userpassworddoesnotmatch'];
                                }
                            } else {
                                $_message['type'] = 'danger';
                                $_message['message'] = $_alert['passwordinvalid'];
                            }
                        }
                    } else {
                        $_message['type'] = 'danger';
                        $_message['message'] = $_alert['required'];
                    }
                }
            break;
        }
    }
    if (isset($_REQUEST['resource'])) {
        if ($_REQUEST['resource'] == 'portal') {
            if (isset($_POST['v-id']) && isset($_POST['action'])) {
                if (!empty($_POST['v-id']) && $_POST['action'] == 'edit') {
                    if (isset($collection)) {
                        if ($collection == 'planning') {
                            $accordion['form'] = false;
                            $accordion['data'] = true;
                            $accordion['calendar'] = true;
                            $id = (isset($_POST['v-id']) ? controlInt($_POST['v-id']) : "");
                        } else if ($collection == 'activities') {
                            $accordion['form'] = false;
                            $accordion['data'] = true;
                            $accordion['activities'] = true;
                            $id = (isset($_POST['v-id']) ? controlInt($_POST['v-id']) : "");
                        } else {
                            $accordion['form'] = false;
                            $accordion['data'] = true;
                            $id = (isset($_POST['v-id']) ? controlInt($_POST['v-id']) : "");
                        }
                    }
                }
            }
            if (isset($title) && isset($collection)) {
                htmlTitle($title);
                include '../share/php/'. $collection .'.php';
                htmlCopyright();
            }
        }
    }
}

require '../etc/conn_close.php';