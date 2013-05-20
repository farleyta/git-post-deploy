<?php

require_once('PreDeployClass.php');

$pre_deploy_options = array(
		'hosted_repo_dir' 		=> 'git@bitbucket.org:oreganocreative/oregano-website.git',
		'website_dir' 			=> '/home/oregano/public_html',
		'git_dir' 				=> '/home/oregano/git_repos',
		'logfiles_dir'			=> '/home/oregano/pre-deployment',
		'deploy_class'			=> 'https://raw.github.com/farleyta/git-post-deploy/master/DeployClass.php',
		'deploy_logfiles_dir'	=> '/home/oregano/deployment'
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