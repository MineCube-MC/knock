<p>
    <h1>Knock<img src="https://raw.githubusercontent.com/ApexieDevelopment/Knock/main/KnockbackFFA.gif" height="64" width="64" align="left" alt=""></h1><br>
    <b>The classic sumo minigame fun to play for PocketMine-MP.</b>
</p>

<p>
    [![Discord](https://img.shields.io/badge/chat-on%20discord-7289da.svg)](https://discord.gg/a75eNEAtrt)
    [![License](https://img.shields.io/github/license/ApexieDevelopment/Knock)](https://github.com/ApexieDevelopment/Knock)
    [![Support me on Patreon](https://img.shields.io/endpoint.svg?url=https%3A%2F%2Fshieldsio-patreon.vercel.app%2Fapi%3Fusername%3DItzLightyHD%26type%3Dpatrons&style=flat)](https://patreon.com/ItzLightyHD) <br>
</p>

### Features
- Easy to setup
- Customizable scoretag for the players
- Arena map loading when starting the plugin
- Ingame sounds, to make the gameplay alive
- Customizable game behaviour (both in-game and in config file)
- Random maps <b>(Coming soon)</b>
- Permissions <b>(Coming soon)</b>
- Scoreboard <b>(Coming soon)</b>

### How to setup & play
The plugin itself it's easy to setup. Follow the steps:
- Install the plugin
- Change the config values as you like
- Go to your server and type /kbffa
- Enjoy ;)

### Commands and Permissions
#### Commands
- kbffa (or knock) -> The minigame command
  - join -> Makes the player join the minigame
  - leave -> Makes the player leave the minigame
  - kills -> Check the kills of a player (can be used in console too)
  - settings -> Customize the minigame settings directly in-game
#### Permissions
- knockbackffa.customize -> Customize the minigame settings

### Troubleshooting
#### The server crashes
When you reload the server, the plugin will mess up. What you can do is just to restart the server every time you changed a configuration file.

### Developers and API
#### Getting a killstreak
You can get the killstreak of any player from the event listener as in this example:
```php
use ItzLightyHD\KnockbackFFA\EventListener;
use pocketmine\Player;

class API {

  protected static $instance;

  public function __construct() {
    self::$instance = $this;
  }

  public function getInstance(): self {
    return self::$instance;
  }

  public function getKillstreak(Player $player): void {
    EventListener::getInstance()->getKillstreak($player->getName());
  }

}
```
If the player isn't playing KnockbackFFA, the function will return as "None".