<?php

// Set default time zone. 
// List here: http://www.php.net/manual/en/timezones.america.php
date_default_timezone_set('America/Lima');

// The location of the home directory of the server - all else is relative to this
$base_dir = '/home/oregano/'; //INCLUDE TRAILING SLASH

// This file must be present for the pre-deploy setup to work
require_once('PreDeployClass.php');

$pre_deploy_options = array(
		'hosted_repo_dir' 		=> 'git@bitbucket.org:oreganocreative/oregano-website.git',
		'website_dir' 			=> $base_dir . 'public_html',
		'git_dir' 				=> $base_dir . 'git_repos',
		'logfiles_dir'			=> $base_dir . 'pre-deployment',
		'deploy_logfiles_dir'	=> $base_dir . 'deployment',

		// below are the default values of all other possible options
		//  probably no need to change these often
		
		'log' 					=>  'deployments.log', // name of the logfile
		'deploy_class'			=> 'https://raw.github.com/farleyta/git-post-deploy/master/DeployClass.php', // Default location of DeployClass.php file\
		'max_log_size' 			=>  10000, // in bytes = 10KB
		'date_format' 			=>  'Y-m-d H:i:sP' //default date_format
	);

$pre_deploy = new PreDeploy($pre_deploy_options);

$pre_deploy->prep_website_dir();
$pre_deploy->prep_git_dir();
$pre_deploy->clone_git_repo();
$pre_deploy->update_git_config();
$pre_deploy->move_git_from_working_tree();
$pre_deploy->move_workingtree();
$pre_deploy->setup_deploy_class();
$pre_deploy->final_cleanup();

?>