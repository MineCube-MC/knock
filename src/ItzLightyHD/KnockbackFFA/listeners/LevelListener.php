<?php

namespace ItzLightyHD\KnockbackFFA\listeners;

use ItzLightyHD\KnockbackFFA\Loader;
use ItzLightyHD\KnockbackFFA\utils\GameSettings;
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
                $this->lastworld[strtolower($player->getName())] = $event->getOrigin()->getName();
                $this->killstreak[strtolower($player->getName())] = 0;
                $player->setHealth(20);
                $player->setFood(20);

                $player->getInventory()->clearAll();
                $player->getArmorInventory()->clearAll();

                $stick = Item::get(280, 0, 1);
                if(GameSettings::getInstance()->enchant_level == 0) {
                    $player->getInventory()->setItem(0, $stick);    
                } else {
                    $stick->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(12), GameSettings::getInstance()->enchant_level));
                    $player->getInventory()->setItem(0, $stick);
                }
                
                $player->removeAllEffects();
                if(GameSettings::getInstance()->speed_level !== 0) {
                    $player->addEffect(new EffectInstance(Effect::getEffect(1), 99999, GameSettings::getInstance()->speed_level, false));
                }
                if(GameSettings::getInstance()->jump_boost_level !== 0) {
                    $player->addEffect(new EffectInstance(Effect::getEffect(8), 99999, GameSettings::getInstance()->jump_boost_level, false));
                }

                if(GameSettings::getInstance()->scoretag == true) {
                    $player->setScoreTag(str_replace(["{kills}"], [$this->killstreak[strtolower($player->getName())]], GameSettings::getInstance()->getConfig()->get("scoretag-format")));
                }
            } else {
                if ($event->getOrigin()->getName() == $this->plugin->getGameData()->get("arena")) {
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