<?php

class Deploy {

	/**
	* The directory where your website is located. Must be an absolute path.
	* 
	* @var string
	*/
	private $_website_dir;

	/**
	* The directory where your website is located. Must be an absolute path.
	* 
	* @var string
	*/
	private $_git_dir;

	/**
	* The directory where the log file and this Class file should live. Must be an absolute path.
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
	private $_log = 'deployments.log';

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
	* The IP Address of the repo's server
	* 
	* @var string
	*/
	private $_repo_ipaddr = '63.246.22.222';

	/**
	* The name of the branch to pull from.
	* 
	* @var string
	*/
	private $_branch = 'master';

	/**
	* The name of the remote to pull from.
	* 
	* @var string
	*/
	private $_remote = 'origin';

	/**
	* Sets up defaults.
	* 
	* @param  string  $directory  Directory where your website is located
	* @param  array   $data       Information about the deployment
	*/
	public function __construct($options = array()) {

		$available_options = array('website_dir', 'git_dir', 'logfiles_dir', 'log', 'max_log_size', 'date_format', 'repo_ipaddr', 'branch', 'remote');

		foreach ($options as $option => $value) {
			if (in_array($option, $available_options)) {
				$this->{'_'.$option} = $value;
			}
		}

		// Some of the options are required, display an error if they're missing
		if ( empty($this->_website_dir) || empty($this->_git_dir) || empty($this->_logfiles_dir) ) {
			die("FATAL ERROR: Missing necessary arguments...\n");
		} else {
			$this->log('---------------------------', 'INITIALIZE');
			$this->log('Pre-deployment preparation process beginning...');
		}

		// Check to be sure the IP Address of the request came from the repo
		$requestIP = $_SERVER['REMOTE_ADDR'];
		// $this->check_IP_address($requestIP, $this->_repo_ipaddr);

		// Make sure directory setup is correct
		$this->check_setup();

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
	* Verifies that the provided IP Address matches the repo's ipaddress
	*
	*  @param  string  $ipaddr  The IP Address of the POST request
	*  @param  string  $message  The IP Address that the repo should be requesting from
	*/
	public function check_IP_address($ipaddr, $repo_ipaddr) {

		$this->log('Request IP: '.$ipaddr, 'IPADDR');
		$this->log('Repo IP: '.$repo_ipaddr, 'IPADDR');

		if ($ipaddr == $repo_ipaddr) {
			$this->log('Success: IP Addresses Match...');
			return true;
		} else {
			$this->log('Security Error - IP Addresses don\'t match...', 'ERROR');
			die('Security Error - IP Addresses don\'t match...');
		}

	}

	/**
	* Make sure all files / directories are set up properly to run the deployment
	*/
	public function check_setup() {

		if ( ! file_exists($this->_website_dir) || ! is_dir($this->_website_dir) ) {
			$this->log('Setup Error - Website Directory doesn\'t exist...', 'ERROR');
			die('Setup Error - Website Directory doesn\'t exist...');
		}

		if ( ! file_exists($this->_git_dir) || ! is_dir($this->_git_dir) ) {
			$this->log('Setup Error - .git Directory doesn\'t exist...', 'ERROR');
			die('Setup Error - .git Directory doesn\'t exist...');
		}

		return true;

	}

	/**
	* Executes the necessary commands to deploy the website.
	*/
	public function execute() {
		try {
			// Change directories to go to the web directory
			exec('cd '.$this->_website_dir);
			$this->log('Changing working directory to website directory... ');

			// Update the local repository
			exec('GIT_DIR='.$this->_git_dir.' git pull '.$this->_remote.' '.$this->_branch, $output);
			$this->log('Pulling in changes... '.implode(' ', $output));

			$this->log('Deployment successful.');
		}
		catch (Exception $e) {
			$this->log($e, 'ERROR');
		}
	}

}

?>