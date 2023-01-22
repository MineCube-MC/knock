<?php

namespace ItzLightyHD\KnockbackFFA\utils;

use ItzLightyHD\KnockbackFFA\event\PlayerKitEvent;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

final class KnockbackKit
{
    public function __construct(Player $player)
    {
        KnockbackPlayer::getInstance()->killstreak[strtolower($player->getName())] = 0;
        $ev = new PlayerKitEvent($player);
        $ev->call();
        if ($ev->isCancelled()) {
            return;
        }
        $player->setHealth(20);
        $player->getHungerManager()->setFood(20);
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $stick = VanillaItems::STICK();
        if (GameSettings::getInstance()->enchant_level !== 0) {
            $stick->addEnchantment(new EnchantmentInstance(VanillaEnchantments::KNOCKBACK(), GameSettings::getInstance()->enchant_level));
        }
        $player->getInventory()->setItem(0, $stick);
        if (GameSettings::getInstance()->bow) {
            $bow = VanillaItems::BOW();
            $arrow = VanillaItems::ARROW();
            $bow->addEnchantment(new EnchantmentInstance(VanillaEnchantments::INFINITY(), 1));
            $bow->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10));
            if (GameSettings::getInstance()->knockback_level !== 0) {
                $bow->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PUNCH(), GameSettings::getInstance()->knockback_level));
            }
            $player->getInventory()->addItem($bow);
            $player->getInventory()->setItem(9, $arrow);
        }
        if (GameSettings::getInstance()->snowballs) {
            $snowballs = VanillaItems::SNOWBALL();
            $player->getInventory()->addItem($snowballs);
        }
        if (GameSettings::getInstance()->leap) {
            $leap = VanillaItems::FEATHER();
            $leap->setCustomName("§r§eLeap§r");
            $leap->setLore(["§r§7Saves you from the danger..."]);
            $player->getInventory()->addItem($leap);
        }
        $player->getEffects()->clear();
        if (GameSettings::getInstance()->speed_level !== 0) {
            $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 99999, GameSettings::getInstance()->speed_level, false));
        }
        if (GameSettings::getInstance()->jump_boost_level !== 0) {
            $player->getEffects()->add(new EffectInstance(VanillaEffects::JUMP_BOOST(), 99999, GameSettings::getInstance()->jump_boost_level, false));
        }
        if (GameSettings::getInstance()->scoretag) {
            $player->setScoreTag(str_replace(["{kills}"], [KnockbackPlayer::getInstance()->killstreak[strtolower($player->getName())]], GameSettings::getInstance()->getConfig()->get("scoretag-format")));
        }
    }
}
