<?php

namespace ItzLightyHD\KnockbackFFA\listeners;

use ItzLightyHD\KnockbackFFA\Loader;
use ItzLightyHD\KnockbackFFA\utils\GameSettings;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\Listener;

use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class EssentialsListener implements Listener {

    /** @var Loader $plugin */
    private Loader $plugin;
    public array $cooldown = [];
    /** @var self $instance */
    protected static EssentialsListener $instance;

    public function __construct(Loader $plugin)
    {
        $this->plugin = $plugin;
        self::$instance = $this;
    }

    public static function getInstance(): self
    {
        return self::$instance;
    }

    public function onBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        if($player->getWorld()->getFolderName() === GameSettings::getInstance()->world) {
            $event->cancel();
        }
    }

    public function onHunger(PlayerExhaustEvent $event): void
    {
        if($event->getPlayer()->getWorld()->getFolderName() === GameSettings::getInstance()->world) {
            $event->cancel();
        }
    }

    public function onDrop(PlayerDropItemEvent $event): void {
        $player = $event->getPlayer();
        if($player->getWorld()->getFolderName() === GameSettings::getInstance()->world) {
            $event->cancel();
        }
    }

    public function onEntityShootBow(EntityShootBowEvent $event): void
    {
        $entity = $event->getEntity();
        if (($entity instanceof Player) && $entity->getWorld()->getFolderName() === GameSettings::getInstance()->world) {
            $x = $entity->getLocation()->getX();
            $y = $entity->getLocation()->getY();
            $z = $entity->getLocation()->getZ();
            $xx = $entity->getWorld()->getSafeSpawn()->getX();
            $yy = $entity->getWorld()->getSafeSpawn()->getY();
            $zz = $entity->getWorld()->getSafeSpawn()->getZ();
            $sr = GameSettings::getInstance()->getConfig()->get("protection-radius");

            if (abs($xx - $x) < $sr && abs($yy - $y) < $sr && abs($zz - $z) < $sr) {
                $event->cancel();
                $entity->sendMessage(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§cYou can't use that item here!");
            }
        }
    }

    public function onItemUse(PlayerItemUseEvent $event): void
    {
        $player = $event->getPlayer();
        if (($event->getItem()->getId() === ItemIds::SNOWBALL) && $player->getWorld()->getFolderName() === GameSettings::getInstance()->world) {
            $x = $player->getLocation()->getX();
            $y = $player->getLocation()->getY();
            $z = $player->getLocation()->getZ();
            $xx = $player->getWorld()->getSafeSpawn()->getX();
            $yy = $player->getWorld()->getSafeSpawn()->getY();
            $zz = $player->getWorld()->getSafeSpawn()->getZ();
            $sr = GameSettings::getInstance()->getConfig()->get("protection-radius");

            if (abs($xx - $x) < $sr && abs($yy - $y) < $sr && abs($zz - $z) < $sr) {
                $event->cancel();
                $player->sendMessage(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§cYou can't use that item here!");
            }
        }
        if(($player->getWorld()->getFolderName() === GameSettings::getInstance()->world) && $event->getItem()->getCustomName() === "§r§eLeap§r") {
            if(!isset($this->cooldown[$player->getName()])) {
                $this->cooldown[$player->getName()] = 0;
            }
            if($this->cooldown[$player->getName()] <= time()) {
                $directionvector = $player->getDirectionVector()->multiply(4 / 2);
                $dx = $directionvector->getX();
                $dz = $directionvector->getZ();
                $player->setMotion(new Vector3($dx, 1, $dz));
                $this->cooldown[$player->getName()] = time() + 10;
            } else {
                $player->sendMessage(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§cWait §e" . (10 - ((time() + 10) - $this->cooldown[$player->getName()])) . "§c seconds before using your leap again.");
            }
        }
    }

}