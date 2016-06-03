<?php

namespace GMPermissions;

use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener{
	
    private $config;
    
	public function onEnable(){
            if (!file_exists($this->getDataFolder())) {
            mkdir($this->getDataFolder());
        }
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
                $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML, array("restrictspectator" => true, "nopermspectator" => "You do not have permission to use Spectator Mode", "nopermsgamemodeother" => "You cannot change other players gamemode"));
    }

	public function onPlayerCommand(PlayerCommandPreprocessEvent $event){
            
		$p = $event->getPlayer();
		$msg = $event->getMessage();
		$args = explode(" ",$msg);
                
		if((strtolower($args[0]) === "/gamemode" || strtolower($args[0]) === "/gm") && (($args[1] === "3" || strtolower($args[1]) === "spectator"))){
			
                if($this->config->get("restrictspectator") && !$p->hasPermission('gmchange.spectator')){
				$event->setCancelled();
				$p->sendMessage(TEXTFORMAT::RED . $this->config->get("nopermspectator"));
                                return false;
			}
		}
                
		if((strtolower($args[0]) === "/gamemode" || (strtolower($args[0]) === "/gm")) && sizeof($args) > 2 && strtolower($args[2]) !== strtolower($p->getName())){
			if(!$p->hasPermission('gmchange.others')){
				$event->setCancelled();
				$p->sendMessage(TEXTFORMAT::RED . $this->config->get("nopermsgamemodeother"));
                                return false;
			}
		}
	}
}