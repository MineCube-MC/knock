<?php

namespace ItzLightyHD\KnockbackFFA\listeners;

use ItzLightyHD\KnockbackFFA\API;
use ItzLightyHD\KnockbackFFA\event\PlayerDeadEvent;
use ItzLightyHD\KnockbackFFA\event\PlayerKilledEvent;
use ItzLightyHD\KnockbackFFA\event\PlayerKillEvent;
use ItzLightyHD\KnockbackFFA\event\PlayerKillstreakEvent;
use ItzLightyHD\KnockbackFFA\Loader;
use ItzLightyHD\KnockbackFFA\utils\GameSettings;
use ItzLightyHD\KnockbackFFA\utils\KnockbackKit;
use ItzLightyHD\KnockbackFFA\utils\KnockbackPlayer;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\Server;

class DamageListener implements Listener
{

    /** @var self $instance */
    protected static DamageListener $instance;
    /** @var Loader $plugin */
    private Loader $plugin;

    public function __construct(Loader $plugin)
    {
        $this->plugin = $plugin;
        self::$instance = $this;
    }

    public function onEntityDamage(EntityDamageEvent $event): void
    {
        $player = $event->getEntity();
        if (($player instanceof Player) && $event->getEntity()->getWorld()->getFolderName() === GameSettings::getInstance()->world) {
            if ($event->getCause() === EntityDamageEvent::CAUSE_VOID) {
                $event->cancel();
                $event->getEntity()->teleport(Server::getInstance()->getWorldManager()->getWorldByName(GameSettings::getInstance()->world)?->getSpawnLocation());
                if (KnockbackPlayer::getInstance()->lastDmg[strtolower($player->getName())] === "none") {
                    $deadevent = new PlayerDeadEvent($event->getEntity());
                    $deadevent->call();
                    new KnockbackKit($event->getEntity());
                    KnockbackPlayer::getInstance()->playSound("random.glass", $event->getEntity());
                    KnockbackPlayer::getInstance()->killstreak[strtolower($player->getName())] = 0;
                    if (GameSettings::getInstance()->scoretag === true) {
                        $event->getEntity()->setScoreTag(str_replace(["{kills}"], [KnockbackPlayer::getInstance()->killstreak[strtolower($player->getName())]], GameSettings::getInstance()->getConfig()->get("scoretag-format")));
                    }
                    EssentialsListener::$cooldown[$player->getName()] = 0;
                    $player->sendPopup(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§cYou died");
                } else {
                    KnockbackPlayer::getInstance()->killstreak[strtolower($player->getName())] = 0;
                    $killedBy = Server::getInstance()->getPlayerExact(KnockbackPlayer::getInstance()->lastDmg[strtolower($player->getName())]);
                    if ($killedBy?->isOnline()) {
                        KnockbackPlayer::getInstance()->killstreak[strtolower($killedBy?->getName())]++;
                        if (KnockbackPlayer::getInstance()->killstreak[strtolower($killedBy?->getName())] % 5 === 0) {
                            $players = $event->getEntity()->getWorld()->getPlayers();
                            if ($killedBy instanceof Player) {
                                $killevent = new PlayerKillEvent($killedBy, $event->getEntity());
                                $killevent->call();
                                $killstreakevent = new PlayerKillstreakEvent($killedBy);
                                $killstreakevent->call();
                            }
                            foreach ($players as $p) {
                                KnockbackPlayer::getInstance()->playSound("random.levelup", $p);
                                $p->sendPopup(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§f" . Server::getInstance()->getPlayerExact(KnockbackPlayer::getInstance()->lastDmg[strtolower($player->getName())])?->getDisplayName() . "§r§6 is at §e" . KnockbackPlayer::getInstance()->killstreak[KnockbackPlayer::getInstance()->lastDmg[strtolower($player->getName())]] . "§6 kills");
                            }
                            if (GameSettings::getInstance()->scoretag === true) {
                                $killedBy?->setScoreTag(str_replace(["{kills}"], [KnockbackPlayer::getInstance()->killstreak[strtolower($killedBy?->getName())]], GameSettings::getInstance()->getConfig()->get("scoretag-format")));
                            }
                        } else {
                            if (GameSettings::getInstance()->scoretag === true) {
                                $killedBy?->setScoreTag(str_replace(["{kills}"], [KnockbackPlayer::getInstance()->killstreak[strtolower($killedBy?->getName())]], GameSettings::getInstance()->getConfig()->get("scoretag-format")));
                            }
                            $killevent = new PlayerKillEvent($killedBy, $event->getEntity());
                            $killevent->call();
                            $killedBy?->sendPopup(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§aYou killed §f" . $player->getDisplayName());
                        }
                        KnockbackPlayer::getInstance()->playSound("note.pling", $killedBy);
                    }
                    if ($killedBy instanceof Player) {
                        $killedevent = new PlayerKilledEvent($event->getEntity(), $killedBy);
                        $killedevent->call();
                    }
                    new KnockbackKit($event->getEntity());
                    KnockbackPlayer::getInstance()->playSound("random.glass", $event->getEntity());
                    if (GameSettings::getInstance()->scoretag === true) {
                        $event->getEntity()->setScoreTag(str_replace(["{kills}"], [KnockbackPlayer::getInstance()->killstreak[strtolower($player->getName())]], GameSettings::getInstance()->getConfig()->get("scoretag-format")));
                    }
                    EssentialsListener::$cooldown[$player->getName()] = 0;
                    $player->sendPopup(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§cYou were killed by §f" . $killedBy?->getDisplayName());
                }
                KnockbackPlayer::getInstance()->lastDmg[strtolower($player->getName())] = "none";
            }
            if ($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
                $event->cancel();
            }
        }
    }

    public static function getInstance(): self
    {
        return self::$instance;
    }

    public function kbffaKill(PlayerKillEvent $event): void
    {
        $player = $event->getPlayer();

        if (API::isSnowballsEnabled() === true) {
            $snowballs = VanillaItems::SNOWBALL();
            $player->getInventory()->addItem($snowballs);
        }
    }

    public function entityAttacked(EntityDamageByEntityEvent $event): void
    {
        $player = $event->getEntity();
        $damager = $event->getDamager();

        if (($player instanceof Player) && $player->getWorld()->getFolderName() === GameSettings::getInstance()->world) {
            $player->setHealth(20);
            $player->getHungerManager()->setSaturation(20);

            if ($damager instanceof Player) {
                $x = $player->getLocation()->getX();
                $y = $player->getLocation()->getY();
                $z = $player->getLocation()->getZ();
                $xx = $player->getWorld()->getSafeSpawn()->getX();
                $yy = $player->getWorld()->getSafeSpawn()->getY();
                $zz = $player->getWorld()->getSafeSpawn()->getZ();
                $sr = GameSettings::getInstance()->getConfig()->get("protection-radius");

                if ($damager->getName() === $player->getName()) {
                    $event->cancel();
                    return;
                }

                if (abs($xx - $x) < $sr && abs($yy - $y) < $sr && abs($zz - $z) < $sr) {
                    $event->cancel();
                    $damager->sendMessage(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§cYou can't hit the players here!");
                    return;
                }
                KnockbackPlayer::getInstance()->lastDmg[strtolower($player->getName())] = strtolower($damager->getName());
                $item = $damager->getInventory()->getItemInHand()->getId();
                if ((GameSettings::getInstance()->massive_knockback === true) && $item === ItemIds::STICK) {
                    $x = $damager->getDirectionVector()->x;
                    $z = $damager->getDirectionVector()->z;
                    $player->knockBack($x, $z, 0.6);
                }
            }
        }
    }

    public function projectileAttack(EntityDamageByChildEntityEvent $event): void
    {
        $player = $event->getEntity();
        $damager = $event->getDamager();

        if (($player instanceof Player) && ($player->getWorld()->getFolderName() === GameSettings::getInstance()->world) && $damager instanceof Player) {
            if ($damager->getName() === $player->getName()) {
                $event->cancel();
                $damager->sendMessage(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§cYou can't hit yourself.");
                return;
            }
            $event->setBaseDamage(0);
            $player->setHealth(20);
            $player->getHungerManager()->setSaturation(20);
            $x = $player->getLocation()->getX();
            $y = $player->getLocation()->getY();
            $z = $player->getLocation()->getZ();
            $xx = $player->getWorld()->getSafeSpawn()->getX();
            $yy = $player->getWorld()->getSafeSpawn()->getY();
            $zz = $player->getWorld()->getSafeSpawn()->getZ();
            $sr = GameSettings::getInstance()->getConfig()->get("protection-radius");
            if (abs($xx - $x) < $sr && abs($yy - $y) < $sr && abs($zz - $z) < $sr) {
                $event->cancel();
                $damager->sendMessage(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§cYou can't hit the players here!");
                return;
            }
            KnockbackPlayer::getInstance()->lastDmg[strtolower($player->getName())] = strtolower($damager->getName());
            KnockbackPlayer::getInstance()->playSound("random.orb", $damager);
        }
    }
}
