<?php

namespace awzaw\gmpermissions;

use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

class Main extends PluginBase implements Listener {

    private $myconfig;
    private $enabled;

    public function onEnable() {
        $this->enabled = array();

        if (!file_exists($this->getDataFolder())) {
            mkdir($this->getDataFolder());
        }

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->myconfig = new Config($this->getDataFolder() . "config.yml", Config::YAML, array(
            "restrictspectator" => true,
            "restrictcmode" => false,
            "nopermspectator" => "You do not have permission to use Spectator Mode",
            "nopermsgamemodeother" => "You cannot change other players gamemode",
            "playernogm" => "You cannot change that players gamemode",
            "nopermscmode" => "You cannot give that player creative mode"
                )
        );

        //Updates to config

        if (!$this->myconfig->get("nopermscmode")) {
            $this->myconfig->set("nopermscmode", "You cannot give that player creative mode");
            $this->myconfig->save();
        }

        if (!$this->myconfig->get("restrictcmode")) {
            $this->myconfig->set("restrictcmode", false);
            $this->myconfig->save();
        }
    }

    public function onCommand(CommandSender $issuer, Command $cmd, $label, array $args) {

        if ((strtolower($cmd->getName()) == "nogm") && ($issuer instanceof Player) && ($issuer->hasPermission("gmchange.nogm"))) {
            if (isset($this->enabled[strtolower($issuer->getName())])) {
                unset($this->enabled[strtolower($issuer->getName())]);
            } else {
                $this->enabled[strtolower($issuer->getName())] = strtolower($issuer->getName());
            }

            if (isset($this->enabled[strtolower($issuer->getName())])) {
                $issuer->sendMessage("NoGM mode enabled!");
            } else {
                $issuer->sendMessage("NoGM mode disabled!");
            }
            return true;
        }
        return true;
    }
    
    public function onPlayerCommand(PlayerCommandPreprocessEvent $event) {
            
            $msg = explode(" ", strtolower($event->getMessage()));
            
            if ($msg[0] === "/gamemode" or $msg[0] === "/gm") {
                
                if ($msg[1] === "c") {
                    
                    if ($this->myconfig->get("restrictcmode") === true) {
                        $event->getPlayer()->sendMessage("CMode is restricted!");
                        $event->setCancelled();
                    } elseif (!$event->getPlayer()->hasPermission("gmchange.creative") or !$event->getPlayer()->hasPermission("gmchange")) {
                        $event->getPlayer()->sendMessage("You don't have permission for CMODE!");
                        $event->setCancelled();
                    }
                    
                } elseif ($msg[1] === "spectator") {
                    
                    if ($this->myconfig->get("restrictspectator") === true) {
                        $event->getPlayer()->sendMessage("Spectator Mode is restricted!");
                        $event->setCancelled();
                    } elseif (!$event->getPlayer()->hasPermission("gmchange.spectator") or !$event->getPlayer()->hasPermission("gmchange")) {
                        $event->getPlayer()->sendMessage("You don't have perms for Spectator Mode!");
                        $event->setCancelled();
                    }
                    
                }
                
            }
    }
    
}
