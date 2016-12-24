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

        /**
         * @var NotORM_Result $unlocked
         */
        $unlocked = $connection->unlocked_achievments();
        $unlocked->where('id_achievment = ?', $achievment['id']);
        $unlocked->where('id_candidate = ?', $candidate['id']);

        if ( empty($achievment['limit_earned']) || $achievment['limit_earned'] < count($unlocked) )
        {
            $mailtext = $candidate['candidate_salutation']."\n\n".  //TODO: this will be replaced with templates from the db which math the users group
                ' du hast folgendes Achievment erreicht: '.
                $achievment['achievment_name'].
                "\n\nDein Fortschritt wurde gespeichert, gut gemacht!";

            echo $mailtext;  //TODO: instead of echo the text will be mailed to the user

            $data = [
                'id_achievment' => $achievment['id'],
                'id_candidate'  => $candidate['id'],
                'unlock_date'   => date("Y-m-d H:i:s")
            ];
            $connection->unlocked_achievments()->insert($data);
        }

    }
}
