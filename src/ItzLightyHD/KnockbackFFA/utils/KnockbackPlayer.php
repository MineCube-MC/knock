<?php

namespace ItzLightyHD\KnockbackFFA\utils;

use ItzLightyHD\KnockbackFFA\listeners\EssentialsListener;
use ItzLightyHD\KnockbackFFA\Loader;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\types\PlayerAuthInputFlags;
use pocketmine\player\Player;
use pocketmine\Server;

class KnockbackPlayer implements Listener
{

    /** @var self $instance */
    protected static KnockbackPlayer $instance;
    public array $lastDmg = [];
    public array $killstreak = [];
    /** @var Loader $plugin */
    private Loader $plugin;

    public function __construct(Loader $plugin)
    {
        $this->plugin = $plugin;
        self::$instance = $this;
    }

    public function onJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $name = strtolower($player->getName());
        $this->lastDmg[$name] = "none";
        $this->killstreak[$name] = 0;
        if ($player->getWorld()->getFolderName() === GameSettings::getInstance()->world) {
            $lobbyWorld = GameSettings::getInstance()->lobby_world;
            $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName($lobbyWorld)?->getSpawnLocation());
        }
    }

    public static function getInstance(): self
    {
        return self::$instance;
    }

    public function onQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        foreach (Server::getInstance()->getOnlinePlayers() as $p) {
            $world = $p->getWorld()->getFolderName();
            if (($world === GameSettings::getInstance()->world) && $this->lastDmg[strtolower($p->getName())] === strtolower($player->getName())) {
                $this->lastDmg[strtolower($p->getName())] = "none";
            }
        }
    }

    /**
     * @param DataPacketReceiveEvent $event
     * @priority HIGHEST
     */

    public function onDataPacketReceive(DataPacketReceiveEvent $event): void
    {
        $player = $event->getOrigin()->getPlayer();
        $packet = $event->getPacket();
        if ($player?->getWorld()->getFolderName() === GameSettings::getInstance()->world) {
            if (!$packet instanceof PlayerAuthInputPacket) {
                return;
            }
            if ($player === null) {
                return;
            }
            if (!$packet->hasFlag(PlayerAuthInputFlags::JUMP_DOWN)) {
                return;
            }
            if (!isset(EssentialsListener::getInstance()->cooldown[$player->getName()]) || EssentialsListener::getInstance()->cooldown[$player->getName()] <= time()) {
                $directionvector = $player->getDirectionVector()->multiply(4 / 2);
                $dx = $directionvector->getX();
                $dz = $directionvector->getZ();
                $player->setMotion(new Vector3($dx, 1, $dz));
                EssentialsListener::getInstance()->cooldown[$player->getName()] = time() + 10;
            } else {
                $player->sendMessage(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§cWait §e" . (10 - ((time() + 10) - EssentialsListener::getInstance()->cooldown[$player->getName()])) . "§c seconds before using your leap/double jump again.");
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
