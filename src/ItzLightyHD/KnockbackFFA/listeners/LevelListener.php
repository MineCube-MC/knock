<?php

namespace ItzLightyHD\KnockbackFFA\listeners;

use ItzLightyHD\KnockbackFFA\AdditionalItems;
use ItzLightyHD\KnockbackFFA\Loader;
use ItzLightyHD\KnockbackFFA\utils\GameSettings;
use ItzLightyHD\KnockbackFFA\utils\KnockbackKit;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Effect;
use pocketmine\item\Item;
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
            if($event->getTarget()->getFolderName() == GameSettings::getInstance()->getConfig()->get("arena")) {
                new KnockbackKit($player);
            } else {
                if ($event->getOrigin()->getName() == GameSettings::getInstance()->getConfig()->get("arena")) {
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