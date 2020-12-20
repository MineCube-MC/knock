<?php
declare(strict_types=1);

namespace ItzLightyHD\KnockbackFFA;

use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\utils\Config;

class KnockbackFFA extends PluginBase {

    /** @var Config $config */
    protected $config;
    /** @var self $instance */
    protected static $instance;

    // What happens when plugin is enabled
    public function onEnable(): void {
        self::$instance = $this;
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getServer()->getCommandMap()->register("kbffa", new KbFFACommand($this));
        @mkdir($this->getDataFolder());
        $this->saveResource("kbffa.yml");
        $this->getServer()->loadLevel($this->getGameData()->get("arena"));
        $this->checkUpdate();
    }

    // Helpul to make an API for the plugin
    public static function getInstance(): self
    {
        return self::$instance;
    }

    public function getGameData() {
        $data = new Config($this->getDataFolder() . "kbffa.yml", Config::YAML);
        return $data;
    }

    // Make sure the plugin it's up-to-date
    public function checkUpdate() {
        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );

        $datei = file_get_contents("https://raw.githubusercontent.com/AetherPlace/KnockbackFFA/main/plugin.yml", false, stream_context_create($arrContextOptions));
        if (!$datei)
            return false;

        $datei = str_replace("\n", "", $datei);
        $newversion = explode("version: ", $datei);
        $newversion = explode("api: ", $newversion[1]);
        $newversion = $newversion[0];
        //var_dump($newversion);

        $plugin = $this->getServer()->getPluginManager()->getPlugin("KnockbackFFA");
        $version = $plugin->getDescription()->getVersion();
        //var_dump($version);
        if (!($version === $newversion)) {
            $update = false;
            if (intval($version[0]) < intval($newversion[0])) {
                $update = true;
            } elseif (intval($version[0]) === intval($newversion[0])) {
                if (intval($version[1]) < intval($newversion[1])) {
                    $update = true;
                } elseif (intval($version[1]) === intval($newversion[1])) {
                    if (intval($version[2]) < intval($newversion[2])) {
                        $update = true;
                    }
                }
            }

            if ($update) {
                $this->getLogger()->info("§6New update available!");
                $this->getLogger()->info("§6Local version: " . $version);
                $this->getLogger()->info("§6Newest version: " . $newversion);
                $this->getLogger()->info("§6Update your plugin by downloading the newest .phar at https://github.com/AetherPlace/KnockbackFFA/releases");
                return true;
            }
        }

        return false;
    }
}