<?php

namespace ItzLightyHD\KnockbackFFA;

use ItzLightyHD\KnockbackFFA\utils\KnockbackPlayer;
use ItzLightyHD\KnockbackFFA\utils\GameSettings;
use pocketmine\Player;
use pocketmine\Server;

class API {

    public static function getKills(Player $player)
    {
        $playername = strtolower($player->getName());
        if(isset(KnockbackPlayer::getInstance()->killstreak[$playername])) {
            return KnockbackPlayer::getInstance()->killstreak[$playername];
        } else {
            return "none";
        }
    }

    public static function getLastDmg(Player $player)
    {
        $playername = strtolower($player->getName());
        if(isset(KnockbackPlayer::getInstance()->lastDmg[$playername])) {
            if(!KnockbackPlayer::getInstance()->lastDmg[$playername] == "none") {
                $lastDmg = Server::getInstance()->getPlayer(KnockbackPlayer::getInstance()->lastDmg[$playername]);
                return $lastDmg;
            } else {
                return "none";
            }
        } else {
            return "none";
        }
    }

    public static function isMassiveKnockbackEnabled(): bool
    {
        if(GameSettings::getInstance()->massive_knockback == true) {
            return true;
        } else {
            return false;
        }
    }

    public static function isBowEnabled(): bool
    {
        if(GameSettings::getInstance()->bow == true) {
            return true;
        } else {
            return false;
        }
    }

    public static function isSnowballsEnabled(): bool
    {
        if(GameSettings::getInstance()->snowballs == true) {
            return true;
        } else {
            return false;
        }
    }

    public static function isLeapEnabled(): bool
    {
        if(GameSettings::getInstance()->leap == true) {
            return true;
        } else {
            return false;
        }
    }

}