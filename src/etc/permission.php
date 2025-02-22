<?php

if (isset($_SESSION['user']['role'])) {

    if ($_SESSION['user']['role'] != 'A') {

        if (isset($_SESSION['user']) && !isset($_SESSION['permission'])) {
            $_SESSION['permission'] = array(
                'home'=>(isset($_permission['home'][strtolower($_roles[$_SESSION['user']['role']])]) ? $_permission['home'][strtolower($_roles[$_SESSION['user']['role']])] : false),
                'support'=>(isset($_permission['support'][strtolower($_roles[$_SESSION['user']['role']])]) ? $_permission['support'][strtolower($_roles[$_SESSION['user']['role']])] : false),
                'settings'=>(isset($_permission['settings'][strtolower($_roles[$_SESSION['user']['role']])]) ? $_permission['settings'][strtolower($_roles[$_SESSION['user']['role']])] : false),
            );
            $_jsonglobal = __DIR__ .'/../data/json/permission.json';
            if (is_file($_jsonglobal)) {
                $json = json_decode(file_get_contents($_jsonglobal), true);
                if (isset($json['permission'])) {
                    $access = '';
                    foreach($json['permission'] as $m=>$p) {
                        foreach($p as $k=>$v) {
                            if ($k == strtolower($_roles[$_SESSION['user']['role']])) {
                                switch ($v) {
                                    case 'full':
                                        $access = 'W';
                                    break;
                                    case 'read':
                                        $access = 'R';
                                    break;
                                    default:
                                        $access = 'N';
                                }
                                $_SESSION['permission'][$m] = $access;
                            }
                        }
                    }
                    unset($access);
                }
            }
        }

        if (isset($_SESSION['permission'])) {
            if (isset($_REQUEST['collection'])) {
                switch($_REQUEST['collection']) {
                    case 'dashboard':
                        if (isset($_SESSION['permission']['dashboard'])) {
                            if ($_SESSION['permission']['dashboard'] == 'N') {
                                $noaccess = true;
                            }
                        } else {
                            $noaccess = true;
                        }
                    break;
                    case 'planning':
                        if (isset($_SESSION['permission']['planning'])) {
                            if ($_SESSION['permission']['planning'] != 'N') {
                                if (isset($_POST['action'])) {
                                    if ($_SESSION['permission']['planning'] == 'R') {
                                        $noaccess = true;
                                    }
                                }
                            } else {
                                $noaccess = true;
                            }
                        } else {
                            $noaccess = true;
                        }
                    break;
                    case 'activities':
                        if (isset($_SESSION['permission']['activities'])) {
                            if ($_SESSION['permission']['activities'] != 'N') {
                                if (isset($_POST['action'])) {
                                    if ($_SESSION['permission']['activities'] == 'R') {
                                        $noaccess = true;
                                    }
                                }
                            } else {
                                $noaccess = true;
                            }
                        } else {
                            $noaccess = true;
                        }
                    break;
                    case 'environments':
                        if (isset($_SESSION['permission']['environments'])) {
                            if ($_SESSION['permission']['environments'] != 'N') {
                                if (isset($_POST['action'])) {
                                    if ($_SESSION['permission']['environments'] == 'R') {
                                        $noaccess = true;
                                    }
                                }
                            } else {
                                $noaccess = true;
                            }
                        } else {
                            $noaccess = true;
                        }
                    break;
                    case 'platforms':
                        if (isset($_SESSION['permission']['platforms'])) {
                            if ($_SESSION['permission']['platforms'] != 'N') {
                                if (isset($_POST['action'])) {
                                    if ($_SESSION['permission']['platforms'] == 'R') {
                                        $noaccess = true;
                                    }
                                }
                            } else {
                                $noaccess = true;
                            }
                        } else {
                            $noaccess = true;
                        }
                    break;
                    case 'kubernetes':
                        if (isset($_SESSION['permission']['kubernetes'])) {
                            if ($_SESSION['permission']['kubernetes'] != 'N') {
                                if (isset($_POST['action'])) {
                                    if ($_SESSION['permission']['kubernetes'] == 'R') {
                                        $noaccess = true;
                                    }
                                }
                            } else {
                                $noaccess = true;
                            }
                        } else {
                            $noaccess = true;
                        }
                    break;
                    case 'clouds':
                        if (isset($_SESSION['permission']['kubernetes'])) {
                            if ($_SESSION['permission']['kubernetes'] != 'N') {
                                if (isset($_POST['action'])) {
                                    if ($_SESSION['permission']['kubernetes'] == 'R') {
                                        $noaccess = true;
                                    }
                                }
                            } else {
                                $noaccess = true;
                            }
                        } else {
                            $noaccess = true;
                        }
                    break;
                    case 'clusters':
                        if (isset($_SESSION['permission']['clusters'])) {
                            if ($_SESSION['permission']['clusters'] != 'N') {
                                if (isset($_POST['action'])) {
                                    if ($_SESSION['permission']['clusters'] == 'R') {
                                        $noaccess = true;
                                    }
                                }
                            } else {
                                $noaccess = true;
                            }
                        } else {
                            $noaccess = true;
                        }
                    break;
                    case 'resources':
                        if (isset($_SESSION['permission']['resources'])) {
                            if ($_SESSION['permission']['resources'] != 'N') {
                                if (isset($_POST['action'])) {
                                    if ($_SESSION['permission']['resources'] == 'R') {
                                        $noaccess = true;
                                    }
                                }
                            } else {
                                $noaccess = true;
                            }
                        } else {
                            $noaccess = true;
                        }
                    break;
                    case 'versions':
                        if (isset($_SESSION['permission']['versions'])) {
                            if ($_SESSION['permission']['versions'] != 'N') {
                                if (isset($_POST['action'])) {
                                    if ($_SESSION['permission']['versions'] == 'R') {
                                        $noaccess = true;
                                    }
                                }
                            } else {
                                $noaccess = true;
                            }
                        } else {
                            $noaccess = true;
                        }
                    break;
                    case 'users':
                        if (isset($_SESSION['permission']['users'])) {
                            if ($_SESSION['permission']['users'] != 'N') {
                                if (isset($_POST['action'])) {
                                    if ($_SESSION['permission']['users'] == 'R') {
                                        $noaccess = true;
                                    }
                                }
                            } else {
                                $noaccess = true;
                            }
                        } else {
                            $noaccess = true;
                        }
                    break;
                    case 'products':
                        if (isset($_SESSION['permission']['products'])) {
                            if ($_SESSION['permission']['products'] != 'N') {
                                if (isset($_POST['action'])) {
                                    if ($_SESSION['permission']['products'] == 'R') {
                                        $noaccess = true;
                                    }
                                }
                            } else {
                                $noaccess = true;
                            }
                        } else {
                            $noaccess = true;
                        }
                    break;
                    case 'cases':
                        if (isset($_SESSION['permission']['cases'])) {
                            if ($_SESSION['permission']['cases'] != 'N') {
                                if (isset($_POST['action'])) {
                                    if ($_SESSION['permission']['cases'] == 'R') {
                                        $noaccess = true;
                                    }
                                }
                            } else {
                                $noaccess = true;
                            }
                        } else {
                            $noaccess = true;
                        }
                    break;
                    case 'recurrents':
                        if (isset($_SESSION['permission']['recurrents'])) {
                            if ($_SESSION['permission']['recurrents'] != 'N') {
                                if (isset($_POST['action'])) {
                                    if ($_SESSION['permission']['recurrents'] == 'R') {
                                        $noaccess = true;
                                    }
                                }
                            } else {
                                $noaccess = true;
                            }
                        } else {
                            $noaccess = true;
                        }
                    break;
                    case 'abstracts':
                        if (isset($_SESSION['permission']['abstracts'])) {
                            if ($_SESSION['permission']['abstracts'] != 'N') {
                                if (isset($_POST['action'])) {
                                    if ($_SESSION['permission']['abstracts'] == 'R') {
                                        $noaccess = true;
                                    }
                                }
                            } else {
                                $noaccess = true;
                            }
                        } else {
                            $noaccess = true;
                        }
                    break;
                    case 'reports':
                        if (isset($_SESSION['permission']['reports'])) {
                            if ($_SESSION['permission']['reports'] != 'N') {
                                if (isset($_POST['action'])) {
                                    if ($_SESSION['permission']['reports'] == 'R') {
                                        $noaccess = true;
                                    }
                                }
                            } else {
                                $noaccess = true;
                            }
                        } else {
                            $noaccess = true;
                        }
                    break;
                }
            }             
        }

        if (isset($noaccess)) {
            require '../etc/conn_close.php';
            exit("<p style=\"margin-top: 20px;\">". $_alert['noaccess'] ."</p>\n");
        }

    }

}