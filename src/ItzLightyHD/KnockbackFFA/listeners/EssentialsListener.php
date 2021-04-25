<?php

namespace ItzLightyHD\KnockbackFFA\listeners;

use ItzLightyHD\KnockbackFFA\Loader;
use ItzLightyHD\KnockbackFFA\utils\GameSettings;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\Listener;

class EssentialsListener implements Listener {

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

    public function onBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        if($player->getLevel()->getFolderName() == GameSettings::getInstance()->getConfig()->get("arena")) {
            $event->setCancelled();
        }
    }

    public function onHunger(PlayerExhaustEvent $event) {
        if($event->getPlayer()->getLevel()->getFolderName() === GameSettings::getInstance()->getConfig()->get("arena")) {
            $event->setCancelled(true);
        }
    }

    public function onDrop(PlayerDropItemEvent $event): void {
        $player = $event->getPlayer();
        if($player->getLevel()->getFolderName() === GameSettings::getInstance()->getConfig()->get("arena")) {
            $event->setCancelled();
        }
    }

}