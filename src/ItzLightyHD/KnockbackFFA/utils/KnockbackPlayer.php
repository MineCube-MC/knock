<?php

namespace ItzLightyHD\KnockbackFFA\utils;

use ItzLightyHD\KnockbackFFA\Loader;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;

class KnockbackPlayer implements Listener {

    /** @var Loader $plugin */
    private $plugin;
    /** @var self $instance */
    protected static $instance;

    public function __construct(Loader $plugin)
    {
        $this->plugin = $plugin;
        self::$instance = $this;
    }

    public static function getInstance(): self
    {
        return self::$instance;
    }

    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        $name = strtolower($player->getName());
        $this->lastDmg[$name] = "none";
        $this->killstreak[$name] = 0;
        if($player->getLevel()->getFolderName() == GameSettings::getInstance()->getConfig()->get("arena")) {
            $lobbyWorld = GameSettings::getInstance()->getConfig()->get("lobby-world");
            $player->teleport(Server::getInstance()->getLevelByName($lobbyWorld)->getSpawnLocation());
        }
    }

    public function onQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        foreach(Server::getInstance()->getOnlinePlayers() as $p) {
            $world = $p->getLevel()->getFolderName();
            if($world == GameSettings::getInstance()->getConfig()->get("arena")) {
                if($this->lastDmg[strtolower($p->getName())] == strtolower($player->getName())) {
                    $this->lastDmg[strtolower($p->getName())] = "none";
                }
            }
        } 
    }

    public function playSound(string $soundName, Player $player) {
        $pk = new PlaySoundPacket();
        $pk->soundName = $soundName;
        $pk->x = $player->getX();
        $pk->y = $player->getY();
        $pk->z = $player->getZ();
        $pk->volume = 500;
        $pk->pitch = 1;
        Server::getInstance()->broadcastPacket($player->getLevel()->getPlayers(), $pk);
    }

}