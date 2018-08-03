<?php
/**
 * Created by PhpStorm.
 * User: galina.logofatu
 * Date: 7/11/2018
 * Time: 10:01 AM
 */
$conn = mysqli_connect('localhost', 'root', '', 'tv');
global $conn;

//$date_for_kpi = '';

//$last_date_dau = lastDate('kpi_dau');

$sql = "INSERT INTO `kpi_dau` 
          SELECT `date`,`tv_post_id`,count(DISTINCT`user_id`) AS `nb_users`
          FROM `dau` 
          WHERE 1 
          GROUP BY `date`,`tv_post_id` 
          ON DUPLICATE KEY UPDATE
          `nb_users` = values(`nb_users`)
          ";
if (mysqli_query($conn, $sql)) {
} else {
    echo mysqli_error($conn);
}

$sql = "INSERT INTO `kpi_lau` 
        SELECT 
            d.`date`,
            d.`tv_post_id`,
            (SELECT 
                    COUNT(DISTINCT `user_id`) AS nb_users
                FROM
                    tv.dau
                WHERE
                    `date` <= d.`date`
                        AND `tv_post_id` = d.`tv_post_id`) AS `nb_users`
        FROM
            `dau` d
        GROUP BY d.`date` , d.`tv_post_id`
        ON DUPLICATE KEY UPDATE
          `nb_users` = (SELECT 
            COUNT(DISTINCT `user_id`) AS nb_users
        FROM
            tv.dau
        WHERE
            `date` <= d.`date`
                AND `tv_post_id` = d.`tv_post_id`)
        ";
if (mysqli_query($conn, $sql)) {
} else {
    echo mysqli_error($conn);
}

$sql = "INSERT INTO `kpi_dau_per_os`
          SELECT `date`,`os_id`,`tv_post_id`,count(DISTINCT`user_id`) AS `nb_users`
          FROM `dau`
          WHERE 1
          GROUP BY `date`,`os_id`,`tv_post_id`
          ON DUPLICATE KEY UPDATE
          `nb_users` = values(`nb_users`)
          ";
if (mysqli_query($conn, $sql)) {
} else {
    echo mysqli_error($conn);
}

$sql = "INSERT INTO `kpi_time_spent`
          SELECT `date`,`tv_post_id`,SUM(`time_spent`)/SUM(`number_of_launches`) AS `avg_time_per_session`, SUM(`time_spent`) AS `total_time_spent`
          FROM `dau`
          WHERE 1
          GROUP BY `date`,`tv_post_id`
          ON DUPLICATE KEY UPDATE
          `avg_time_per_session` = values(`avg_time_per_session`),
          `total_time_spent` = values(`total_time_spent`)
          ";
if (mysqli_query($conn, $sql)) {
} else {
    echo mysqli_error($conn);
}

echo "ok";


















function lastDate($table){
    global $conn;

    $sql = "SELECT `date` FROM `" . $table . "` ORDER BY `date` DESC limit 1";

    $result = mysqli_query($conn, $sql);
    if (mysqli_query($conn, $sql)) {
        while($row = mysqli_fetch_assoc($result)) {
            return $row['date'];
        }
        echo "executed\r\n";
    } else {
        echo mysqli_error($conn);
    }
    return false;
}