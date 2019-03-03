<?php
/**
 *
 *
 */
$pid = pcntl_fork();
if ($pid == -1)
{
    die("could not fork");
}
elseif($pid == 0)
{
    echo "I'm the child  process \n";
}
else
{
    echo "I'm the parent process \n";
    exit;
}