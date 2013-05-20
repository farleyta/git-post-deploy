<?php

// Set default time zone. 
// List here: http://www.php.net/manual/en/timezones.america.php
date_default_timezone_set('America/Lima');

class PreDeploy {

	/**
	* The Hosted Repository address.  This is tested for use with BitBucket, but 
	*  would likely work with little extra effort on GitHub as well.
	* 
	* @var string
	*/
	private $_hosted_repo_dir;

	/**
	* The directory where your website files are located. Must be absolute path, 
	*  for HostGator, the default is /home/YOURUSERAME/public_html
	* 
	* @var string
	*/
	private $_website_dir;

	/**
	* The directory where your .git repo will live. Must be absolute path.
	* 
	* @var string
	*/
	private $_git_dir;

	/**
	* The name of the directory to create for the log file and backup files. 
	*  Must be an absolute path.
	* 
	* @var string
	*/
	private $_logfiles_dir;

	/**
	* The name of the file that will be used for logging deployments. Set to 
	* FALSE to disable logging.
	* 
	* @var string
	*/
	private $_log = 'pre-deployment.log';

	/**
	* The max filesize of the log file, in bytes.
	* 
	* @var string
	*/
	private $_max_log_size = 10000;

	/**
	* The timestamp format used for logging.
	* 
	* @link    http://www.php.net/manual/en/function.date.php
	* @var     string
	*/
	private $_date_format = 'Y-m-d H:i:sP';

	/**
	* Sets up defaults.
	* 
	* @param  array   $options       Information about our setup
	*/
	public function __construct($options = array()) {

		$available_options = array('hosted_repo_dir', 'website_dir', 'git_dir', 'logfiles_dir', 'log', 'date_format');

		foreach ($options as $option => $value) {
			if (in_array($option, $available_options)) {
				$this->{'_'.$option} = $value;
			}
		}

		// Some of the options are required, display an error if they're missing
		if ( empty($this->_hosted_repo_dir) || empty($this->_website_dir) || empty($this->_git_dir) || empty($this->_logfiles_dir) ) {
			die("FATAL ERROR: Missing necessary arguments...\n");
		} else {
			$this->log('---------------------------', 'INITIALIZE');
			$this->log('Pre-deployment preparation process beginning...');
		}

	}

	/**
	* Writes a message to the log file.
	* 
	* @param  string  $message  The message to write
	* @param  string  $type     The type of log message (e.g. INFO, DEBUG, ERROR, etc.)
	*/
	public function log($message, $type = 'STATUS') {

		if ($this->_log && $this->_logfiles_dir) {

			// Set the name of the logfiles directory
			$logfiles_dir = $this->_logfiles_dir;
			// Set the name and location of the log file
			$logfile = $logfiles_dir . DIRECTORY_SEPARATOR . $this->_log;
			// Set the name and location of the log backup .zip
			$log_backup = $logfiles_dir . DIRECTORY_SEPARATOR . 'log_backup.zip';

			if ( ! file_exists($logfiles_dir)) {
				// Create the logfiles directory and make it writable
				mkdir($logfiles_dir, 0755);
			}

			if ( ! file_exists($logfile)) {
				// Create the log file
				file_put_contents($logfile, '');

				// Allow anyone to write to log files
				chmod($logfile, 0666);
			}

			// Limit to MAX file size of the log
			elseif ( filesize($logfile) > $this->_max_log_size ) {

				// Make a .zip of the log file and then create a new one
				exec('zip -rq ' . $log_backup . ' ' . $logfile);
				echo("Log file zipped");

				// Then remove the old, large log file
				exec('rm -rf ' . $logfile);
				echo("Log file removed");

				// Create a new one
				file_put_contents($logfile, '');
				echo("new log file created");

				// Allow anyone to write to log files
				chmod($logfile, 0666);

			}

			// Finally, make sure the logfile backup doesn't get too large
			if ( file_exists($log_backup) && filesize($log_backup) > $this->_max_log_size) {
				// Just remove it
				exec('rm -rf ' . $log_backup);
				echo("Log backup too large - removed");
			}

			// Write the message to the log file
			// Format: time --- type: message
			file_put_contents($logfile, date($this->_date_format).' --- '.$type.': '.$message.PHP_EOL, FILE_APPEND);

			//Also print to screen
			echo date($this->_date_format) . ' --- ' . $type . ': ' . $message . "\n";
		}
	}

