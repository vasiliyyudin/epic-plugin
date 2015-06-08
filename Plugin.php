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
		foreach ($phpFiles as $phpFile) {
			/** @var \SplFileInfo $phpFile */
//			if($phpFile->getBasename() != 'Staff.php'){
//				continue;
//			}

			$content = file_get_contents($phpFile->getRealPath());
			$tokens = token_get_all($content);

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
							'file' => $phpFile->getRealPath()
						];

					}
				}

			}

		}

		var_dump($facadeClass);
	}

	protected function getUseClass($tokens, $index){
		$token = $tokens[$index];

//		if(is_array($token) && $token[0] == T_GLOBAL && $token[1] == 'use'){ // T_GLOBAL = 346???
		if(is_array($token)  && $token[1] == 'use'){
//			$index += 2; // next index = whitespace
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