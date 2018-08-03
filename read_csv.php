<?php
/**
 * Created by PhpStorm.
 * User: galina.logofatu
 * Date: 7/10/2018
 * Time: 2:31 PM
 */

$conn = mysqli_connect('localhost', 'root', '', 'tv');
global $conn;

$root = 'csv/';
$files = glob($root."*.csv");

if (!$conn) {
    echo "couldn't connect to database";
    exit;
} else {

    $users = getUsers();
    $os = getOs();
    $browser = getBrowser();
    $browser_ver = getBrowserVer();
    $tv_post = getTvPost();

    foreach ($files as $file){
//        $file = str_replace($root,'', str_replace('.csv','', $file));

        $content = file_get_contents($file);
        $jsons = explode('\n',$content);

//        echo '<pre>';print_r($jsons);echo '</pre>';
        foreach ($jsons as $json){
            if(empty($json))continue;
            $object = json_decode($json);
//            print_r(json_decode($json));
//            print_r('<br><br>');
            $ip = escape($object->clientIP);
            $name = escape($object->clientName);

            if (!isset($users[$ip][$name]) || empty($users[$ip][$name])){
                $first_launch_ts = escape($object->events[0]->ts);
                $sql = "INSERT IGNORE INTO lau SET `ip` = '".$ip."',`name` = '".$name."',`first_launch_ts` = '".(int)$first_launch_ts."'";

                if (mysqli_query($conn, $sql)) {
                    echo "executed\r\n";
                } else {
                    echo mysqli_error($conn);
                }

                $users = getUsers();
            }

            $os_name = escape($object->os);
            if (!array_key_exists($os_name,$os)){
                $sql = "INSERT IGNORE INTO os SET `name` = '".$os_name."'";

                if (mysqli_query($conn, $sql)) {
                    echo "executed\r\n";
                } else {
                    echo mysqli_error($conn);
                }

                $os = getOs();
            }

            $browser_name = escape($object->browser);
            if (!array_key_exists($browser_name,$browser)) {
                $sql = "INSERT IGNORE INTO browser SET `name` = '" . $browser_name . "'";

                if (mysqli_query($conn, $sql)) {
                    echo "executed\r\n";
                } else {
                    echo mysqli_error($conn);
                }

                $browser = getBrowser();
            }

            $browser_ver_name = escape($object->browser_ver);
            if (!array_key_exists($browser_ver_name,$browser_ver)) {
                $sql = "INSERT IGNORE INTO browser_ver SET `name` = '".$browser_ver_name."'";

                if (mysqli_query($conn, $sql)) {
                    echo "executed\r\n";
                } else {
                    echo mysqli_error($conn);
                }

                $browser_ver = getBrowserVer();
            }


            foreach ($object->events as $event){
                if ($event->name == 'change'){
                    $tv_post_name = escape($event->tv_post);
                    if (!array_key_exists($tv_post_name,$tv_post)) {
                        $sql = "INSERT IGNORE INTO tv_post SET `name` = '".$tv_post_name."'";

                        if (mysqli_query($conn, $sql)) {
                            echo "executed\r\n";
                        } else {
                            echo mysqli_error($conn);
                        }

                        $tv_post = getTvPost();
                    }


                    $sql = "INSERT INTO dau (`date`,`user_id`,`os_id`,`browser_id`,`browser_ver_id`,`tv_post_id`,`number_of_launches`,`time_spent`) ";
                    $sql .= "VALUES (
                                DATE(FROM_UNIXTIME(".$event->ts.")), 
                                " . $users[$ip][$name]['user_id'] . ", 
                                " . $os[$os_name] . ",
                                " . $browser[$browser_name] . ",
                                " . $browser_ver[$browser_ver_name] . ",
                                " . $tv_post[$tv_post_name] . ",
                                1,
                                " . escape($event->time_spent) . "
                                )
                          ON DUPLICATE KEY UPDATE `number_of_launches`=`number_of_launches`+1, `time_spent`=`time_spent`+" . escape($event->time_spent) . "";

                    if (mysqli_query($conn, $sql)) {
                        echo "executed\r\n";
                    } else {
                        echo mysqli_error($conn);
                    }
                }
            }
        }

        rename($file, str_replace('csv/','saved_csv/',$file));
    }
}

function escape($data){
    global $conn;
    return mysqli_real_escape_string($conn, $data);
}

function getUsers(){
    global $conn;
    $data = [];

    $sql = "SELECT * FROM lau";

    $result = mysqli_query($conn, $sql);
    if (mysqli_query($conn, $sql)) {
        while($row = mysqli_fetch_assoc($result)) {
            $data[$row['ip']][$row['name']]['user_id'] = $row['user_id'];
            $data[$row['ip']][$row['name']]['first_launch_ts'] = $row['first_launch_ts'];
        }
        echo "executed\r\n";
    } else {
        echo mysqli_error($conn);
    }

    return $data;
}

function getOs(){
    global $conn;
    $data = [];

    $sql = "SELECT * FROM os";

    $result = mysqli_query($conn, $sql);
    if (mysqli_query($conn, $sql)) {
        while($row = mysqli_fetch_assoc($result)) {
            $data[$row['name']] = $row['id'];
        }
        echo "executed\r\n";
    } else {
        echo mysqli_error($conn);
    }

    return $data;
}

function getBrowser(){
    global $conn;
    $data = [];

    $sql = "SELECT * FROM browser";

    $result = mysqli_query($conn, $sql);
    if (mysqli_query($conn, $sql)) {
        while($row = mysqli_fetch_assoc($result)) {
            $data[$row['name']] = $row['id'];
        }
        echo "executed\r\n";
    } else {
        echo mysqli_error($conn);
    }

    return $data;
}

function getBrowserVer(){
    global $conn;
    $data = [];

    $sql = "SELECT * FROM browser_ver";

    $result = mysqli_query($conn, $sql);
    if (mysqli_query($conn, $sql)) {
        while($row = mysqli_fetch_assoc($result)) {
            $data[$row['name']] = $row['id'];
        }
        echo "executed\r\n";
    } else {
        echo mysqli_error($conn);
    }

    return $data;
}

function getTvPost(){
    global $conn;
    $data = [];

    $sql = "SELECT * FROM tv_post";

    $result = mysqli_query($conn, $sql);
    if (mysqli_query($conn, $sql)) {
        while($row = mysqli_fetch_assoc($result)) {
            $data[$row['name']] = $row['id'];
        }
        echo "executed\r\n";
    } else {
        echo mysqli_error($conn);
    }

    return $data;
}