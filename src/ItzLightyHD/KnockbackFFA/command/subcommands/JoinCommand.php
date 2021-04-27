<?php

namespace ItzLightyHD\KnockbackFFA\command\subcommands;

use ItzLightyHD\KnockbackFFA\Loader;
use ItzLightyHD\KnockbackFFA\utils\GameSettings;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\Server;

class JoinCommand extends BaseSubCommand {

    private $plugin;

    public function __construct(Loader $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct("join", "Join the minigame");
    }

    protected function prepare(): void
    {

    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if(!$sender instanceof Player) {
            $sender->sendMessage("§cOnly players are allowed to use this subcommand!");
            return;
        }
        $world = GameSettings::getInstance()->world;
        if(Server::getInstance()->getLevelByName($world) instanceof Level) {
            if($sender instanceof Player) {
                $sender->teleport(Server::getInstance()->getLevelByName($world)->getSpawnLocation());
            }
        } else {
            $sender->sendMessage("§cCouldn't teleport you to the minigame. This is because the level is either not loaded or it doesn't even exist. If you are the server administrator, try to change the world in the configuration file to make it work.");
        }
    }

}