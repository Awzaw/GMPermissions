<?php

namespace GMPermissions;

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
        } else {
            return false;
        }
    }

    /**
     * @param PlayerCommandPreprocessEvent $event
     *
     * @priority MONITOR
     */
    public function onPlayerCommand(PlayerCommandPreprocessEvent $event) {

        $p = $event->getPlayer();
        $msg = $event->getMessage();
        $args = explode(" ", $msg);

        if ((strtolower($args[0]) === "/gamemode" || strtolower($args[0]) === "/gm") && (($args[1] === "3" || strtolower($args[1]) === "spectator"))) {

            if ($this->myconfig->get("restrictspectator") && !$p->hasPermission("gmchange.spectator")) {
                $event->setCancelled(true);
                $p->sendMessage(TEXTFORMAT::RED . $this->myconfig->get("nopermspectator"));
                return false;
            }
        }

        if ((strtolower($args[0]) === "/gamemode" || (strtolower($args[0]) === "/gm")) && sizeof($args) > 2 && strtolower($args[2]) !== strtolower($p->getName())) {
            if (!($p->hasPermission("gmchange.others"))) {
                $event->setCancelled(true);
                $p->sendMessage(TEXTFORMAT::RED . $this->myconfig->get("nopermsgamemodeother"));
                return false;
            }

            $target = $this->getServer()->getPlayer($args[2]);

            if ($this->myconfig->get("restrictcmode") && ($target instanceof Player) && !$target->hasPermission("gmchange.creative")) {
                $event->setCancelled(true);
                $p->sendMessage(TEXTFORMAT::RED . $this->myconfig->get("nopermscmode"));
                return false;
            }
        }

        if (sizeof($args) > 2 && in_array(strtolower($args[2]), $this->enabled)) {
            $event->setCancelled(true);
            $p->sendMessage(TEXTFORMAT::RED . $this->myconfig->get("playernogm"));
            return false;
        }
    }

}
