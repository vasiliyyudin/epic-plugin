<?php namespace Epic\Plugins;
require_once __DIR__ . '/../../../vendor/autoload.php';

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Script\CommandEvent;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

use Symfony\Component\Finder\Finder;
use hanneskod\classtools\Iterator\ClassIterator;



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
		$dir = __DIR__ . '/../../../project/';
		echo $dir . PHP_EOL;

		$finder = new Finder;
		$iter = new ClassIterator($finder->in($dir));
		$iter->enableAutoloading();

		foreach ($iter->type('Epic\Facade\Facade') as $class) {
			echo $class->getName() . PHP_EOL;
		}



//		// Print the file names of classes, interfaces and traits in 'src'
//		foreach ($iter->getClassMap() as $classname => $splFileInfo) {
//			echo $classname.': '.$splFileInfo->getRealPath();
//		}

	}
}