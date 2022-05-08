<?php

namespace ItzLightyHD\KnockbackFFA\command\subcommands;

use ItzLightyHD\KnockbackFFA\Loader;
use ItzLightyHD\KnockbackFFA\utils\GameSettings;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\args\RawStringArgument;
use ItzLightyHD\KnockbackFFA\API;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class KillsCommand extends BaseSubCommand {

    private $plugin;

    public function __construct(Loader $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct("kills", "Get the kills of an online player");
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new RawStringArgument("player", false));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if(!isset($args["player"])) {
            $this->sendUsage();
        }
        $player = Server::getInstance()->getPlayerByPrefix($args["player"]);
        if($player->isOnline()) {
            if(API::getKills($player) === "none") {
                $sender->sendMessage(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§e" . $player->getDisplayName() . " §r§6isn't playing KnockbackFFA right now");
            } else {
                $sender->sendMessage(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§e" . $player->getDisplayName() . " §r§6is at §e" . API::getKills(Server::getInstance()->getPlayerByPrefix($args["player"])) . " §6kills");
            }
        } else {
            $sender->sendMessage(GameSettings::getInstance()->getConfig()->get("prefix") . "§r§c" . $args["player"] . " isn't online!");
        }
    }

}