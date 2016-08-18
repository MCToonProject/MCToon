<?php
namespace yuu528\MCToon;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Server as Srv;

class Main extends PluginBase implements Listener{
	function onEnable(){
		Srv::getInstance()->getPluginManager()->registerEvents($this,$this);
		Srv::getInstance()->getLogger()->info("[MCToon]有効");
	}

	function onDisable(){
		Srv::getInstance()->getLogger()->info("[MCToon]無効");
	}
}