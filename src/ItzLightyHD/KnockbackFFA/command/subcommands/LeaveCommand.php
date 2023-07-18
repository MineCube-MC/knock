<?php

namespace ItzLightyHD\KnockbackFFA\command\subcommands;

use CortexPE\Commando\BaseSubCommand;
use ItzLightyHD\KnockbackFFA\Loader;
use ItzLightyHD\KnockbackFFA\utils\GameSettings;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\World;

class LeaveCommand extends BaseSubCommand
{
    public function __construct(Loader $plugin)
    {
        parent::__construct("leave", "Leave the minigame", []);
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
        $lobbyWorld = GameSettings::getInstance()->lobby_world;
        if (Server::getInstance()->getWorldManager()->getWorldByName($lobbyWorld) instanceof World) {
            $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($lobbyWorld)->getSpawnLocation());
        } else {
            $sender->sendMessage("§cThe lobby world doesn't exist. It was probably removed or unloaded. If you are the server administrator, load the level again and retry.");
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