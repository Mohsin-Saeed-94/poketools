#!/usr/bin/env php
<?php
/**
 * @file Wrapper for symfony console inside docker container
 */

// PhpStorm will start with a garbage working directory.
chdir(__DIR__.'/..');

$dockercompose = 'docker-compose exec -T php ';
$cmd = 'bin/console ';

// Append the requested arguments
$cmd .= implode(' ', array_slice($argv, 1));
$run = $dockercompose.$cmd;

// Run the command.
passthru($run);
