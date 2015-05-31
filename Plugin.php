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

	public function testEvent(Event $event)
	{
		echo '<pre>';
		var_dump("here");
		echo '</pre>';
	}
}