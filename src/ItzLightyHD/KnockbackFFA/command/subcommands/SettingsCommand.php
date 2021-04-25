<?php

namespace ItzLightyHD\KnockbackFFA\command\subcommands;

use ItzLightyHD\KnockbackFFA\KnockbackFFA;
use ItzLightyHD\KnockbackFFA\EventListener;
use CortexPE\Commando\BaseSubCommand;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\command\CommandSender;
use pocketmine\level\Level;
use pocketmine\item\Item;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\Player;
use pocketmine\Server;

class SettingsCommand extends BaseSubCommand {

    private $plugin;

    public function __construct(KnockbackFFA $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct("settings", "Customize the minigame directly from the game");
    }

    protected function prepare(): void
    {
        $this->setPermission("knockbackffa.customize");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if(!$sender instanceof Player) {
            $sender->sendMessage("§cOnly players are allowed to use this subcommand!");
            return;
        }
        $this->customizeGame($sender);
    }

    public function customizeGame(Player $player): void
    {
        $form = new CustomForm(function (Player $player, $data) {
            if($data == null) {
                return;
            }
            if($data[1] == true) {
                $this->plugin->massive_knockback = true;
            } else {
                $this->plugin->massive_knockback = false;
            }
            $this->plugin->enchant_level = intval($data[2]);
            $this->plugin->speed_level = intval($data[3]);
            $this->plugin->jump_boost_level = intval($data[4]);
            $this->reloadGame($this->plugin->getGameData()->get("arena"));
        });
        $form->setTitle($this->plugin->getGameData()->get("prefix") . "§r§8Settings");
        $form->addLabel("Customize the game options here. If a value is blank, the effect will be disabled. After the server restart, the values will be the same as those from the configuration file.");
        $form->addToggle("Massive knockback", $this->isMassiveKnockbackEnabled());
        $form->addInput("Enchant level", $this->plugin->enchant_level);
        $form->addInput("Speed level", $this->plugin->speed_level);
        $form->addInput("Jump boost level", $this->plugin->jump_boost_level);
        $player->sendForm($form);
    }

    public function reloadGame(string $world): void
    {
        if(Server::getInstance()->getLevelByName($world) instanceof Level) {
            foreach(Server::getInstance()->getLevelByName($world)->getPlayers() as $player) {
                $player->teleport(Server::getInstance()->getLevelByName($world)->getSpawnLocation());
                $player->getInventory()->clearAll();
                $player->removeAllEffects();
                EventListener::getInstance()->killstreak[strtolower($player->getName())] = 0;
                if($this->plugin->scoretag == true) {
                    $player->setScoreTag(str_replace(["{kills}"], [EventListener::getInstance()->killstreak[strtolower($player->getName())]], $this->plugin->getGameData()->get("scoretag-format")));
                }
                $player->setHealth(20);
                $player->setFood(20);

                $player->getInventory()->clearAll();
                $player->getArmorInventory()->clearAll();

                $stick = Item::get(280, 0, 1);
                if($this->plugin->enchant_level == 0) {
                    $player->getInventory()->setItem(0, $stick);    
                } else {
                    $stick->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(12), $this->plugin->enchant_level));
                    $player->getInventory()->setItem(0, $stick);
                }
                
                $player->removeAllEffects();
                if($this->plugin->speed_level !== 0) {
                    $player->addEffect(new EffectInstance(Effect::getEffect(1), 99999, $this->plugin->speed_level, false));
                }
                if($this->plugin->speed_level !== 0) {
                    $player->addEffect(new EffectInstance(Effect::getEffect(8), 99999, $this->plugin->jump_boost_level, false));
                }
            }
        }
    }

    public function isMassiveKnockbackEnabled(): bool
    {
        if($this->plugin->massive_knockback == true) {
            return true;
        } else {
            return false;
        }
    }

}