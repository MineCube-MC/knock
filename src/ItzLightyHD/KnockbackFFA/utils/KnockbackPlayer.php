<?php

namespace ItzLightyHD\KnockbackFFA\utils;

use ItzLightyHD\KnockbackFFA\event\PlayerDoubleJumpEvent;
use ItzLightyHD\KnockbackFFA\listeners\EssentialsListener;
use ItzLightyHD\KnockbackFFA\Utils;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\types\PlayerAuthInputFlags;
use pocketmine\Server;

use function microtime;

final class KnockbackPlayer implements Listener
{
    /** @var self $instance */
    protected static KnockbackPlayer $instance;
    /** @var array<string> */
    public array $lastDmg = [];
    /** @var array<int|string> */
    public array $killstreak = [];
    /** @var array<float> */
    public array $jump_queue = [];
    /** @var array<float> */
    public array $double_jump_queue = [];

    public function __construct()
    {
        self::$instance = $this;
    }

    /**
     * @param PlayerJoinEvent $event
     * @return void
     * @priority HIGH
     */
    public function onJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $name = strtolower($player->getName());
        $this->lastDmg[$name] = "none";
        $this->killstreak[$name] = 0;
        if (!isset(EssentialsListener::$cooldown[$player->getName()])) {
            EssentialsListener::$cooldown[$player->getName()] = 0;
        }
        if ($player->getWorld()->getFolderName() === GameSettings::getInstance()->world) {
            $lobbyWorld = Server::getInstance()->getWorldManager()->getWorldByName(GameSettings::getInstance()->lobby_world);
            if ($lobbyWorld !== null) {
                $player->teleport($lobbyWorld->getSpawnLocation());
            } else {
                $player->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()?->getSpawnLocation());
            }
        }
    }

    /**
     * @return self
     */
    public static function getInstance(): self
    {
        return self::$instance;
    }

    /**
     * @param PlayerQuitEvent $event
     * @return void
     * @priority HIGH
     */
    public function onQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        $name = strtolower($player->getName());
        unset($this->lastDmg[$name], $this->killstreak[$name], EssentialsListener::$cooldown[$name]);
        foreach (Server::getInstance()->getOnlinePlayers() as $p) {
            $world = $p->getWorld()->getFolderName();
            if (isset($this->lastDmg[$name]) && $world === GameSettings::getInstance()->world) {
                // $this->lastDmg[$p->getName()] === $name;
                $this->lastDmg[$name] = "none";
            }
        }
    }

    /**
     * @param PlayerJumpEvent $event
     * @return void
     * @priority HIGH
     */
    public function onPlayerJump(PlayerJumpEvent $event): void
    {
        if (!GameSettings::getInstance()->doublejump) {
            return;
        }
        $player = $event->getPlayer();
        if ($player->getWorld()->getFolderName() === GameSettings::getInstance()->world) {
            $this->jump_queue[$player->getName()] = microtime(true);
        }
    }

    /**
     * @param DataPacketReceiveEvent $event
     * @return void
     * @priority MONITOR
     */
    public function onDataPacketReceive(DataPacketReceiveEvent $event): void
    {
        if (!GameSettings::getInstance()->doublejump) {
            return;
        }
        $player = $event->getOrigin()->getPlayer();
        if ($player === null) {
            return;
        }
        $ev = new PlayerDoubleJumpEvent($player);
        $ev->call();
        if ($ev->isCancelled()) {
            return;
        }
        $packet = $event->getPacket();
        if (!$packet instanceof PlayerAuthInputPacket || !$packet->hasFlag(PlayerAuthInputFlags::JUMP_DOWN) || !isset($this->jump_queue[$player->getName()]) || microtime(true) - $this->jump_queue[$player->getName()] < 0.05 || (isset($this->double_jump_queue[$player->getName()]) && microtime(true) - $this->jump_queue[$player->getName()] < 0.3)) {
            return;
        }
        if (!isset(EssentialsListener::$cooldown[$player->getName()])) {
            EssentialsListener::$cooldown[$player->getName()] = 0;
        }
        $this->double_jump_queue[$player->getName()] = microtime(true);
        unset($this->jump_queue[$player->getName()]);
        if (EssentialsListener::$cooldown[$player->getName()] <= time()) {
            $directionvector = $player->getDirectionVector()->multiply(4 / 2);
            $dx = $directionvector->getX();
            $dz = $directionvector->getZ();
            $player->setMotion(new Vector3($dx, 1, $dz));
            Utils::playSound("mob.enderdragon.flap", $player);
            EssentialsListener::$cooldown[$player->getName()] = time() + 10;
        } else {
            $player->sendMessage(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§cWait §e" . (10 - ((time() + 10) - EssentialsListener::$cooldown[$player->getName()])) . "§c seconds before using your leap/double jump again.");
        }
    }
}
