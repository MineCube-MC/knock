<?php

namespace ItzLightyHD\KnockbackFFA\listeners;

use ItzLightyHD\KnockbackFFA\Loader;
use ItzLightyHD\KnockbackFFA\utils\KnockbackPlayer;
use ItzLightyHD\KnockbackFFA\utils\GameSettings;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\level\sound\PopSound;
use pocketmine\level\sound\AnvilFallSound;
use pocketmine\level\sound\FizzSound;
use pocketmine\event\Listener;

class DamageListener implements Listener {

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

    public function onEntityDamage(EntityDamageEvent $event)
    {
        $player = $event->getEntity();
        if($player instanceof Player) {
            if($event->getEntity()->getLevel()->getFolderName() === GameSettings::getInstance()->getConfig()->get("arena")) {
                if($event->getCause() === EntityDamageEvent::CAUSE_VOID) {
                    $event->setCancelled();
                    $event->getEntity()->teleport(Server::getInstance()->getLevelByName(GameSettings::getInstance()->getConfig()->get("arena"))->getSpawnLocation());
                    if(KnockbackPlayer::getInstance()->lastDmg[strtolower($player->getName())] === "none") {
                        $event->getEntity()->getLevel()->addSound(new AnvilFallSound($event->getEntity()));
                        KnockbackPlayer::getInstance()->killstreak[strtolower($player->getName())] = 0;
                        if(GameSettings::getInstance()->scoretag == true) {
                            $event->getEntity()->setScoreTag(str_replace(["{kills}"], [KnockbackPlayer::getInstance()->killstreak[strtolower($player->getName())]], GameSettings::getInstance()->getConfig()->get("scoretag-format")));
                        }
                        $player->sendPopup(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§cYou died");
                    } else {
                        KnockbackPlayer::getInstance()->killstreak[strtolower($player->getName())] = 0;
                        $killedBy = Server::getInstance()->getPlayer(KnockbackPlayer::getInstance()->lastDmg[strtolower($player->getName())]);
                        if($killedBy->isOnline()) {
                            KnockbackPlayer::getInstance()->killstreak[strtolower($killedBy->getName())] = KnockbackPlayer::getInstance()->killstreak[strtolower($killedBy->getName())] + 1;
                            $ks = [5, 10, 15, 20, 25, 30, 40, 50];
                            if (in_array(KnockbackPlayer::getInstance()->killstreak[strtolower($killedBy->getName())], $ks)) {
                                $players = $event->getEntity()->getLevel()->getPlayers();
            
                                foreach ($players as $p) {
                                    $p->getLevel()->addSound(new PopSound($p));
                                    $p->sendPopup(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§f" . Server::getInstance()->getPlayer(KnockbackPlayer::getInstance()->lastDmg[strtolower($player->getName())])->getDisplayName() . "§r§6 is at §e" . KnockbackPlayer::getInstance()->killstreak[KnockbackPlayer::getInstance()->lastDmg[strtolower($player->getName())]] . "§6 kills");
                                }
                                if(GameSettings::getInstance()->scoretag == true) {
                                    $killedBy->setScoreTag(str_replace(["{kills}"], [KnockbackPlayer::getInstance()->killstreak[strtolower($killedBy->getName())]], GameSettings::getInstance()->getConfig()->get("scoretag-format")));
                                }
                            } else {
                                if(GameSettings::getInstance()->scoretag == true) {
                                    $killedBy->setScoreTag(str_replace(["{kills}"], [KnockbackPlayer::getInstance()->killstreak[strtolower($killedBy->getName())]], GameSettings::getInstance()->getConfig()->get("scoretag-format")));
                                }
                                $killedBy->sendPopup(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§aYou killed §f" . $player->getDisplayName());
                            }
                            $killedBy->getLevel()->addSound(new FizzSound($killedBy));
                        }
                        $event->getEntity()->getLevel()->addSound(new AnvilFallSound($event->getEntity()));
                        if(GameSettings::getInstance()->scoretag == true) {
                            $event->getEntity()->setScoreTag(str_replace(["{kills}"], [KnockbackPlayer::getInstance()->killstreak[strtolower($player->getName())]], GameSettings::getInstance()->getConfig()->get("scoretag-format")));
                        }
                        $player->sendPopup(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§cYou were killed by §f" . $killedBy->getDisplayName());
                    }
                    KnockbackPlayer::getInstance()->lastDmg[strtolower($player->getName())] = "none";                    
                }
                if($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
                    $event->setCancelled();
                }
            }
        }
    }

    public function entityAttacked(EntityDamageByEntityEvent $event) {
        $player = $event->getEntity();
        $damager = $event->getDamager();

        if($player instanceof Player) {
            $player->setHealth(20);
            $player->setSaturation(20);

            if($damager instanceof Player) {
                $x = $player->getX();
                $y = $player->getY();
                $z = $player->getZ();
                $xx = $player->getLevel()->getSafeSpawn()->getX();
                $yy = $player->getLevel()->getSafeSpawn()->getY();
                $zz = $player->getLevel()->getSafeSpawn()->getZ();
                $sr = GameSettings::getInstance()->getConfig()->get("protection-radius");

                if (abs($xx - $x) < $sr && abs($yy - $y) < $sr && abs($zz - $z) < $sr) {
                    $event->setCancelled();
                    $damager->sendMessage("§cYou can't hit the players here!");
                    return;
                }

                KnockbackPlayer::getInstance()->lastDmg[strtolower($player->getName())] = strtolower($damager->getName());

                $item = $damager->getInventory()->getItemInHand()->getId();
                if(GameSettings::getInstance()->massive_knockback == true) {
                    if ($item == 280) {
                        $x = $damager->getDirectionVector()->x;
                        $z = $damager->getDirectionVector()->z;
                        $player->knockBack($event->getEntity(), 0, $x, $z, 0.6);
                        return;
                    }
                }
            }
        }
    }

}