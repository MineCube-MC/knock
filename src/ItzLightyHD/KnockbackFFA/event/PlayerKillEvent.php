<?php

namespace ItzLightyHD\KnockbackFFA\event;

use ItzLightyHD\KnockbackFFA\Loader;
use pocketmine\event\Event;
use pocketmine\player\Player;

class PlayerKillEvent extends Event
{
    /** @var Loader */
    protected Loader $plugin;
    /** @var Player */
    protected Player $player;
    /** @var Player */
    protected Player $target;

    public function __construct(Player $player, Player $target)
    {
        $this->plugin = Loader::getInstance();
        $this->player = $player;
        $this->target = $target;
    }

    /**
     * @return Loader
     */
    public function getPlugin(): Loader
    {
        return $this->plugin;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @return Player
     */
    public function getTarget(): Player
    {
        return $this->target;
    }
}
