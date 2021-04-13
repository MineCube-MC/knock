<?php
declare(strict_types=1);

namespace ItzLightyHD\KnockbackFFA;

use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\Plugin;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\level\Position;
use pocketmine\level\Level;
use function implode;

class KnockbackCommand extends Command implements PluginIdentifiableCommand {

    /** @var KnockbackFFA $plugin */
    private $plugin;

    public function __construct(KnockbackFFA $plugin) {
        parent::__construct("kbffa", "Play an amazing minigame", "/kbffa");
        $this->plugin = $plugin;
    }

    public function getPlugin() : Plugin{
        return $this->plugin;
    }

    public function execute(CommandSender $sender, string $label, array $args): void {
        if(!($sender instanceof Player)) {
            $sender->sendMessage("§cOnly players can execute this command!");
            return;
        }
        $world = KnockbackFFA::getInstance()->getGameData()->get("arena");
        if(isset($args[0])) {
            if($args[0] == "kills") {
                    if(!isset($args[1])) {
                        $sender->sendMessage("§cUsage: /kbffa kills <player>");
                        return;
                    }
                    $player = Server::getInstance()->getPlayer($args[1]);
                    if($player->isOnline()) {
                        if(EventListener::getInstance()->getKillstreak($player->getName()) === "None") {
                            $sender->sendMessage(KnockbackFFA::getInstance()->getGameData()->get("prefix") . "§r§e" . $player->getDisplayName() . " §r§6isn't playing KnockBackFFA right now");
                        } else {
                            $sender->sendMessage(KnockbackFFA::getInstance()->getGameData()->get("prefix") . "§r§e" . $player->getDisplayName() . " §r§6is at §e" . EventListener::getInstance()->getKillstreak(Server::getInstance()->getPlayer($args[1])->getName()) . " §6kills");
                        }
                    } else {
                        $sender->sendMessage(KnockbackFFA::getInstance()->getGameData()->get("prefix") . "§r§c" . $args[1] . " isn't online!");
                    }
                return;
            } elseif($args[0] == "leave") {
                if(Server::getInstance()->getLevelByName(EventListener::getInstance()->lastworld[strtolower($sender->getName())]) instanceof Level) {
                    $sender->teleport(Server::getInstance()->getLevelByName(EventListener::getInstance()->lastworld[strtolower($sender->getName())])->getSpawnLocation());
                    $sender->sendMessage("§cThe world you joined the minigame from doesn't exist. It was probably removed or unloaded. If you are the server administrator, load the level again and retry.");
                }
            } else {
                $sender->sendMessage("Usage: /kbffa, /kbffa kills <player>");
                return;
            }
        }
        if(Server::getInstance()->getLevelByName($world) instanceof Level) {
            $sender->teleport(Server::getInstance()->getLevelByName($world)->getSpawnLocation());
        } else {
            $sender->sendMessage("§cCouldn't teleport you to the minigame. This is because the level is either not loaded or it doesn't even exist. If you are the server administrator, try to change the world in config.yml to make it work.");
        }
    }
}
