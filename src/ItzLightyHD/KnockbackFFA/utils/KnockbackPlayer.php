<?php

namespace ItzLightyHD\KnockbackFFA\utils;

use ItzLightyHD\KnockbackFFA\listeners\EssentialsListener;
use ItzLightyHD\KnockbackFFA\Loader;
use ItzLightyHD\KnockbackFFA\tasks\ResetJumpCount;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

class KnockbackPlayer implements Listener
{

    /** @var self $instance */
    protected static KnockbackPlayer $instance;
    public array $jumpcount = [];
    public array $jumptask = [];
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
        $this->jumpcount[$name] = 0;
        $this->killstreak[$name] = 0;
        if(!isset(EssentialsListener::$cooldown[$player->getName()])) {
            EssentialsListener::$cooldown[$player->getName()] = 0;
        }
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
        $name = strtolower($player->getName());
        unset($this->lastDmg[$name], $this->killstreak[$name], EssentialsListener::$cooldown[$name], $this->jumpcount[$name]);
        foreach (Server::getInstance()->getOnlinePlayers() as $p) {
            $world = $p->getWorld()->getFolderName();
            if (($world === GameSettings::getInstance()->world) && $this->lastDmg[strtolower($p->getName())] === $name) {
                $this->lastDmg[strtolower($p->getName())] = "none";
            }
        }
    }

    public function onPlayerJump(PlayerJumpEvent $event): void
    {
        if (GameSettings::getInstance()->doublejump) return;
        $player = $event->getPlayer();
        if (!isset(EssentialsListener::$cooldown[$player->getName()])) {
            EssentialsListener::$cooldown[$player->getName()] = 0;
        }
        if (!isset($this->jumpcount[strtolower($player->getName())])) {
            $this->jumpcount[strtolower($player->getName())] = 0;
        }
        if (($player->getWorld()->getFolderName() === GameSettings::getInstance()->world) && isset($this->jumpcount[strtolower($player->getName())])) {
            $this->jumpcount[strtolower($player->getName())]++;
            if (!isset($this->jumptask[strtolower($player->getName())])) {
                $this->jumptask[strtolower($player->getName())] = Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void {
                    $this->jumpcount[strtolower($player->getName())] = 0;
                    unset($this->jumptask[strtolower($player->getName())]);
                }), 30);
            }
            if ($this->jumpcount[strtolower($player->getName())] === 2) {
                if (EssentialsListener::$cooldown[$player->getName()] <= time()) {
                    $directionvector = $player->getDirectionVector()->multiply(4 / 2);
                    $dx = $directionvector->getX();
                    $dz = $directionvector->getZ();
                    $player->setMotion(new Vector3($dx, 1, $dz));
                    EssentialsListener::$cooldown[$player->getName()] = time() + 10;
                    $this->jumpcount[strtolower($player->getName())] = 0;
                    unset($this->jumptask[strtolower($player->getName())]);
                } else {
                    $player->sendMessage(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§cWait §e" . (10 - ((time() + 10) - EssentialsListener::$cooldown[$player->getName()])) . "§c seconds before using your leap/double jump again.");
                }
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
