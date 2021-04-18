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
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerQuitEvent;
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

    // Last world for each player
    public $lastworld = [];

    public function __construct(KnockbackFFA $plugin)
    {
        $this->plugin = $plugin;
        self::$instance = $this;
    }

    public function getPlugin(): KnockbackFFA {
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
        $this->lastWorld[$name] = $player->getLevel()->getFolderName();
        if($this->plugin->getGameData()->get("world-handler") == true) {
            if($player->getLevel()->getFolderName() == $this->plugin->getGameData()->get("arena")) {
                $lobbyWorld = $this->plugin->getGameData()->get("lobby-world");
                $player->teleport(Server::getInstance()->getLevelByName($lobbyWorld)->getSpawnLocation());
            }
        }
    }

    public function onQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        foreach(Server::getInstance()->getOnlinePlayers() as $p) {
            $world = $p->getLevel()->getFolderName();
            if($world == $this->plugin->getGameData()->get("arena")) {
                if($this->lastDmg[strtolower($p->getName())] == strtolower($player->getName())) {
                    $this->lastDmg[strtolower($p->getName())] = "none";
                }
            }
        } 
    }

    public function getKillstreak($player) {
        $target = Server::getInstance()->getPlayer($player)->getName();
        if(isset($this->killstreak[strtolower($target)])) {
            return $this->killstreak[strtolower($target)];
        } else {
            return "None";
        }
    }

    public function onBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        if($player->getLevel()->getFolderName() == $this->plugin->getGameData()->get("arena")) {
            $event->setCancelled();
        }
    }

    public function onEntityDamage(EntityDamageEvent $event): void {
        $player = $event->getEntity();
        if($player instanceof Player) {
            if($event->getEntity()->getLevel()->getFolderName() === KnockbackFFA::getInstance()->getGameData()->get("arena")) {
                $cause = $event->getCause();
                if($event->getCause() === EntityDamageEvent::CAUSE_VOID) {
                    $event->setCancelled();
                    $event->getEntity()->teleport(Server::getInstance()->getLevelByName(KnockbackFFA::getInstance()->getGameData()->get("arena"))->getSpawnLocation());
                    if($this->lastDmg[strtolower($player->getName())] === "none") {
                        $event->getEntity()->getLevel()->addSound(new AnvilFallSound($event->getEntity()));
                        $this->killstreak[strtolower($player->getName())] = 0;
                        if(KnockbackFFA::getInstance()->scoretag == true) {
                            $event->getEntity()->setScoreTag(str_replace(["{kills}"], [$this->killstreak[strtolower($player->getName())]], KnockbackFFA::getInstance()->getGameData()->get("scoretag-format")));
                        }
                        $player->sendPopup(KnockbackFFA::getInstance()->getGameData()->get("prefix") . "§r§cYou died");
                    } else {
                        $this->killstreak[strtolower($player->getName())] = 0;
                        $killedBy = Server::getInstance()->getPlayer($this->lastDmg[strtolower($player->getName())]);
                        if($killedBy->isOnline()) {
                            $this->killstreak[strtolower($killedBy->getName())] = $this->killstreak[strtolower($killedBy->getName())] + 1;
                            $ks = [5, 10, 15, 20, 25, 30, 40, 50];
                            if (in_array($this->killstreak[strtolower($killedBy->getName())], $ks)) {
                                $players = $event->getEntity()->getLevel()->getPlayers();
            
                                foreach ($players as $p) {
                                    $p->getLevel()->addSound(new PopSound($p));
                                    $p->sendPopup(KnockbackFFA::getInstance()->getGameData()->get("prefix") . "§r§f" . Server::getInstance()->getPlayer($this->lastDmg[strtolower($player->getName())])->getDisplayName() . "§r§6 is at §e" . $this->killstreak[$this->lastDmg[strtolower($player->getName())]] . "§6 kills");
                                }
                                if(KnockbackFFA::getInstance()->scoretag == true) {
                                    $killedBy->setScoreTag(str_replace(["{kills}"], [$this->killstreak[strtolower($killedBy->getName())]], KnockbackFFA::getInstance()->getGameData()->get("scoretag-format")));
                                }
                            } else {
                                if(KnockbackFFA::getInstance()->scoretag == true) {
                                    $killedBy->setScoreTag(str_replace(["{kills}"], [$this->killstreak[strtolower($killedBy->getName())]], KnockbackFFA::getInstance()->getGameData()->get("scoretag-format")));
                                }
                                $killedBy->sendPopup(KnockbackFFA::getInstance()->getGameData()->get("prefix") . "§r§aYou killed §f" . $player->getDisplayName());
                            }
                            $killedBy->getLevel()->addSound(new FizzSound($killedBy));
                        }
                        $event->getEntity()->getLevel()->addSound(new AnvilFallSound($event->getEntity()));
                        if(KnockbackFFA::getInstance()->scoretag == true) {
                            $event->getEntity()->setScoreTag(str_replace(["{kills}"], [$this->killstreak[strtolower($player->getName())]], KnockbackFFA::getInstance()->getGameData()->get("scoretag-format")));
                        }
                        $player->sendPopup(KnockbackFFA::getInstance()->getGameData()->get("prefix") . "§r§cYou were killed by §f" . $killedBy->getDisplayName());
                    }
                    $this->lastDmg[strtolower($player->getName())] = "none";                    
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
                $sr = KnockbackFFA::getInstance()->getGameData()->get("protection-radius");

                if (abs($xx - $x) < $sr && abs($yy - $y) < $sr && abs($zz - $z) < $sr) {
                    $event->setCancelled();
                    $damager->sendMessage("§cYou can't hit the players here!");
                    return;
                }

                $this->lastDmg[strtolower($player->getName())] = strtolower($damager->getName());

                $item = $damager->getInventory()->getItemInHand()->getId();
                if(KnockbackFFA::getInstance()->massive_knockback == true) {
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
        $player = $event->getEntity();
        if($player instanceof Player) {
            if($event->getTarget()->getFolderName() == KnockBackFFA::getInstance()->getGameData()->get("arena")) {
                $this->lastworld[strtolower($player->getName())] = $event->getOrigin()->getName();
                $this->killstreak[strtolower($player->getName())] = 0;
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
                if(KnockbackFFA::getInstance()->jump_boost_level !== 0) {
                    $player->addEffect(new EffectInstance(Effect::getEffect(8), 99999, KnockbackFFA::getInstance()->jump_boost_level, false));
                }

                if(KnockbackFFA::getInstance()->scoretag == true) {
                    $player->setScoreTag(str_replace(["{kills}"], [$this->killstreak[strtolower($player->getName())]], KnockbackFFA::getInstance()->getGameData()->get("scoretag-format")));
                }
            } else {
                if ($event->getOrigin()->getName() == $this->plugin->getGameData()->get("arena")) {
                    $player->getInventory()->clearAll();
                    $player->removeAllEffects();
                    $this->killstreak[strtolower($player->getName())] = "None";
                    if(KnockbackFFA::getInstance()->scoretag == true) {
                        $player->setScoreTag("");
                    }
                }
                // $this->lastworld[strtolower($player->getName())] = $event->getTarget()->getFolderName();
            }
        }
    }
}
