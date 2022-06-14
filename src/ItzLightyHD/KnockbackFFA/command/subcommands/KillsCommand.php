<?php

namespace ItzLightyHD\KnockbackFFA\command\subcommands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use ItzLightyHD\KnockbackFFA\API;
use ItzLightyHD\KnockbackFFA\Loader;
use ItzLightyHD\KnockbackFFA\utils\GameSettings;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class KillsCommand extends BaseSubCommand
{

    public function __construct(Loader $plugin)
    {
        parent::__construct($plugin, "kills", "Get the kills of an online player", []);
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!isset($args["player"])) {
            $this->sendUsage();
        }
        $player = Server::getInstance()->getPlayerByPrefix($args["player"]);
        if ($player?->isOnline()) {
            if (API::getKills($player) === "none") {
                $sender->sendMessage(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§e" . $player?->getDisplayName() . " §r§6isn't playing KnockbackFFA right now");
            } else {
                $sender->sendMessage(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§e" . $player?->getDisplayName() . " §r§6is at §e" . API::getKills(Server::getInstance()->getPlayerByPrefix($args["player"])) . " §6kills");
            }
        } else {
            $sender->sendMessage(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§c" . $args["player"] . " isn't online!");
        }
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new RawStringArgument("player", false));
    }

}