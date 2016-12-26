<?php

@$user = $argv[1];

if (!$user)
{
    return;
}

function separate($element)
{
    return explode("\t", $element);
}

function doCheck($element)
{
    return $element[0] > 1000;
}

$counts = shell_exec('git shortlog -s -n -e --all | grep '.$user);

$counts = explode("\n", $counts);
$counts = array_map('trim', $counts);
$counts = array_map('separate', $counts);

$counts = array_filter($counts, 'doCheck');

if ( count($counts) > 0)
{
    echo 'true'; //we need a non-empty result
}