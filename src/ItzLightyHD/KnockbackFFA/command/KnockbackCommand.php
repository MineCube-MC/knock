<?php

namespace ItzLightyHD\KnockbackFFA\command;

use ItzLightyHD\KnockbackFFA\Loader;
use CortexPE\Commando\BaseCommand;
use ItzLightyHD\KnockbackFFA\command\subcommands\JoinCommand;
use ItzLightyHD\KnockbackFFA\command\subcommands\LeaveCommand;
use ItzLightyHD\KnockbackFFA\command\subcommands\KillsCommand;
use ItzLightyHD\KnockbackFFA\command\subcommands\SettingsCommand;
use pocketmine\command\CommandSender;

class KnockbackCommand extends BaseCommand {

    private Loader $plugin;

    public function __construct(Loader $plugin) {
        $this->plugin = $plugin;
        parent::__construct($plugin, "kbffa", "Play an amazing sumo FFA minigame", ["knock"]);
    }

    protected function prepare(): void
    {
        $this->registerSubCommand(new JoinCommand($this->plugin));
        $this->registerSubCommand(new LeaveCommand($this->plugin));
        $this->registerSubCommand(new KillsCommand($this->plugin));
        $this->registerSubCommand(new SettingsCommand($this->plugin));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $this->sendUsage();
    }
}