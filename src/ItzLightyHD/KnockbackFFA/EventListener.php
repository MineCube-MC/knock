<?php
declare(strict_types=1);

namespace ItzLightyHD\KnockbackFFA;

use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\item\Item;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Effect;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\level\sound\PopSound;
use pocketmine\level\sound\AnvilFallSound;
use pocketmine\level\sound\FizzSound;

class EventListener implements Listener {

    /** @var KnockbackFFA $plugin */
    private $plugin;
    /** @var self $instance */
    protected static $instance;

    public function __construct(KnockbackFFA $plugin)
    {
        $this->plugin = $plugin;
        self::$instance = $this;
    }

    public function getPlugin() : Plugin{
        return $this->plugin;
    }

    public static function getInstance(): self
    {
        return self::$instance;
    }

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $name = strtolower($player->getName());
        $this->lastDmg[$name] = "none";
        $this->killstreak[$name] = 0;
    }

    public function getKillstreak($player) {
        $target = Server::getInstance()->getPlayer($player)->getName();
        if(isset($this->killstreak[strtolower($target)])) {
            return $this->killstreak[strtolower($target)];
        } else {
            return "None";
        }
    }

    public function onEntityDamage(EntityDamageEvent $event): void {
        if($event->getEntity() instanceof Player) {
            if($event->getEntity()->getLevel()->getFolderName() === KnockbackFFA::getInstance()->getGameData()->get("arena")) {
                if($event->getCause() === EntityDamageEvent::CAUSE_VOID) {
                    $event->setCancelled();
                    $event->getEntity()->teleport(Server::getInstance()->getLevelByName(KnockbackFFA::getInstance()->getGameData()->get("arena"))->getSpawnLocation());
                    if($this->lastDmg[strtolower($event->getEntity()->getName())] === "none") {
                        $event->getEntity()->getLevel()->addSound(new AnvilFallSound($event->getEntity()));
                        if(KnockbackFFA::getInstance()->scoretag == true) {
                            $event->getEntity()->setScoreTag(str_replace(["{kills}"], [$this->killstreak[strtolower($event->getEntity()->getName())]], KnockbackFFA::getInstance()->getGameData()->get("scoretag-format")));
                        }
                        $event->getEntity()->sendPopup(KnockbackFFA::getInstance()->getGameData()->get("prefix") . "§r§cYou died");
                    } else {
                        $this->killstreak[strtolower($event->getEntity()->getName())] = 0;
                        $killedBy = Server::getInstance()->getPlayer($this->lastDmg[strtolower($event->getEntity()->getName())]);
                        if($killedBy->isOnline()) {
                            $this->killstreak[strtolower($killedBy->getName())] = $this->killstreak[strtolower($killedBy->getName())] + 1;
                            $ks = [5, 10, 15, 20, 25, 30, 40, 50];
                            if (in_array($this->killstreak[strtolower($killedBy->getName())], $ks)) {
                                $players = $event->getEntity()->getLevel()->getPlayers();
            
                                foreach ($players as $p) {
                                    $p->getLevel()->addSound(new PopSound($p));
                                    $p->sendPopup(KnockbackFFA::getInstance()->getGameData()->get("prefix") . "§r§f" . Server::getInstance()->getPlayer($this->lastDmg[strtolower($event->getEntity()->getName())])->getDisplayName() . "§r§6 is at §e" . $this->killstreak[$this->lastDmg[strtolower($event->getEntity()->getName())]] . "§6 kills");
                                }
                                if(KnockbackFFA::getInstance()->scoretag == true) {
                                    $killedBy->setScoreTag(str_replace(["{kills}"], [$this->killstreak[strtolower($killedBy->getName())]], KnockbackFFA::getInstance()->getGameData()->get("scoretag-format")));
                                }
                            } else {
                                if(KnockbackFFA::getInstance()->scoretag == true) {
                                    $killedBy->setScoreTag(str_replace(["{kills}"], [$this->killstreak[strtolower($killedBy->getName())]], KnockbackFFA::getInstance()->getGameData()->get("scoretag-format")));
                                }
                                $killedBy->sendPopup(KnockbackFFA::getInstance()->getGameData()->get("prefix") . "§r§aYou killed §f" . $event->getEntity()->getDisplayName());
                            }
                            $killedBy->getLevel()->addSound(new FizzSound($killedBy));
                        }
                        $event->getEntity()->getLevel()->addSound(new AnvilFallSound($event->getEntity()));
                        if(KnockbackFFA::getInstance()->scoretag == true) {
                            $event->getEntity()->setScoreTag(str_replace(["{kills}"], [$this->killstreak[strtolower($event->getEntity()->getName())]], KnockbackFFA::getInstance()->getGameData()->get("scoretag-format")));
                        }
                        $event->getEntity()->sendPopup(KnockbackFFA::getInstance()->getGameData()->get("prefix") . "§r§cYou were killed by §f" . $killedBy->getDisplayName());
                    }
                    $this->lastDmg[strtolower($event->getEntity()->getName())] = "none";                    
                }
                if($event->getCause() === EntityDamageEvent::CAUSE_ENTITY_ATTACK) {
                    $event->getEntity()->setHealth(20);
                    $event->getEntity()->setSaturation(20);

                    // Protection radius thing
                    $damager = $event->getDamager();
                    if($damager instanceof Player) {
                        $x = $event->getEntity()->getX();
                        $y = $event->getEntity()->getY();
                        $z = $event->getEntity()->getZ();
                        $xx = $event->getEntity()->getLevel()->getSafeSpawn()->getX();
                        $yy = $event->getEntity()->getLevel()->getSafeSpawn()->getY();
                        $zz = $event->getEntity()->getLevel()->getSafeSpawn()->getZ();
                        $sr = KnockbackFFA::getInstance()->getGameData()->get("protection-radius");

                        if (abs($xx - $x) < $sr && abs($yy - $y) < $sr && abs($zz - $z) < $sr) {
                            $event->setCancelled();
                            $damager->sendMessage("§cYou can't hit the players here!");
                            return;
                        }

                        $this->lastDmg[strtolower($event->getEntity()->getName())] = strtolower($damager->getName());

                        $item = $damager->getInventory()->getItemInHand()->getId();
                        if(KnockbackFFA::getInstance()->massive_knockback == true) {
                            if ($item == 280) {
                                $x = $damager->getDirectionVector()->x;
                                $z = $damager->getDirectionVector()->z;
                                $event->getEntity()->knockBack($event->getEntity(), 0, $x, $z, 0.6);
                                return;
                            }
                        }
                    }
                }
                if($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
                    $event->setCancelled();
                }
            }
        }
    }

    public function Hunger(PlayerExhaustEvent $event) {
        if($event->getPlayer()->getLevel()->getFolderName() === KnockbackFFA::getInstance()->getGameData()->get("arena")) {
            $event->setCancelled(true);
        }
    }

    public function onDrop(PlayerDropItemEvent $event): void {
        $player = $event->getPlayer();
        // Prevent item drop on lobby
        if($player->getLevel()->getFolderName() === KnockBackFFA::getInstance()->getGameData()->get("arena")) {
            $event->setCancelled();
        }
    }

    public function onEntityLevelChange(EntityLevelChangeEvent $event): void {
        if($event->getEntity() instanceof Player) {
            if($event->getTarget()->getFolderName() === KnockBackFFA::getInstance()->getGameData()->get("arena")) {
                $player = $event->getEntity();
                $this->killstreak[strtolower($event->getEntity()->getName())] = 0;
                $player->setHealth(20);
                $player->setFood(20);

                $player->getInventory()->clearAll();
                $player->getArmorInventory()->clearAll();

                $stick = Item::get(280, 0, 1);
                if(KnockbackFFA::getInstance()->enchant_level == 0) {
                    $player->getInventory()->setItem(0, $stick);    
                } else {
                    $stick->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(12), KnockbackFFA::getInstance()->enchant_level));
                    $player->getInventory()->setItem(0, $stick);
                }
                
                $player->removeAllEffects();
                if(KnockbackFFA::getInstance()->speed_level !== 0) {
                    $player->addEffect(new EffectInstance(Effect::getEffect(1), 99999, KnockbackFFA::getInstance()->speed_level, false));
                }
                if(KnockbackFFA::getInstance()->speed_level !== 0) {
                    $player->addEffect(new EffectInstance(Effect::getEffect(8), 99999, KnockbackFFA::getInstance()->jump_boost_level, false));
                }

                if(KnockbackFFA::getInstance()->scoretag == true) {
                    $event->getEntity()->setScoreTag(str_replace(["{kills}"], [$this->killstreak[strtolower($event->getEntity()->getName())]], KnockbackFFA::getInstance()->getGameData()->get("scoretag-format")));
                }
            } else {
                $player = $event->getEntity();
                $player->removeAllEffects();
                $this->killstreak[strtolower($event->getEntity()->getName())] = "None";
                if(KnockbackFFA::getInstance()->scoretag == true) {
                    $event->getEntity()->setScoreTag("");
                }
            }
        }
    }
}
