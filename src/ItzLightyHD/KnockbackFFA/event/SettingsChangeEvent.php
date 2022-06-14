<?php

namespace ItzLightyHD\KnockbackFFA\event;

use ItzLightyHD\KnockbackFFA\Loader;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use pocketmine\player\Player;

class SettingsChangeEvent extends Event implements Cancellable
{

    use CancellableTrait;

    protected Loader $plugin;
    protected Player $player;

    public function __construct(Player $player)
    {
        $this->plugin = Loader::getInstance();
        $this->player = $player;
    }

    public function getPlugin(): Loader
    {
        return $this->plugin;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

}