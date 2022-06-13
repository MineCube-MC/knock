<?php

namespace ItzLightyHD\KnockbackFFA\utils;

use ItzLightyHD\KnockbackFFA\Loader;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\Player;

class KnockbackPlayer implements Listener {

    /** @var Loader $plugin */
    private Loader $plugin;
    public array $lastDmg = [];
    public array $killstreak = [];
    /** @var self $instance */
    protected static KnockbackPlayer $instance;

    public function __construct(Loader $plugin)
    {
        $this->plugin = $plugin;
        self::$instance = $this;
    }

    public static function getInstance(): self
    {
        return self::$instance;
    }

    public function onJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $name = strtolower($player->getName());
        $this->lastDmg[$name] = "none";
        $this->killstreak[$name] = 0;
        if($player->getWorld()->getFolderName() === GameSettings::getInstance()->world) {
            $lobbyWorld = GameSettings::getInstance()->lobby_world;
            $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName($lobbyWorld)?->getSpawnLocation());
        }
    }

    public function onQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        foreach(Server::getInstance()->getOnlinePlayers() as $p) {
            $world = $p->getWorld()->getFolderName();
            if(($world === GameSettings::getInstance()->world) && $this->lastDmg[strtolower($p->getName())] === strtolower($player->getName())) {
                $this->lastDmg[strtolower($p->getName())] = "none";
            }
        }
    }

    public function playSound(string $soundName, ?Player $player): void
    {
        $pk = new PlaySoundPacket();
        $pk->soundName = $soundName;
        $pk->x = $player?->getLocation()->getX();
        $pk->y = $player?->getLocation()->getY();
        $pk->z = $player?->getLocation()->getZ();
        $pk->volume = 500;
        $pk->pitch = 1;
        Server::getInstance()->broadcastPackets($player?->getWorld()->getPlayers(), [$pk]);
    }

}