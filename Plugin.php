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

		$facadeClass = array();
		$classMap = [];
		foreach ($phpFiles as $phpFile) {
			/** @var \SplFileInfo $phpFile */
			if($phpFile->getBasename() != 'Staff.php'){
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
							$index += 2;
							while(is_array($tokens[$index])){
								$namespace .= $tokens[$index][1];
								$index++;
							}
							if($tokens[$index] == ';'){
								continue;
							}
//						if(!$namespace)
//							$namespace = '\\';
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

//						if(is_array($tokens[$index]) && $tokens[$index] == ' '){
//							$index++;
//							if(is_array($tokens[$index]) && $tokens[$index] == 'extends'){
//
//							} else {
//								continue;
//							}
//						} else {
//							continue;
//						}

							while(is_array($tokens[$index])){
								if($tokens[$index][1] == 'implements')
									break;
								$extFrom .= $tokens[$index][1];
								$index++;
							}

							$fileClases[] = [
								'name' => $className,
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


			var_dump($fileUse);
			var_dump($fileClases);
			continue;


			$classForExtend = false;
			$namespace = '';
			for ($index = 0; isset($tokens[$index]); $index++) {
				if(is_array($tokens[$index]) && $tokens[$index][1] == 'namespace'){
					$nmIndex = $index + 2;
					do {
						$namespace .= $tokens[$nmIndex][1];
						$nmIndex++;
					} while (is_array($tokens[$nmIndex]));
				}
				if(!$classForExtend){
					$classForExtend = $this->getUseClass($tokens, $index);
					if($classForExtend){
						continue;
					}
				}

				if(is_array($tokens[$index]) && $tokens[$index][1] == 'class' && is_array($tokens[$index + 4]) && $tokens[$index + 4][1] == 'extends'){
					if(is_array($tokens[$index + 6]) && $tokens[$index + 6][1] == $classForExtend && is_array($tokens[$index + 2])){
						$className = $tokens[$index + 2][1];
						$facadeClass[] = [
							'name' => $className,
							'namespace' => $namespace,
							'file' => $phpFile->getRealPath(),
							'extend' => ''
						];

					}
				}

			}

		}

//		var_dump($facadeClass);
	}

	protected function getUseClass($tokens, $index){
		$token = $tokens[$index];

		if(is_array($token)  && $token[1] == 'use'){
			// use Epic\Facade\Facade;
			if($tokens[$index + 2][1] == 'Epic' && $tokens[$index + 4][1] == 'Facade' && $tokens[$index + 6][1] == 'Facade'){
				if($tokens[$index + 7] == ';'){
					return 'Facade';
				} else {
					// use Epic\Facade\Facade as FF;
					return $tokens[$index + 10][1];
				}
			}
		}
		return false;
	}

}