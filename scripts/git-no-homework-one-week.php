<?php

@$user = $argv[1];
@$start = $argv[2];
@$end = $argv[3];

if (!$user || !$start || !$end)
{
    return;
}

if ( date('D') !== 'Mon') //just run it once a week, so we have to pick a day
{
    return;
}

$date = new DateTime('7 days ago');
$from = $date->format('Y-m-d');
$log = shell_exec('git log --since=\''.$from.'\' --author=\''.$user.'\' --format=\'%aI\'');
$log = trim($log);
$logLines = explode("\n", $log);
$logLines = array_map('trim', $logLines);

$start = explode(':', $start);
$end   = explode(':', $end);
$logLines = array_filter($logLines, 'checkWorkhours');

if ( count($logLines) > 0)
{
    echo 'true'; //we need a non-empty result
}

function checkWorkhours($dateString)
{
    global $start, $end; //this looks ugly, i know, but I like this more than a foreach and unset

    $date = new DateTime($dateString);
/**
    if ( in_array($date->format('D'), ['Sat', 'Sun'])) //I simply assume a mon-fri - week
    {
        return true;
    }
*/
    $startDate = clone $date;
    $startDate->setTime($start[0], $start[1], $start[2]);

    $endDate = clone $date;
    $endDate->setTime($end[0], $end[1], $end[2]);

    if ( $date->getTimestamp() - $startDate->getTimestamp() < 0)
    {
        return true;
    }

    if ( $endDate->getTimestamp() - $date->getTimestamp() < 0)
    {
        return true;
    }

    return false;
}