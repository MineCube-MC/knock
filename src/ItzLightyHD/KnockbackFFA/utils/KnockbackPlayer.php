<?php

namespace ItzLightyHD\KnockbackFFA\utils;

use ItzLightyHD\KnockbackFFA\event\PlayerDoubleJumpEvent;
use ItzLightyHD\KnockbackFFA\listeners\EssentialsListener;
use ItzLightyHD\KnockbackFFA\Loader;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\types\PlayerAuthInputFlags;
use pocketmine\player\Player;
use pocketmine\Server;
use function microtime;

class KnockbackPlayer implements Listener
{

    /** @var self $instance */
    protected static KnockbackPlayer $instance;
    public array $lastDmg = [];
    public array $killstreak = [];
    public array $jump_queue = [];
    public array $double_jump_queue = [];
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
        if (!isset(EssentialsListener::$cooldown[$player->getName()])) {
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
        unset($this->lastDmg[$name], $this->killstreak[$name], EssentialsListener::$cooldown[$name]);
        foreach (Server::getInstance()->getOnlinePlayers() as $p) {
            $world = $p->getWorld()->getFolderName();
            if ($world === GameSettings::getInstance()->world && isset($this->lastDmg[$name])) {
                // $this->lastDmg[$p->getName()] === $name;
                $this->lastDmg[$name] = "none";
            }
        }
    }

    public function onPlayerJump(PlayerJumpEvent $event): void
    {
        if (!GameSettings::getInstance()->doublejump) return;
        $player = $event->getPlayer();
        if ($player->getWorld()->getFolderName() === GameSettings::getInstance()->world) $this->jump_queue[$player->getName()] = microtime(true);
    }

    public function onDataPacketReceive(DataPacketReceiveEvent $event): void
    {
        if (!GameSettings::getInstance()->doublejump) return;
        $player = $event->getOrigin()->getPlayer();
        $ev = new PlayerDoubleJumpEvent($player);
        $ev->call();
        if ($ev->isCancelled()) return;
        $packet = $event->getPacket();
        if ($player === null || !$packet instanceof PlayerAuthInputPacket || !$packet->hasFlag(PlayerAuthInputFlags::JUMP_DOWN) || !isset($this->jump_queue[$player->getName()]) || microtime(true) - $this->jump_queue[$player->getName()] < 0.05 || (isset($this->double_jump_queue[$player->getName()]) && microtime(true) - $this->jump_queue[$player->getName()] < 0.3)) {
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
            $this->playSound("mob.enderdragon.flap", $player);
            EssentialsListener::$cooldown[$player->getName()] = time() + 10;
        } else {
            $player->sendMessage(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§cWait §e" . (10 - ((time() + 10) - EssentialsListener::$cooldown[$player->getName()])) . "§c seconds before using your leap/double jump again.");
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
