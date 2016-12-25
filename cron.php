<?php

require_once 'notorm/NotORM.php';
require_once 'config.php';
require_once 'PHPMailer/PHPMailerAutoload.php';

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
        $command = str_replace('{{workhour_start}}', $candidate['workhour_start'], $command);
        $command = str_replace('{{workhour_end}}', $candidate['workhour_end'], $command);

        if ( empty($command) )
        {
            continue;
        }

        $result = shell_exec($command);

        if ( empty($result) )
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
            $mailtexts = $connection->templates()->where('candidate_team = ?', $candidate['candidate_team'] );

            $text = '';
            foreach ($mailtexts as $mailtext)
            {
                $mailtext = str_replace('{{salutation}}', $candidate['candidate_salutation'], $mailtext['template']);
                $mailtext = str_replace('{{achievment_name}}', $achievment['achievment_name'], $mailtext);
                $mailtext = str_replace('{{achievment_description}}', $achievment['achievment_description'], $mailtext);

                $text .= $mailtext."\n<br/>";
            }
            $text = trim($text);

            if (!$text)
            {
                error_log('Achievment unlocked but no message to send found, command: '.$command);
                continue;
            }

            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = SMTP_AUTH;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = SMTP_ENCR;
            $mail->Port = SMTP_PORT;
            $mail->setFrom(SMTP_FROM);
            $mail->isHTML(true);
            $mail->addAddress($candidate['candidate_email']);
            $mail->Subject = MAIL_SUBJECT;
            $mail->Body    = $text;
            $mail->AltBody = strip_tags($text);

            if (!$mail->send())
            {
                error_log('Mail could not be sent, command: '.$command.' Errormessage: '.$mail->ErrorInfo);
            }

            $data = [
                'id_achievment' => $achievment['id'],
                'id_candidate'  => $candidate['id'],
                'unlock_date'   => date("Y-m-d H:i:s")
            ];
            $connection->unlocked_achievments()->insert($data);
        }

    }
}
