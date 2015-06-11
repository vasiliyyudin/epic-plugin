<?php namespace Epic\Plugins;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;


class Plugin implements PluginInterface, EventSubscriberInterface
{
	protected $composer;
	protected $io;

	public function activate(Composer $composer, IOInterface $io)
	{
		$this->composer = $composer;
		$this->io = $io;
	}

	public static function getSubscribedEvents()
	{
		return array(
			ScriptEvents::POST_INSTALL_CMD => 'testEvent',
			ScriptEvents::POST_UPDATE_CMD => 'testEvent',
		);
	}

	public function testEvent(Event $event)
	{
		$dir = __DIR__ . '/../../../';

		$allFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
		$phpFiles = new \RegexIterator($allFiles, '/\.php$/');

		$projClasses = [];
		foreach ($phpFiles as $phpFile) {
			/** @var \SplFileInfo $phpFile */
			if($phpFile->getBasename() != 'Staff.php' && $phpFile->getBasename() != 'LpprStaff.php'){
				continue;
			}

			$content = file_get_contents($phpFile->getRealPath());
			$tokens = token_get_all($content);

			$index = 0;
			$namespace = '';
			$fileUse = [];
			$fileClases = [];

			try {


				do{
					if(is_array($tokens[$index])){
						if($tokens[$index][1] == 'namespace'){
							$namespace = ''; // prevent 2 or more namespaces in one file
							$index += 2;
							while(is_array($tokens[$index])){
								$namespace .= $tokens[$index][1];
								$index++;
							}
							if($tokens[$index] == ';' || $tokens[$index] == '{'){
								continue;
							}
						}
						if($tokens[$index][1] == 'use'){
							$index += 2;
							$class = '';
							$classAs = '';
							while(is_array($tokens[$index])){
								if($tokens[$index][1] == 'as'){
									$index += 2;
									$classAs = $tokens[$index][1];
								} else {
									$class .= $tokens[$index][1];
								}
								$index++;
							}
							if(!$classAs && $class){
								$clArray = explode('\\', $class);
								$classAs = end($clArray);
							}
							if(!is_array($tokens[$index]) && $tokens[$index] == ';'){
								$fileUse[$classAs] = $class;
								$index++;
							}
						}
						if($tokens[$index][1] == 'class'){
							$index += 2;
							$className = $tokens[$index][1];
							$index += 2;
							$extFrom = '';
							if(is_array($tokens[$index]) && $tokens[$index][1] == 'extends'){
								$index++;
							}

							while(is_array($tokens[$index])){
								if($tokens[$index][1] == 'implements')
									break;
								$extFrom .= $tokens[$index][1];
								$index++;
							}
							$extFrom = trim($extFrom);

							$namespace = trim($namespace);
							if(array_key_exists($extFrom, $fileUse)){
								$extFrom = $fileUse[$extFrom];
							} else if(!strpos('\\', $extFrom) !== 0){
								$extFrom = $namespace . '\\' . $extFrom;
							}

							$fileClases[] = [
								'name' => $className,
								'fullName' => $namespace . '\\' . $className,
								'namespace' => $namespace,
								'file' => $phpFile->getRealPath(),
								'extend' => trim($extFrom)
							];

						}
					}
					$index++;
				} while(isset($tokens[$index]));

			}catch (\Exception $e){
				var_dump($e->getMessage() . '  on line   ' . $e->getLine());
				var_dump($tokens[$index]);
			}

			$projClasses = array_merge($projClasses, $fileClases);
		}

		$masterClasses = [
			'Epic\Facade\Facade'
		];

		for($iteration = 0; $iteration < 3; $iteration++){
			foreach ($projClasses as $class) {
				if(in_array($class['extend'], $masterClasses) && !in_array($class['fullName'], $masterClasses)){
					$masterClasses[] = $class['fullName'];
				}
			}
		}

		$result = [];
		foreach ($projClasses as $class) {
			if(in_array($class['extend'], $masterClasses)){
				$result[] = $class;
			}
		}

		var_dump($masterClasses);
		var_dump($result);
	}
}