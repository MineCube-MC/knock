<?php

namespace ItzLightyHD\KnockbackFFA\event;

use ItzLightyHD\KnockbackFFA\Loader;
use pocketmine\event\Event;
use pocketmine\player\Player;

class PlayerKilledEvent extends Event
{
    /** @var Loader */
    protected Loader $plugin;
    /** @var Player */
    protected Player $player;
    /** @var Player */
    protected Player $damager;

    public function __construct(Player $player, Player $damager)
    {
        $this->plugin = Loader::getInstance();
        $this->player = $player;
        $this->damager = $damager;
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
    public function getDamager(): Player
    {
        return $this->damager;
    }
}
