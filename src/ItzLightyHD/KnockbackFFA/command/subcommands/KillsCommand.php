<?php

namespace ItzLightyHD\KnockbackFFA\command\subcommands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use ItzLightyHD\KnockbackFFA\API;
use ItzLightyHD\KnockbackFFA\Loader;
use ItzLightyHD\KnockbackFFA\utils\GameSettings;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class KillsCommand extends BaseSubCommand
{
    public function __construct(Loader $plugin)
    {
        parent::__construct("kills", "Get the kills of an online player", []);
    }

    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param array $args
     * @return void
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!isset($args["player"])) {
            $this->sendUsage();
        }
        $player = Server::getInstance()->getPlayerByPrefix($args["player"]);
        if ($player !== null && $player->isOnline()) {
            $kills = API::getKills($player);
            if ($kills === "none") {
                $sender->sendMessage(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§e" . $player->getDisplayName() . " §r§6isn't playing KnockbackFFA right now");
            } else {
                $sender->sendMessage(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§e" . $player->getDisplayName() . " §r§6is at §e" . $kills . " §6kills");
            }
        } else {
            $sender->sendMessage(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§c" . $args["player"] . " isn't online!");
        }
    }

    /**
     * @return void
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission("knockbackffa.player");

        $this->registerArgument(0, new RawStringArgument("player", false));
    }
}
