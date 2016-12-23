<?php

require_once 'notorm/NotORM.php';
require_once 'config.php';

$PDOconnection = new PDO("mysql:dbname=".DB_NAME, DB_USER, DB_PASSWORD);
$connection = new NotORM($PDOconnection);

$achievments = $connection->achievments();
$candidates  = $connection->candidates();

foreach ($achievments as $achievment)
{
    foreach ($candidates as $candidate)
    {
        $cGroups = explode(',', $candidate['candidate_groups']);
        if ( !in_array($achievment['achievment_group'], $cGroups) )
        {
            continue;
        }

        $command = str_replace('{{user}}', $candidate['candidate_email'], $achievment['execute_command']);
        if (empty($command)) {
            continue;
        }

        $result = shell_exec($command);

        if (empty($result))
        {
            continue;
        }

        $mailtext = $candidate['candidate_salutation']."\n\n".
            ' du hast folgendes Achievment erreicht: '.
            $achievment['achievment_name'].
            "\n\nDein Fortschritt wurde gespeichert, gut gemacht!";

        echo $mailtext;

        $data = [
            'id_achievment' => $achievment['id'],
            'id_candidate'  => $candidate['id'],
            'unlock_date'   => date("Y-m-d H:i:s")
        ];
        $connection->unlocked_achievments()->insert($data);
    }
}
