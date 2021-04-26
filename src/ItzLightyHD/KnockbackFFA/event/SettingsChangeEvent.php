<?php

namespace ItzLightyHD\KnockbackFFA\event;

use ItzLightyHD\KnockbackFFA\Loader;
use pocketmine\event\Event;

class SettingsChangeEvent extends Event {

    protected $plugin;

    public function __construct()
    {
        $this->plugin = Loader::getInstance();
    }

    public function getPlugin(): Loader
    {
        return $this->plugin;
    }

}