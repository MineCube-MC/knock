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
use function implode;

class KbFFACommand extends Command implements PluginIdentifiableCommand {

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
        $sender->teleport(Server::getInstance()->getLevelByName($world)->getSpawnLocation());
    }
}