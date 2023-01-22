<?php

namespace ItzLightyHD\KnockbackFFA\listeners;

use ItzLightyHD\KnockbackFFA\event\GameJoinEvent;
use ItzLightyHD\KnockbackFFA\event\GameQuitEvent;
use ItzLightyHD\KnockbackFFA\utils\GameSettings;
use ItzLightyHD\KnockbackFFA\utils\KnockbackKit;
use ItzLightyHD\KnockbackFFA\utils\KnockbackPlayer;
use pocketmine\entity\projectile\Arrow;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\Listener;
use pocketmine\player\Player;

class LevelListener implements Listener
{
    /** @var self $instance */
    protected static LevelListener $instance;

    public function __construct()
    {
        self::$instance = $this;
    }

    /**
     * @param ProjectileHitBlockEvent $event
     * @return void
     * @priority HIGH
     */
    public function onProjectileHitBlock(ProjectileHitBlockEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity instanceof Arrow && $entity->getWorld()->getFolderName() === GameSettings::getInstance()->world) {
            $entity->flagForDespawn();
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
     * @param EntityTeleportEvent $event
     * @return void
     * @priority HIGH
     */
    public function onEntityTeleport(EntityTeleportEvent $event): void
    {
        $player = $event->getEntity();
        if ($player instanceof Player) {
            if ($event->getTo()->getWorld()->getFolderName() === GameSettings::getInstance()->world) {
                $ev = new GameJoinEvent($player);
                $ev->call();
                new KnockbackKit($player);
            } elseif ($event->getFrom()->getWorld()->getFolderName() === GameSettings::getInstance()->world) {
                $ev = new GameQuitEvent($player);
                $ev->call();
                $player->getInventory()->clearAll();
                $player->getEffects()->clear();
                KnockbackPlayer::getInstance()->killstreak[strtolower($player->getName())] = "None";
                if (GameSettings::getInstance()->scoretag) {
                    $player->setScoreTag("");
                }
            }
        }
    }
}
