<?php

namespace ItzLightyHD\KnockbackFFA\listeners;

use ItzLightyHD\KnockbackFFA\event\PlayerDoubleJumpEvent;
use ItzLightyHD\KnockbackFFA\Utils;
use ItzLightyHD\KnockbackFFA\utils\GameSettings;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\item\ItemTypeIds;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class EssentialsListener implements Listener
{
    /** @var array<int|string> */
    public static array $cooldown = [];
    /** @var self $instance */
    protected static EssentialsListener $instance;

    public function __construct()
    {
        self::$instance = $this;
    }

    /**
     * @param BlockBreakEvent $event
     * @return void
     * @priority HIGH
     */
    public function onBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();
        if ($player->getWorld()->getFolderName() === GameSettings::getInstance()->world) {
            $event->cancel();
        }
    }

    /**
     * @return self
     */
    public static function getInstance(): self
    {
        return self::$instance;
    }

    /**
     * @param PlayerExhaustEvent $event
     * @return void
     * @priority HIGH
     */
    public function onHunger(PlayerExhaustEvent $event): void
    {
        if ($event->getPlayer()->getWorld()->getFolderName() === GameSettings::getInstance()->world) {
            $event->setAmount(0);
        }
    }

    /**
     * @param PlayerDropItemEvent $event
     * @return void
     * @priority HIGH
     */
    public function onDrop(PlayerDropItemEvent $event): void
    {
        $player = $event->getPlayer();
        if ($player->getWorld()->getFolderName() === GameSettings::getInstance()->world) {
            $event->cancel();
        }
    }

    /**
     * @param EntityShootBowEvent $event
     * @return void
     * @priority HIGH
     */
    public function onEntityShootBow(EntityShootBowEvent $event): void
    {
        $entity = $event->getEntity();
        if (($entity instanceof Player) && !Utils::canTakeDamage($entity) && $entity->getWorld()->getFolderName() === GameSettings::getInstance()->world) {
            $event->cancel();
            $entity->sendMessage(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§cYou can't use that item here!");
        }
    }

    /**
     * @param PlayerItemUseEvent $event
     * @return void
     * @priority HIGH
     */
    public function onItemUse(PlayerItemUseEvent $event): void
    {
        $player = $event->getPlayer();
        if (!Utils::canTakeDamage($player) && ($event->getItem()->getTypeId() === ItemTypeIds::SNOWBALL) && $player->getWorld()->getFolderName() === GameSettings::getInstance()->world) {
            $event->cancel();
            $player->sendMessage(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§cYou can't use that item here!");
        }
        if ($event->getItem()->getCustomName() === "§r§eLeap§r" && ($player->getWorld()->getFolderName() === GameSettings::getInstance()->world)) {
            $ev = new PlayerDoubleJumpEvent($player);
            $ev->call();
            if ($ev->isCancelled()) {
                return;
            }
            if (!isset(self::$cooldown[$player->getName()])) {
                self::$cooldown[$player->getName()] = 0;
            }
            if (self::$cooldown[$player->getName()] <= time()) {
                $directionvector = $player->getDirectionVector()->multiply(4 / 2);
                $dx = $directionvector->getX();
                $dz = $directionvector->getZ();
                $player->setMotion(new Vector3($dx, 1, $dz));
                Utils::playSound("mob.enderdragon.flap", $player);
                self::$cooldown[$player->getName()] = time() + 10;
            } else {
                $player->sendMessage(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§cWait §e" . (10 - ((time() + 10) - self::$cooldown[$player->getName()])) . "§c seconds before using your leap/double jump again.");
            }
        }
    }
}
