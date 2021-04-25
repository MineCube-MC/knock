<?php

namespace ItzLightyHD\KnockbackFFA\utils;

use ItzLightyHD\KnockbackFFA\Loader;
use pocketmine\utils\Config;

class GameSettings {

    protected static $instance;

    public $massive_knockback;
    public $bow;
    public $snowballs;
    public $leap;
    public $enchant_level;
    public $knockback_level;
    public $speed_level;
    public $jump_boost_level;

    public $scoretag;

    public function __construct(Loader $plugin)
    {
        self::$instance = $this;
        $this->prepare();
    }

    public static function getInstance(): self
    {
        return self::$instance;
    }

    public function prepare() {
        @mkdir(Loader::getInstance()->getDataFolder());
        Loader::getInstance()->saveResource("kbffa.yml");
        $this->massive_knockback = $this->getConfig()->get("massive-knockback");
        $this->bow = $this->getConfig()->get("bow");
        $this->snowballs = $this->getConfig()->get("snowballs");
        $this->leap = $this->getConfig()->get("leap");
        $this->enchant_level = $this->getConfig()->get("enchant-level");
        $this->knockback_level = $this->getConfig()->get("knockback-level");
        $this->speed_level = $this->getConfig()->get("speed-level");
        $this->jump_boost_level = $this->getConfig()->get("jump-boost-level");
        $this->scoretag = $this->getConfig()->get("kills-scoretag");
    }

    public function getConfig() {
        $data = new Config(Loader::getInstance()->getDataFolder() . "kbffa.yml", Config::YAML);
        return $data;
    }

}