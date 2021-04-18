<?php
declare(strict_types=1);

namespace ItzLightyHD\KnockbackFFA;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use JackMD\UpdateNotifier\UpdateNotifier;
use CortexPE\Commando\PacketHooker;
use ItzLightyHD\KnockbackFFA\command\KnockbackCommand;
use pocketmine\level\Level;

class KnockbackFFA extends PluginBase {

    /** @var Config $config */
    protected $config;
    /** @var self $instance */
    protected static $instance;

    # Game modifiers
    public $massive_knockback;
    public $enchant_level;
    public $speed_level;
    public $jump_boost_level;

    # Scoretag?
    public $scoretag;

    // What happens when plugin is enabled
    public function onEnable(): void {
        // Sets the instance
        self::$instance = $this;
        // Registers the event listener
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        // Creates the data folder for the plugin
        @mkdir($this->getDataFolder());
        // Saves the resource on the data folder
        $this->saveResource("kbffa.yml");
        // Loads the arena that is wrote in the folder
        $this->getServer()->loadLevel($this->getGameData()->get("arena"));
        // Changes the game modifiers variables to the config ones
        $this->massive_knockback = $this->getGameData()->get("massive-knockback");
        $this->enchant_level = $this->getGameData()->get("enchant-level");
        $this->speed_level = $this->getGameData()->get("speed-level");
        $this->jump_boost_level = $this->getGameData()->get("jump-boost-level");
        // Changes the scoretag variable to the config one
        $this->scoretag = $this->getGameData()->get("kills-scoretag");
        // Checking for a new update (new system)
        UpdateNotifier::checkUpdate($this->getDescription()->getName(), $this->getDescription()->getVersion());
        // Register the packet hooker for Commando (command framework)
        if(!PacketHooker::isRegistered()) {
            PacketHooker::register($this);
        }
        // Registers the "kbffa" command
        $this->getServer()->getCommandMap()->register($this->getName(), new KnockbackCommand($this));
        // Check for world existance (if the world doesn't exist, it will instantly disable the plugin)
        if(!($this->getServer()->getLevelByName($this->getGameData()->get("arena")) instanceof Level)) {
            $this->getLogger()->alert("The world specified for the arena in the configuration file doesn't exist. Change it or make sure it has the correct name!");
            $plugin = $this->getServer()->getPluginManager()->getPlugin($this->getName());
            $this->getServer()->getPluginManager()->disablePlugin($plugin);
        }
        if($this->getGameData()->get("world-hander") == true) {
            if(!($this->getServer()->getLevelByName($this->getGameData()->get("arena")) instanceof Level)) {
                $this->getLogger()->alert("The world specified for the lobby in the configuration file doesn't exist. Change it or make sure it has the correct name!");
                $plugin = $this->getServer()->getPluginManager()->getPlugin($this->getName());
                $this->getServer()->getPluginManager()->disablePlugin($plugin);
            }
        }
    }

    // Helpul to make an API for the plugin
    public static function getInstance(): self
    {
        return self::$instance;
    }

    // Gets the config
    public function getGameData() {
        $data = new Config($this->getDataFolder() . "kbffa.yml", Config::YAML);
        return $data;
    }
}