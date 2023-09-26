<?php

namespace ItzLightyHD\KnockbackFFA\command;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\RawStringArgument;
use ItzLightyHD\KnockbackFFA\command\subcommands\JoinCommand;
use ItzLightyHD\KnockbackFFA\command\subcommands\KillsCommand;
use ItzLightyHD\KnockbackFFA\command\subcommands\LeaveCommand;
use ItzLightyHD\KnockbackFFA\command\subcommands\SettingsCommand;
use ItzLightyHD\KnockbackFFA\Loader;
use pocketmine\command\CommandSender;

class KnockbackCommand extends BaseCommand
{
    /** @var Loader */
    protected $plugin;

    public function __construct(Loader $plugin, string $name, string $description)
    {
        $this->plugin = $plugin;
        $this->setPermission("knockbackffa.player");

       parent::__construct($plugin, $name, $description);
    }

    public function getPermission(): string
    {
        return $this->getPermission();
    }

    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param array $args
     * @return void
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $this->sendUsage($sender);
    }

    /**
     * @return void
     */
    protected function prepare(): void
    {
        $this->setPermission("knockbackffa.player");

        $this->registerSubCommand(new JoinCommand($this->plugin, "join", "Join the Knockback FFA"));
        $this->registerSubCommand(new LeaveCommand($this->plugin, "leave", "Leave the Knockback FFA"));
        $this->registerSubCommand(new KillsCommand($this->plugin, "kills", "View your kills in the Knockback FFA"));
        $this->registerSubCommand(new SettingsCommand($this->plugin, "settings", "Adjust settings for the Knockback FFA"));
    }
}
