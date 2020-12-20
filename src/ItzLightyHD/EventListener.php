<?php
declare(strict_types=1);

namespace ItzLightyHD;

use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\item\Item;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Effect;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\level\sound\ClickSound;

class EventListener implements Listener {

    /** @var KnockbackFFA $plugin */
    private $plugin;

    public function __construct(KnockbackFFA $plugin)
    {
        $this->plugin = $plugin;
    }

    public function getPlugin() : Plugin{
        return $this->plugin;
    }

    public function onEntityDamage(EntityDamageEvent $event): void {
        if($event->getEntity() instanceof Player) {
            if($event->getEntity()->getLevel()->getFolderName() === KnockbackFFA::getInstance()->getGameData()->get("arena")) {
                if($event->getCause() === EntityDamageEvent::CAUSE_VOID) {
                    $event->setCancelled();
                    $event->getEntity()->teleport(Server::getInstance()->getLevelByName(KnockbackFFA::getInstance()->getGameData()->get("arena"))->getSpawnLocation());
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

                        $item = $damager->getInventory()->getItemInHand()->getId();
                            if ($item == 280) {
                                $x = $damager->getDirectionVector()->x;
                                $z = $damager->getDirectionVector()->z;
                                $event->getEntity()->knockBack($event->getEntity(), 0, $x, $z, 0.6);
                                return;
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

    public function onEntityLevelChange(EntityLevelChangeEvent $event): void {
        if($event->getEntity() instanceof Player) {
            if($event->getTarget()->getFolderName() === KnockBackFFA::getInstance()->getGameData()->get("arena")) {
                $player = $event->getEntity();
                $player->setHealth(20);
                $player->setFood(20);

                $player->getInventory()->clearAll();
                $player->getArmorInventory()->clearAll();

                $stick = Item::get(280, 0, 1);
                $stick->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(12), 1));
                $player->getInventory()->setItem(0, $stick);

                $player->addEffect(new EffectInstance(Effect::getEffect(1), 99999, 1, false));
                $player->addEffect(new EffectInstance(Effect::getEffect(8), 99999, 1, false));

                $player->getLevel()->addSound(new ClickSound($player));
            } else {
                $player = $event->getEntity();
                $player->removeAllEffects();
            }
        }
    }
}