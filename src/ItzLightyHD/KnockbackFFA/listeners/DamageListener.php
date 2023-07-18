<?php

namespace ItzLightyHD\KnockbackFFA\listeners;

use ItzLightyHD\KnockbackFFA\API;
use ItzLightyHD\KnockbackFFA\event\PlayerDeadEvent;
use ItzLightyHD\KnockbackFFA\event\PlayerKilledEvent;
use ItzLightyHD\KnockbackFFA\event\PlayerKillEvent;
use ItzLightyHD\KnockbackFFA\event\PlayerKillstreakEvent;
use ItzLightyHD\KnockbackFFA\Utils;
use ItzLightyHD\KnockbackFFA\utils\GameSettings;
use ItzLightyHD\KnockbackFFA\utils\KnockbackKit;
use ItzLightyHD\KnockbackFFA\utils\KnockbackPlayer;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\World;

class DamageListener implements Listener
{
    /** @var self $instance */
    protected static DamageListener $instance;

    public function __construct()
    {
        self::$instance = $this;
    }

    /**
     * @param EntityDamageEvent $event
     * @return void
     * @priority HIGH
     */
    public function onEntityDamage(EntityDamageEvent $event): void
    {
        $player = $event->getEntity();
        $gameWorld = GameSettings::getInstance()->world;
        if (($player instanceof Player) && $event->getEntity()->getWorld()->getFolderName() === $gameWorld) {
            $world = Server::getInstance()->getWorldManager()->getWorldByName($gameWorld);
            if ($world instanceof World) {
                if ($event->getCause() === EntityDamageEvent::CAUSE_VOID) {
                    $event->cancel();
                    $event->getEntity()->teleport($world->getSpawnLocation());
                    if (KnockbackPlayer::getInstance()->lastDmg[strtolower($player->getName())] === "none") {
                        $deadevent = new PlayerDeadEvent($player);
                        $deadevent->call();
                        new KnockbackKit($player);
                        Utils::playSound("random.glass", $player);
                        KnockbackPlayer::getInstance()->killstreak[strtolower($player->getName())] = 0;
                        if (GameSettings::getInstance()->scoretag) {
                            $event->getEntity()->setScoreTag(str_replace(["{kills}"], [KnockbackPlayer::getInstance()->killstreak[strtolower($player->getName())]], GameSettings::getInstance()->getConfig()->get("scoretag-format")));
                        }
                        EssentialsListener::$cooldown[$player->getName()] = 0;
                        $player->sendPopup(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§cYou died");
                    } else {
                        KnockbackPlayer::getInstance()->killstreak[strtolower($player->getName())] = 0;
                        $killedBy = Server::getInstance()->getPlayerExact(KnockbackPlayer::getInstance()->lastDmg[strtolower($player->getName())]);
                        if ($killedBy instanceof Player) {
                            KnockbackPlayer::getInstance()->killstreak[strtolower($killedBy->getName())]++;
                            if ((int)KnockbackPlayer::getInstance()->killstreak[strtolower($killedBy->getName())] % 5 === 0) {
                                $players = $event->getEntity()->getWorld()->getPlayers();
                                $killevent = new PlayerKillEvent($killedBy, $player);
                                $killevent->call();
                                $killstreakevent = new PlayerKillstreakEvent($killedBy);
                                $killstreakevent->call();
                                foreach ($players as $p) {
                                    Utils::playSound("random.levelup", $p);
                                    $p->sendPopup(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§f" . Server::getInstance()->getPlayerExact(KnockbackPlayer::getInstance()->lastDmg[strtolower($player->getName())])?->getDisplayName() . "§r§6 is at §e" . KnockbackPlayer::getInstance()->killstreak[KnockbackPlayer::getInstance()->lastDmg[strtolower($player->getName())]] . "§6 kills");
                                }
                                if (GameSettings::getInstance()->scoretag) {
                                    $killedBy->setScoreTag(str_replace(["{kills}"], [KnockbackPlayer::getInstance()->killstreak[strtolower($killedBy->getName())]], GameSettings::getInstance()->getConfig()->get("scoretag-format")));
                                }
                            } else {
                                if (GameSettings::getInstance()->scoretag) {
                                    $killedBy->setScoreTag(str_replace(["{kills}"], [KnockbackPlayer::getInstance()->killstreak[strtolower($killedBy->getName())]], GameSettings::getInstance()->getConfig()->get("scoretag-format")));
                                }
                                $killevent = new PlayerKillEvent($killedBy, $player);
                                $killevent->call();
                                $killedBy->sendPopup(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§aYou killed §f" . $player->getDisplayName());
                            }
                            Utils::playSound("note.pling", $killedBy);
                            $killedevent = new PlayerKilledEvent($player, $killedBy);
                            $killedevent->call();
                        }
                        new KnockbackKit($player);
                        Utils::playSound("random.glass", $player);
                        if (GameSettings::getInstance()->scoretag) {
                            $event->getEntity()->setScoreTag(str_replace(["{kills}"], [KnockbackPlayer::getInstance()->killstreak[strtolower($player->getName())]], GameSettings::getInstance()->getConfig()->get("scoretag-format")));
                        }
                        EssentialsListener::$cooldown[$player->getName()] = 0;
                        $player->sendPopup(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§cYou were killed by §f" . $killedBy?->getDisplayName());
                    }
                    KnockbackPlayer::getInstance()->lastDmg[strtolower($player->getName())] = "none";
                } elseif ($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
                    $event->cancel();
                }
            }
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
     * @param PlayerKillEvent $event
     * @return void
     * @priority HIGH
     */
    public function KnockBackFFAKillEvent(PlayerKillEvent $event): void
    {
        $player = $event->getPlayer();
        if (API::isSnowballsEnabled()) {
            $snowballs = VanillaItems::SNOWBALL();
            $player->getInventory()->addItem($snowballs);
        }
    }

    /**
     * @param EntityDamageByEntityEvent $event
     * @return void
     * @priority HIGH
     */
    public function entityAttacked(EntityDamageByEntityEvent $event): void
    {
        $player = $event->getEntity();
        $damager = $event->getDamager();
        if (($player instanceof Player) && $player->getWorld()->getFolderName() === GameSettings::getInstance()->world) {
            $player->setHealth(20);
            $player->getHungerManager()->setSaturation(20);
            if ($damager instanceof Player) {
                if (!Utils::canTakeDamage($player)) {
                    $damager->sendMessage(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§cYou can't hit the players here!");
                    $event->cancel();
                    return;
                }
                if ($damager->getName() === $player->getName()) {
                    $event->cancel();
                    return;
                }
                KnockbackPlayer::getInstance()->lastDmg[strtolower($player->getName())] = strtolower($damager->getName());
                if (GameSettings::getInstance()->massive_knockback && $damager->getInventory()->getItemInHand()->getTypeId() === ItemTypeIds::STICK) {
                    $x = $damager->getDirectionVector()->x;
                    $z = $damager->getDirectionVector()->z;
                    $player->knockBack($x, $z, 0.6);
                }
            }
        }
    }

    /**
     * @param EntityDamageByChildEntityEvent $event
     * @return void
     * @priority HIGH
     */
    public function projectileAttack(EntityDamageByChildEntityEvent $event): void
    {
        $player = $event->getEntity();
        $damager = $event->getDamager();
        if (($player instanceof Player && $damager instanceof Player) && ($player->getWorld()->getFolderName() === GameSettings::getInstance()->world)) {
            if ($damager->getName() === $player->getName()) {
                $event->cancel();
                $damager->sendMessage(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§cYou can't hit yourself.");
                return;
            }
            $event->setBaseDamage(0);
            $player->setHealth(20);
            $player->getHungerManager()->setSaturation(20);
            if (!Utils::canTakeDamage($player)) {
                $event->cancel();
                $damager->sendMessage(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§cYou can't hit the players here!");
                return;
            }
            KnockbackPlayer::getInstance()->lastDmg[strtolower($player->getName())] = strtolower($damager->getName());
            Utils::playSound("random.orb", $damager);
        }
    }
}
