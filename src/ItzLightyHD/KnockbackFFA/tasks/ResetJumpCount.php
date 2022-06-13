<?php

# Create PocketMine task that resets the jump count

namespace ItzLightyHD\KnockbackFFA\tasks;

use ItzLightyHD\KnockbackFFA\utils\KnockbackPlayer;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;

class ResetJumpCount extends Task {
    
        /** @var Player $player */
        private Player $player;
    
        public function __construct(Player $player)
        {
            $this->player = $player;
        }
    
        public function onRun(): void
        {
            KnockbackPlayer::getInstance()->jumps[$this->player->getName()] = 0;
        }
    
}