	/**
	* Prepares the website directory, and also makes a backup of any existing
	* files in the specified directory.  The end goal is to have a completely 
	* empty directory at the location specified as $_website_dir.
	*/
	public function prep_website_dir() {

		// the name of the backup file you'd like to make
		$backup_zip_name = 'pre-deployment-backup.zip';

		// First make a copy of the current directory if it exists, as backup
		if ( file_exists($this->_website_dir) ) {

			// Move the right directory
			exec('cd ' . $this->_website_dir);
			$this->log('Changing working directory to website directory... ');

			// Make the .zip backup file of website_dir and place in logfiles_dir
			exec('zip -rq ' . $this->_logfiles_dir.DIRECTORY_SEPARATOR.$backup_zip_name . ' ' . $this->_website_dir);
			$this->log('Making a .zip backup of current website directory... ');

			// Finally remove all files of the $_website_dir, including hidden - EXCEPT FOR THIS PHP FILE!

			// Originally tried in shell, but had issues - this doesn't work when run through php's exec()?
			// exec('shopt -s extglob', $rm_output); // First set up shell to accept the !() character
			// exec('rm -rf ./!('.basename(__FILE__).')', $rm_output);


			// Scan through all files and send for delete, accept those in second
			//  array - INCLUDING THIS PHP FILE
			$files = array_diff( scandir( $this->_website_dir), array('.','..', basename(__FILE__) ) );
			
			foreach ($files as $file) { 
				if (is_dir($this->_website_dir.DIRECTORY_SEPARATOR.$file)) {
					//send it to the recursive delete directory function
					$this->rrmdir($this->_website_dir.DIRECTORY_SEPARATOR.$file);
				} else {
					//delete the file
					unlink($this->_website_dir.DIRECTORY_SEPARATOR.$file);
				}
			}

			$this->log('Removing ALL files from website directory, including hidden files... ');

		} 
		// Otherwise, create the website directory in the proper location
		else {
			exec('mkdir ' . $this->_website_dir);
			$this->log('Creating website directory... ');
		}

		$this->log('Website Directory Prepped...');

	}

	/**
	* Prepares the .git directory on the local server
	*/
	public function prep_git_dir() {

		// the name of the backup file you'd like to make
		$backup_git_zip = 'pre-deployment-git-backup.zip';
		$local_git_repo_name = $this->get_local_git_repo_name();

		// First make a copy of the current directory if it exists, as backup
		if ( file_exists($this->_git_dir) ) {

			// Move the right directory
			exec('cd ' . $this->_git_dir);
			$this->log('Changing working directory to existing .git directory... ');

			if ( file_exists($this->_git_dir . DIRECTORY_SEPARATOR . $local_git_repo_name) ) {

				// Make the .zip backup file and place in logfiles_dir
				exec('zip -rq ' . $this->_logfiles_dir . DIRECTORY_SEPARATOR . $backup_git_zip . ' ' . $this->_git_dir . DIRECTORY_SEPARATOR . $local_git_repo_name);
				$this->log('Making a .zip backup of existing .git directory... ');

				// Finally remove all files of the $_git_dir, including hidden			
				$files = array_diff(scandir($this->_git_dir), array('.','..')); 
			
				foreach ($files as $file) { 
					if (is_dir($this->_git_dir.DIRECTORY_SEPARATOR.$file)) {
						//send it to the recursive delete directory function
						$this->rrmdir($this->_git_dir.DIRECTORY_SEPARATOR.$file);
					} else {
						//delete the file
						unlink($this->_git_dir.DIRECTORY_SEPARATOR.$file);
					}
				}

				$this->log('Removing ALL files from existing .git directory, including hidden files... ');

			}

		} 
		// Otherwise, create the .git directory in the proper location
		else {
			exec('mkdir ' . $this->_git_dir);
			$this->log('Creating .git directory... ');
		}

		$this->log('.git Directory Prepped...');
		
	}

	/**
	* Clones the hosted .git repo into a temporary subdirectory inside of 
	*  $_website_dir
	*/
	public function clone_git_repo() {

		// Create the name for our new repo (defaults to reponame.git)
		$new_repo_name = $this->get_local_git_repo_name();

		// Move the website_dir directory
		exec('cd ' . $this->_website_dir);
		$this->log('Changing working directory to website directory... ');

		// Clone the hosted .git repo
		$this->log('Cloning hosted .git repo into temp directory of website dir... ');
		exec('GIT_WORK_TREE='.$this->_website_dir.DIRECTORY_SEPARATOR.'tmp git clone '.$this->_hosted_repo_dir.' '.$this->_website_dir.DIRECTORY_SEPARATOR.$new_repo_name);
		
		$this->log('Hosted .git repo successfully cloned into temp directory of website dir... ');

	}

