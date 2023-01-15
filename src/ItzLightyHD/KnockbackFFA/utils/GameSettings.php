<?php

namespace ItzLightyHD\KnockbackFFA\utils;

use ItzLightyHD\KnockbackFFA\Loader;
use pocketmine\utils\Config;

final class GameSettings
{
    protected static GameSettings $instance;

    /** @var string */
    public string $world;
    /** @var string */
    public string $lobby_world;
    /** @var bool */
    public bool $massive_knockback;
    /** @var bool */
    public bool $bow;
    /** @var bool */
    public bool $snowballs;
    /** @var bool */
    public bool $leap;
    /** @var int */
    public int $enchant_level;
    /** @var int */
    public int $knockback_level;
    /** @var int */
    public int $speed_level;
    /** @var int */
    public int $jump_boost_level;
    /** @var bool */
    public bool $scoretag;
    /** @var bool */
    public bool $doublejump;

    public function __construct()
    {
        self::$instance = $this;
        $this->prepare();
    }

    /**
     * @return void
     */
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
        $this->doublejump = $this->getConfig()->get("double-jump");
    }

    /**
     * @return static
     */
    public static function getInstance(): self
    {
        return self::$instance;
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return new Config(Loader::getInstance()->getDataFolder() . "kbffa.yml", Config::YAML);
    }
}
