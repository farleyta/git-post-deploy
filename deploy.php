<?php

// The location of the home directory of the server - all else is relative to this
$base_dir = '/home/oregano/'; //INCLUDE TRAILING SLASH

// This file must be present for the auto-deploy to work
require_once( $base_dir . 'deployment/DeployClass.php');

$deployment_options = array(
		'website_dir' 		=>  $base_dir . 'public_html',
		'git_dir' 			=>  $base_dir . 'git_repos/oregano-website.git',
		'logfiles_dir'		=>  $base_dir . 'deployment',

		// below are the default values of all other possible options
		//  probably no need to change these often
		
		'log' 				=>  'deployments.log', // name of the logfile
		'max_log_size' 		=>  10000, // in bytes = 10KB
		'date_format' 		=>  'Y-m-d H:i:sP', // default date_format
		'repo_ipaddr' 		=>  '63.246.22.222', // BitBucket's POST service IP
		'branch' 			=>  'master', //default branch
		'remote' 			=>  'origin' //default remote alias
	);

$deploy = new Deploy($deployment_options);

$deploy->execute();


?>