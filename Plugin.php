<?php namespace Epic\Plugins;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Script\CommandEvent;
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

	public function printT($var){
		var_dump($var);
	}

	public function testEvent(Event $event)
	{
//		foreach (get_declared_classes() as $class) {
//			echo	$class . PHP_EOL;
//		}

		$dir = __DIR__ . '/../../../';
		$this->printT($dir);

		$allFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
		$phpFiles = new \RegexIterator($allFiles, '/\.php$/');

		foreach ($phpFiles as $phpFile) {
			/** @var \SplFileInfo $phpFile */
//			$this->printT($phpFile);
			if($phpFile->getBasename() != 'Staff.php'){
				continue;
			}

			$content = file_get_contents($phpFile->getRealPath());
			$tokens = token_get_all($content);

			for ($index = 0; isset($tokens[$index]); $index++) {
				if (!isset($tokens[$index][0])) {
					continue;
				}
				if (T_INTERFACE === $tokens[$index][0] && $tokens[$index][1] == 'class') {
					$index += 2; // Skip class keyword and whitespace

					if(T_OBJECT_OPERATOR === $tokens[$index][0]){ // extends

					}

//					while (isset($tokens[$index]) && is_array($tokens[$index])) {
//						$namespace .= $tokens[$index++][1];
//					}
				}
//				if (T_CLASS === $tokens[$index][0]) {
//					$index += 2; // Skip class keyword and whitespace
//					$fqcns[] = $namespace.'\\'.$tokens[$index][1];
//				}
			}

			var_dump($tokens);

		}

	}
}