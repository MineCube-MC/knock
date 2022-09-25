<?php

namespace ItzLightyHD\KnockbackFFA;

use ItzLightyHD\KnockbackFFA\utils\GameSettings;
use ItzLightyHD\KnockbackFFA\utils\KnockbackPlayer;
use pocketmine\player\Player;
use pocketmine\Server;

class API
{

    public static function getKills(?Player $player)
    {
        $playername = strtolower($player?->getName());
        return KnockbackPlayer::getInstance()->killstreak[$playername] ?? "none";
    }

    public static function getLastDmg(?Player $player): Player|string|null
    {
        $playername = strtolower($player?->getName());
        if (isset(KnockbackPlayer::getInstance()->lastDmg[$playername]) && !KnockbackPlayer::getInstance()->lastDmg[$playername] == "none") {
            return Server::getInstance()->getPlayerExact(KnockbackPlayer::getInstance()->lastDmg[$playername]);
        }
        return "none";
    }

    public static function isMassiveKnockbackEnabled(): bool
    {
        if (GameSettings::getInstance()->massive_knockback) {
            return true;
        }
        return false;
    }

    public static function isBowEnabled(): bool
    {
        if (GameSettings::getInstance()->bow) {
            return true;
        }
        return false;
    }

    public static function isSnowballsEnabled(): bool
    {
        if (GameSettings::getInstance()->snowballs) {
            return true;
        }
        return false;
    }

    public static function isLeapEnabled(): bool
    {
        if (GameSettings::getInstance()->leap) {
            return true;
        }
        return false;
    }

    public static function isDoubleJumpEnabled(): bool
    {
        if (GameSettings::getInstance()->doublejump) {
            return true;
        }
        return false;
    }

}