	/**
	* Calls a few different functions to finalize the pre-deployment setup
	*/
	public function final_cleanup() {

		// If this PHP file is within the $website_dir, remove it (final step)
		if ( file_exists( $this->_website_dir . DIRECTORY_SEPARATOR . basename(__FILE__) ) ) {

			$this->log('Pre-deployment preparation process successfully completed...');
			$this->log('Self Destructing...');
			$this->log('---------------------------', 'COMPLETE');

			// Final step - remove this file, leaving a clean copy of hosted git repo
			exec('rm -rf ' . $this->_website_dir . DIRECTORY_SEPARATOR . basename(__FILE__) );
		}

	}

	/**
	* Updates .git config to remove /tmp from the workingtree declaration
	*/
	public function update_git_config() {

		// Move the website_dir directory
		exec('cd ' . $this->_website_dir);
		$this->log('Changing working directory to website directory... ');

		$this->log('Changing working tree in .git/config... ');

		// Defaults to $website_dir/reponame.git/config
		$local_git_repo_name = $this->get_local_git_repo_name();
		$path_to_config = $this->_website_dir . DIRECTORY_SEPARATOR . $local_git_repo_name . DIRECTORY_SEPARATOR . 'config';
		// Old and new working tree paths (to be replaced)
		$old_worktree_path = 'worktree = ' . $this->_website_dir . DIRECTORY_SEPARATOR . 'tmp';
		$new_worktree_path = 'worktree = ' . $this->_website_dir;

		// Read contents of config file into this variable
		$config_file_contents = file_get_contents($path_to_config);

		// Find and replace the string
		$config_file_contents = str_replace($old_worktree_path, $new_worktree_path, $config_file_contents);

		// Write the contents back out to config
		file_put_contents($path_to_config, $config_file_contents);

		$this->log('Working tree in .git/config changed successfully... ');

	}

	/**
	* Move .git directory from the working tree
	*/
	public function move_git_from_working_tree() {

		$local_git_repo_name = $this->get_local_git_repo_name();

		// move the file to the $git_dir specified in options
		exec('mv ' . $this->_website_dir . DIRECTORY_SEPARATOR . $local_git_repo_name . ' ' . $this->_git_dir . DIRECTORY_SEPARATOR . $local_git_repo_name);
		$this->log('Moving .git/config to the desired git directory... ');

	}

	/**
	* Move contents of the /tmp directory to $website_dir
	*/
	public function move_workingtree() {

		// Move the website_dir directory
		$this->log('Changing working directory to website directory... ');
		exec('cd ' . $this->_website_dir);

		// move the files
		$this->log('Moving working tree to website dir... ');
		exec('shopt -s dotglob && mv ' . $this->_website_dir . DIRECTORY_SEPARATOR . 'tmp/* ' . $this->_website_dir);	

		// Remove tmp folder from website dir
		$this->log('Removing tmp folder from website dir... ');
		exec('rm -rf ' . $this->_website_dir . DIRECTORY_SEPARATOR . 'tmp');

	}

	/**
	* Get the name of the local .git repo directory - defaults to reponame.git
	*/
	private function get_local_git_repo_name() {

		return basename($this->_hosted_repo_dir);

	}

	/**
	* Recursively remove all directories and files - http://php.net/manual/en/function.rmdir.php
	*
	* @param  string   $dir       The path of the directory to remove
	*/
	private function rrmdir($dir) {
		
		$files = array_diff(scandir($dir), array('.','..'));
		
		foreach ($files as $file) { 
			(is_dir("$dir/$file")) ? $this->rrmdir("$dir/$file") : unlink("$dir/$file");
		}		
		return rmdir($dir); 

	}

}

// This is just an example
$pre_deploy_options = array(
		'hosted_repo_dir' 	=> 'git@bitbucket.org:oreganocreative/oregano-website.git',
		'website_dir' 		=> '/home/oregano/public_html',
		'git_dir' 			=> '/home/oregano/git_repos',
		'logfiles_dir'		=> '/home/oregano/pre-deployment'
	);

$pre_deploy = new PreDeploy($pre_deploy_options);

$pre_deploy->prep_website_dir();
$pre_deploy->prep_git_dir();
$pre_deploy->clone_git_repo();
$pre_deploy->update_git_config();
$pre_deploy->move_git_from_working_tree();
$pre_deploy->move_workingtree();
$pre_deploy->final_cleanup();

?>