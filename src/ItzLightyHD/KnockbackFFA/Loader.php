<?php

namespace ItzLightyHD\KnockbackFFA;

use CortexPE\Commando\exception\HookAlreadyRegistered;
use CortexPE\Commando\PacketHooker;
use ItzLightyHD\KnockbackFFA\command\KnockbackCommand;
use ItzLightyHD\KnockbackFFA\listeners\DamageListener;
use ItzLightyHD\KnockbackFFA\listeners\EssentialsListener;
use ItzLightyHD\KnockbackFFA\listeners\LevelListener;
use ItzLightyHD\KnockbackFFA\utils\GameSettings;
use ItzLightyHD\KnockbackFFA\utils\KnockbackPlayer;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\world\World;

final class Loader extends PluginBase
{
    /** @var self $instance */
    protected static Loader $instance;
    /** @var Config $config */
    protected Config $config;

    // What happens when plugin is enabled

    /**
     * @throws HookAlreadyRegistered
     */
    public function onEnable(): void
    {
        // Sets the instance
        self::$instance = $this;
        // Registers the event listeners
        $this->registerEvents();
        // Register the game settings
        new GameSettings();
        // Loads the arena that is written in the folder and upgrades it to the PM 4 world format
        $this->getServer()->getWorldManager()->loadWorld(GameSettings::getInstance()->world, true);
        // Register the packet hooker for Commando (command framework)
        if (!PacketHooker::isRegistered()) {
            PacketHooker::register($this);
        }
        // Registers the "kbffa" command
        $this->getServer()->getCommandMap()->register("kbffa", new KnockbackCommand($this, "kbffa", "join kbffa arena!!"));
        // Check for world existance (if the world doesn't exist, it will instantly disable the plugin)
        if (!($this->getServer()->getWorldManager()->getWorldByName(GameSettings::getInstance()->world) instanceof World)) {
            $this->getLogger()->alert("The world specified for the arena in the configuration file doesn't exist. Change it or make sure it has the correct name!");
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }
        if (!($this->getServer()->getWorldManager()->getWorldByName(GameSettings::getInstance()->lobby_world) instanceof World) && !($this->getServer()->getWorldManager()->getWorldByName(GameSettings::getInstance()->world) instanceof World)) {
            $this->getLogger()->alert("The world specified for the lobby in the configuration file doesn't exist. Change it or make sure it has the correct name!");
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }
    }

    /**
     * @return void
     */
    private function registerEvents(): void
    {
        // Knockback player, used for getting the killstreak, the last damager, etc...
        $this->getServer()->getPluginManager()->registerEvents(new KnockbackPlayer(), $this);
        // All the event listeners
        $this->getServer()->getPluginManager()->registerEvents(new DamageListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new EssentialsListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new LevelListener(), $this);
    }

    /**
     * @return self
     * Helpful to make an API for the plugin
     */
    public static function getInstance(): self
    {
        return self::$instance;
    }
}
