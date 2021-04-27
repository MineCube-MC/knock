<?php

namespace ItzLightyHD\KnockbackFFA\listeners;

use ItzLightyHD\KnockbackFFA\event\GameJoinEvent;
use ItzLightyHD\KnockbackFFA\event\GameQuitEvent;
use ItzLightyHD\KnockbackFFA\Loader;
use ItzLightyHD\KnockbackFFA\utils\GameSettings;
use ItzLightyHD\KnockbackFFA\utils\KnockbackKit;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\Player;
use pocketmine\event\Listener;

class LevelListener implements Listener {

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

    public function onEntityLevelChange(EntityLevelChangeEvent $event): void {
        $player = $event->getEntity();
        if($player instanceof Player) {
            if($event->getTarget()->getFolderName() == GameSettings::getInstance()->$world) {
                $ev = new GameJoinEvent($player);
                $ev->call();
                new KnockbackKit($player);
            } else {
                if ($event->getOrigin()->getName() == GameSettings::getInstance()->$world) {
                    $ev = new GameQuitEvent($player);
                    $ev->call();
                    $player->getInventory()->clearAll();
                    $player->removeAllEffects();
                    $this->killstreak[strtolower($player->getName())] = "None";
                    if(GameSettings::getInstance()->scoretag == true) {
                        $player->setScoreTag("");
                    }
                }
            }
        }
    }

}