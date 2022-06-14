<?php

namespace ItzLightyHD\KnockbackFFA\listeners;

use ItzLightyHD\KnockbackFFA\event\GameJoinEvent;
use ItzLightyHD\KnockbackFFA\event\GameQuitEvent;
use ItzLightyHD\KnockbackFFA\Loader;
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
    /** @var Loader $plugin */
    private Loader $plugin;

    public function __construct(Loader $plugin)
    {
        $this->plugin = $plugin;
        self::$instance = $this;
    }

    public function onProjectileHitBlock(ProjectileHitBlockEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity instanceof Arrow && $entity->getWorld()->getFolderName() === GameSettings::getInstance()->world) {
            $entity->flagForDespawn();
        }
    }

    public static function getInstance(): self
    {
        return self::$instance;
    }

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
                unset(KnockbackPlayer::getInstance()->jumptask[strtolower($player->getName())], KnockbackPlayer::getInstance()->jumpcount[strtolower($player->getName())]);
                KnockbackPlayer::getInstance()->killstreak[strtolower($player->getName())] = "None";
                if (GameSettings::getInstance()->scoretag === true) {
                    $player->setScoreTag("");
                }
            }
        }
    }
}