<?php

namespace ItzLightyHD\KnockbackFFA\event;

use ItzLightyHD\KnockbackFFA\Loader;
use pocketmine\event\Event;
use pocketmine\player\Player;

class GameQuitEvent extends Event {

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