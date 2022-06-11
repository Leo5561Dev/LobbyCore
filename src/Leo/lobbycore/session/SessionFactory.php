<?php

declare(strict_types=1);

namespace Leo\lobbycore\session;

use Leo\lobbycore\LobbyCore;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class SessionFactory
{
    
    /** @var Session[] */
    private array $sessions = [];
    
    /**
     * @return array
     */
    public function getSessions(): array
    {
        return $this->sessions;
    }
    
    /**
     * @param Player $player
     */
    public function createSession(Player $player): void
    {
        $this->sessions[$player->getName()] = new Session($player, SessionScoreboard::create($player, TextFormat::colorize(LobbyCore::getInstance()->getConfig()->get('scoreboard.title'))));
    }
    
    /**
     * @param Player $player
     */
    public function removeSession(Player $player): void
    {
        unset($this->sessions[$player->getName()]);
    }
}
