<?php
namespace ForKPower;
class ForKPower
{
	public $config;

	public function __construct($config,$argv)
	{
		$this->config = $config;
		if (empty($argv[1])) {
			$this->mark();
		} else {
			$this->chooseWay($argv);
		}
		$this->signal();
	}

	public function signal()
	{
		pcntl_signal(SIGHUP,  function($signo) /*use ()*/{
            printf("The process has been reload.\n");
            Signal::set($signo);
        });
	}

	private function chooseWay($argv)
	{
		switch ($argv[1]) {
		case 'version':
			$this->version();
			break;
		case 'help':
			$this->help();
			break;
		case 'start':
			$this->start($argv);
			break;
		case 'stop':
			$this->stop($argv);
			break;
		case 'restart':
			$this->restart($argv);
			break;
		case 'status':
			$this->status();
			break;
		default:
			$this->mark();
			break;
		}
	}

	private function mark()
	{
		echo '-------------------------------------------------'.PHP_EOL;
		echo '  fff          r  r   k   	@author:ZhangYuan         '.PHP_EOL;
		echo '  f            r r    k                					'.PHP_EOL;
		echo 'fffff  ooooo   rr     k  k k           				'.PHP_EOL;
		echo '  f    o   o   r      k k              			'.PHP_EOL;
		echo '  f    o   o   r      k k              			'.PHP_EOL;
		echo '  f    ooooo   r      k  k      Power  			'.PHP_EOL;
		echo '---------------------------------------			'.PHP_EOL;
		echo 'php ForKPower.php help @get help'.PHP_EOL;
	}

	private function version()
	{
		echo 'ForKPower@0.0.1'.PHP_EOL;
	}

	private function help()
	{
		echo '--------------------------------------------------'.PHP_EOL;
		echo 'Command:			|Example:'.PHP_EOL;
		echo '---------------------------------------------------'.PHP_EOL;
		echo 'start PHP_File_Path		|php ForKPower.php start file1.php >> file.log'.PHP_EOL;
		echo 'stop PHP_File_Path		|php ForKPower.php stop file2.php'.PHP_EOL;
		echo 'restart PHP_File_Path		|php ForKPower.php reload file3.php'.PHP_EOL;
		echo 'status				|php ForKPower.php runing'.PHP_EOL;
		echo 'version				|php ForKPower.php version'.PHP_EOL;
		echo 'help				|php ForKPower.php help'.PHP_EOL;
		echo '----------------------------------------------------'.PHP_EOL;
	}

	private function getRunLogPath($phpPath)
	{
		if (!is_dir($this->config["runLogPath"])) {
			mkdir($this->config["runLogPath"]);
		}
		return $this->config["runLogPath"].DIRECTORY_SEPARATOR.md5($phpPath).'.log';
	}

	private function start($argv)
	{
		if (empty($argv[2]))
		{
			die("PHP_File_Path can't empty!".PHP_EOL);
		}
		$phpPath = $argv[2];
		$runLogPath = $this->getRunLogPath($phpPath);
		if (file_exists($runLogPath)) {
            echo "The file {$phpPath} runing.".PHP_EOL;
            echo "if not run,del {$runLogPath} ,please!".PHP_EOL;
            exit();
        }
		echo "start `{$phpPath}` success!".PHP_EOL;

		$pid = pcntl_fork();
		if ($pid == -1) {
             die('could not fork');
        } else if ($pid) {
             // we are the parent
             //pcntl_wait($status); //Protect against Zombie children
        	echo "runing!".PHP_EOL;
            exit($pid);
        } else {
        	$fileInfo = [
        	'pid'=>getmypid(),
        	'filePath'=>$phpPath,
        	'uid'=>$this->config["uid"],
        	'gid'=>$this->config["gid"],
        	];
            file_put_contents($runLogPath, json_encode($fileInfo));
            posix_setuid($fileInfo["uid"]);
            posix_setgid($fileInfo["gid"]);
            $runPhp = require ($phpPath);
        }
		
	}

	private function stop($argv)
	{
		$phpPath = $argv[2];
		if (empty($argv[2]))
		{
			die("PHP_File_Path can't empty!".PHP_EOL);
		}
		$runLogPath = $this->getRunLogPath($phpPath);
		if (file_exists($runLogPath)) {
            $fileInfoStr = file_get_contents($runLogPath);
			$fileInfo = json_decode($fileInfoStr,true);
            posix_kill($fileInfo['pid'], 9);
            unlink($runLogPath);
            echo "stop `{$phpPath}` success!".PHP_EOL;
        } else {
        	echo "stop `{$phpPath}` fail!".PHP_EOL;
        }
	}

	private function restart($argv)
	{
		if (empty($argv[2]))
		{
			die("PHP_File_Path can't empty!".PHP_EOL);
		}
		$this->stop($argv);
		$this->start($argv);
		// if (empty($argv[2]))
		// {
		// 	echo "PHP_File_Path can't empty!".PHP_EOL;
		// }
		// $phpPath = $argv[2];
		// $runLogPath = $this->getRunLogPath($phpPath);
		// if (file_exists($runLogPath)) {
  //           $fileInfoStr = file_get_contents($runLogPath);
  //           $fileInfo = json_decode($fileInfoStr,true);
  //           //posix_kill(posix_getpid(), SIGHUP);
  //           posix_kill($fileInfo['pid'], SIGHUP);
  //       }
	}

	private function status()
	{
		if(is_dir($this->config["runLogPath"]))
		{
			if ($dh = opendir($this->config["runLogPath"]))
			{
				while (($file = readdir($dh)) !== false)
				{
					if ($file!="." && $file!="..")
					{
						$fileInfoStr = file_get_contents($this->config["runLogPath"].DIRECTORY_SEPARATOR.$file);
						$fileInfo = json_decode($fileInfoStr,true);
						echo '------------------------------------------------------------------------'.PHP_EOL;
						echo $fileInfo['filePath']."(pid:{$fileInfo['pid']}-uid:{$fileInfo['uid']}-gid:{$fileInfo['gid']}:old):".PHP_EOL;
						echo 'PID	|TTY	|STAT	|TIME	|COMMAND (no list,no run)'.PHP_EOL;
            			system(sprintf("ps ax | grep %s | grep -v grep", $fileInfo['pid']));
            			echo '------------------------------------------------------------------------'.PHP_EOL;
					}
				}
			}
		}
	}
}


// $config = [
// "uid"=>"40",
// "gid"=>"40",
// "runLogPath"=>dirname(__FILE__).DIRECTORY_SEPARATOR."runLog",
// ];
// new ForKPower($config,$argv);




