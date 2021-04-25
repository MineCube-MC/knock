<?php

namespace ItzLightyHD\KnockbackFFA\utils;

use ItzLightyHD\KnockbackFFA\utils\KnockbackPlayer;
use ItzLightyHD\KnockbackFFA\utils\GameSettings;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Effect;
use pocketmine\item\Item;
use pocketmine\Player;

class KnockbackKit {

    public function __construct(Player $player)
    {
        KnockbackPlayer::getInstance()->killstreak[strtolower($player->getName())] = 0;
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
            $player->setScoreTag(str_replace(["{kills}"], [KnockbackPlayer::getInstance()->killstreak[strtolower($player->getName())]], GameSettings::getInstance()->getConfig()->get("scoretag-format")));
        }
    }

}