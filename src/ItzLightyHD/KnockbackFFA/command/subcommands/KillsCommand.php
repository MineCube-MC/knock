<?php

namespace ItzLightyHD\KnockbackFFA\command\subcommands;

use ItzLightyHD\KnockbackFFA\KnockbackFFA;
use ItzLightyHD\KnockbackFFA\EventListener;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\args\RawStringArgument;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class KillsCommand extends BaseSubCommand {

    private $plugin;

    public function __construct(KnockbackFFA $plugin)
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
        $player = Server::getInstance()->getPlayer($args["player"]);
        if($player->isOnline()) {
            if(EventListener::getInstance()->getKillstreak($player->getName()) === "None") {
                $sender->sendMessage($this->plugin->getGameData()->get("prefix") . "§r§e" . $player->getDisplayName() . " §r§6isn't playing KnockbackFFA right now");
            } else {
                $sender->sendMessage($this->plugin->getGameData()->get("prefix") . "§r§e" . $player->getDisplayName() . " §r§6is at §e" . EventListener::getInstance()->getKillstreak(Server::getInstance()->getPlayer($args["player"])->getName()) . " §6kills");
            }
        } else {
            $sender->sendMessage($this->plugin->getGameData()->get("prefix") . "§r§c" . $args["player"] . " isn't online!");
        }
    }

}