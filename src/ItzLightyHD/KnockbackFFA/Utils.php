<?php

namespace ItzLightyHD\KnockbackFFA;

use ItzLightyHD\KnockbackFFA\utils\GameSettings;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\Player;
use pocketmine\Server;

final class Utils
{
    /**
     * @param Player $player
     * @return bool
     */
    public static function canTakeDamage(Player $player): bool
    {
        $x = $player->getLocation()->getX();
        $y = $player->getLocation()->getY();
        $z = $player->getLocation()->getZ();
        $xx = $player->getWorld()->getSafeSpawn()->getX();
        $yy = $player->getWorld()->getSafeSpawn()->getY();
        $zz = $player->getWorld()->getSafeSpawn()->getZ();
        $sr = GameSettings::getInstance()->getConfig()->get("protection-radius");
        return !(abs($xx - $x) < $sr && abs($yy - $y) < $sr && abs($zz - $z) < $sr);
    }

    /**
     * @param string $soundName
     * @param Player|null $player
     * @return void
     */
    public static function playSound(string $soundName, ?Player $player): void
    {
        if ($player === null) {
            return;
        }
        $pk = new PlaySoundPacket();
        $pk->soundName = $soundName;
        $pk->x = $player->getLocation()->getX();
        $pk->y = $player->getLocation()->getY();
        $pk->z = $player->getLocation()->getZ();
        $pk->volume = 500;
        $pk->pitch = 1;
        NetworkBroadcastUtils::broadcastPackets($player->getWorld()->getPlayers(), [$pk]);
    }
}
