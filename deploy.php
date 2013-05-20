<?php

//TODO : Change the pre-deployment.php script to setup the DeployClass file and
// add this php to the public_html files...

// This file must be present for the auto-deploy to work
require_once('/home/oregano/deployment/DeployClass.php');

$deployment_options = array(
		'website_dir' 		=> '/home/oregano/public_html',
		'git_dir' 			=> '/home/oregano/git_repos/oregano-website.git',
		'logfiles_dir'		=> '/home/oregano/deployment'
	);

$deploy = new Deploy($deployment_options);

$deploy->execute();

?>