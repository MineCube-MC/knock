![KnockbackFFA](https://raw.githubusercontent.com/AetherPlace/KnockbackFFA/main/KnockbackFFA.gif)
# KnockbackFFA
The classic SumoFFA minigame, open to all for PocketMine-MP.

## How to setup & play
The plugin itself it's easy to setup. Follow the steps:
- First, setup the minigame in config.yml inserting the world where the minigame will be setup and the protection radius, so players can't hit each other on spawn
- (Optional) Setup the ScoreHud plugin to get a scoreboard with the killstreaks
- Join the server, and type in the chat `/kbffa` and you'll get teleported to the minigame
- Fight and get kills by using your Knockback stick
- The "winner" is the first player who reaches 40 kills or who is the first to get many kills in an amount of minutes (you can see the kills of a player by typing in the chat `/kbffa kills <playername>`)

## ScoreHud integration
Download the ScoreHud integration from [here](https://github.com/AetherPlace/KnockbackFFA/releases/latest/download/KnockbackFFAAddon.php), and on your config set `{kbffakills}` to display the kills of the player.
If the player isn't playing the minigame, the counter will return as 'None'.

## Developers and API
To get the current kills of a player just use this function from the EventListener:
```php
\ItzLightyHD\KnockbackFFA\EventListener::getInstance()->getKillstreak($player->getName());
```

## Contributing
Just go in our [GitHub page](https://github.com/AetherPlace/KnockbackFFA) and click the 'Sponsor' button to support us on Patreon.
You can also help us with finding bugs through issues and help us with the plugin by doing some pull requests.
