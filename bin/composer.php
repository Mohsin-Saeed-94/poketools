#!/usr/bin/env php
<?php
/**
 * @file Wrapper for composer inside docker container
 */

// PhpStorm will start with a garbage working directory.
chdir(__DIR__.'/..');

$dockercompose = 'docker-compose exec -T php ';
$cmd = 'composer ';

// Append the requested arguments
$cmd .= implode(' ', array_slice($argv, 1));
$run = $dockercompose.$cmd;

// Run the command.
passthru($run);
