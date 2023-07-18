<?php

namespace ItzLightyHD\KnockbackFFA\command\subcommands;

use CortexPE\Commando\BaseSubCommand;
use ItzLightyHD\KnockbackFFA\API;
use ItzLightyHD\KnockbackFFA\event\SettingsChangeEvent;
use ItzLightyHD\KnockbackFFA\Loader;
use ItzLightyHD\KnockbackFFA\utils\GameSettings;
use ItzLightyHD\KnockbackFFA\utils\KnockbackKit;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\World;

class SettingsCommand extends BaseSubCommand
{
    public function __construct(Loader $plugin)
    {
        parent::__construct("settings", "Customize the minigame directly from the game", []);
    }

    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param array $args
     * @return void
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("§cOnly players are allowed to use this subcommand!");
            return;
        }
        $this->customizeGame($sender);
    }

    /**
     * @param Player $player
     * @return void
     */
    public function customizeGame(Player $player): void
    {
        $form = new CustomForm(function (Player $player, $data) {
            if ($data === null) {
                return;
            }
            $ev = new SettingsChangeEvent($player);
            $ev->call();
            if ($ev->isCancelled()) {
                return;
            }
            if ($data[1]) {
                GameSettings::getInstance()->massive_knockback = true;
            } else {
                GameSettings::getInstance()->massive_knockback = false;
            }
            if ($data[2]) {
                GameSettings::getInstance()->bow = true;
            } else {
                GameSettings::getInstance()->bow = false;
            }
            if ($data[3]) {
                GameSettings::getInstance()->snowballs = true;
            } else {
                GameSettings::getInstance()->snowballs = false;
            }
            if ($data[4]) {
                GameSettings::getInstance()->leap = true;
            } else {
                GameSettings::getInstance()->leap = false;
            }
            if ($data[5]) {
                GameSettings::getInstance()->doublejump = true;
            } else {
                GameSettings::getInstance()->doublejump = false;
            }
            GameSettings::getInstance()->enchant_level = (int)$data[6];
            GameSettings::getInstance()->knockback_level = (int)$data[7];
            GameSettings::getInstance()->speed_level = (int)$data[8];
            GameSettings::getInstance()->jump_boost_level = (int)$data[9];
            $this->reloadGame(GameSettings::getInstance()->world);
        });
        $form->setTitle(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§8Settings");
        $form->addLabel("Customize the game options here. If a value is blank, the effect will be disabled. After the server restart, the values will be the same as those from the configuration file.");
        $form->addToggle("Massive knockback", API::isMassiveKnockbackEnabled());
        $form->addToggle("Bow", API::isBowEnabled());
        $form->addToggle("Snowballs", API::isSnowballsEnabled());
        $form->addToggle("Leap", API::isLeapEnabled());
        $form->addToggle("Double-Jump", API::isDoubleJumpEnabled());
        $form->addInput("Stick's knockback level", GameSettings::getInstance()->enchant_level);
        $form->addInput("Bow's knockback level", GameSettings::getInstance()->knockback_level);
        $form->addInput("Speed level", GameSettings::getInstance()->speed_level);
        $form->addInput("Jump boost level", GameSettings::getInstance()->jump_boost_level);
        $player->sendForm($form);
    }

    /**
     * @param string $world
     * @return void
     */
    public function reloadGame(string $world): void
    {
        if (Server::getInstance()->getWorldManager()->getWorldByName($world) instanceof World) {
            foreach (Server::getInstance()->getWorldManager()->getWorldByName($world)->getPlayers() as $player) {
                $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName($world)->getSpawnLocation());
                new KnockbackKit($player);
            }
        }
    }

    /**
     * @return void
     */
    protected function prepare(): void
    {
        $this->setPermission("knockbackffa.customize");
    }
}
