<?php

namespace ItzLightyHD\KnockbackFFA;

use ItzLightyHD\KnockbackFFA\utils\GameSettings;
use ItzLightyHD\KnockbackFFA\utils\KnockbackPlayer;
use pocketmine\player\Player;
use pocketmine\Server;

final class API
{
    /**
     * @param Player $player
     * @return string
     */
    public static function getKills(Player $player): string
    {
        $kills = (string)KnockbackPlayer::getInstance()->killstreak[strtolower($player->getName())];
        return $kills ?? "none";
    }

    /**
     * @param Player|null $player
     * @return Player|string|null
     */
    public static function getLastDmg(?Player $player): Player|string|null
    {
        $playername = strtolower($player?->getName());
        if (isset(KnockbackPlayer::getInstance()->lastDmg[$playername]) && !KnockbackPlayer::getInstance()->lastDmg[$playername] == "none") {
            return Server::getInstance()->getPlayerExact(KnockbackPlayer::getInstance()->lastDmg[$playername]);
        }
        return "none";
    }

    /**
     * @return bool
     */
    public static function isMassiveKnockbackEnabled(): bool
    {
        if (GameSettings::getInstance()->massive_knockback) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public static function isBowEnabled(): bool
    {
        if (GameSettings::getInstance()->bow) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public static function isSnowballsEnabled(): bool
    {
        if (GameSettings::getInstance()->snowballs) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public static function isLeapEnabled(): bool
    {
        if (GameSettings::getInstance()->leap) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public static function isDoubleJumpEnabled(): bool
    {
        if (GameSettings::getInstance()->doublejump) {
            return true;
        }
        return false;
    }
}
