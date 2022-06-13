<?php

namespace ItzLightyHD\KnockbackFFA\utils;

use ItzLightyHD\KnockbackFFA\Loader;
use pocketmine\utils\Config;

class GameSettings
{

    protected static GameSettings $instance;

    public string $world;
    public string $lobby_world;
    public bool $massive_knockback;
    public bool $bow;
    public bool $snowballs;
    public bool $leap;
    public int $enchant_level;
    public int $knockback_level;
    public int $speed_level;
    public int $jump_boost_level;
    public bool $scoretag;

    public function __construct(Loader $plugin)
    {
        self::$instance = $this;
        $this->prepare();
    }

    public function prepare(): void
    {
        @mkdir(Loader::getInstance()->getDataFolder());
        Loader::getInstance()->saveResource("kbffa.yml");
        $this->world = $this->getConfig()->get("arena");
        $this->lobby_world = $this->getConfig()->get("lobby-world");
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

    public static function getInstance(): self
    {
        return self::$instance;
    }

    public function getConfig(): Config
    {
        return new Config(Loader::getInstance()->getDataFolder() . "kbffa.yml", Config::YAML);
    }

}