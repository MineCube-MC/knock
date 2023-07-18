<?php

namespace ItzLightyHD\KnockbackFFA\command\subcommands;

use CortexPE\Commando\BaseSubCommand;
use ItzLightyHD\KnockbackFFA\Loader;
use ItzLightyHD\KnockbackFFA\utils\GameSettings;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\World;

class JoinCommand extends BaseSubCommand
{
    public function __construct(Loader $plugin)
    {
        parent::__construct("join", "Join the minigame", []);
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
        $world = GameSettings::getInstance()->world;
        if (Server::getInstance()->getWorldManager()->getWorldByName($world) instanceof World) {
            $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($world)->getSpawnLocation());
        } else {
            $sender->sendMessage("§cCouldn't teleport you to the minigame. This is because the level is either not loaded or it doesn't even exist. If you are the server administrator, try to change the world in the configuration file to make it work.");
        }
    }

    /**
     * @return void
     */
    protected function prepare(): void
    {
        $this->setPermission("knockbackffa.player");
    }
}