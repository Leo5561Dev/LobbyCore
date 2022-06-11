<?php

declare(strict_types=1);

namespace Leo\lobbycore\command;

use Leo\lobbycore\entity\ServerEntity;
use Leo\lobbycore\LobbyCore;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class ServerCommand extends Command
{
    
    /**
     * ServerCommand construct.
     */
    public function __construct()
    {
        parent::__construct('lobbycore', 'Command for server');
        $this->setPermission('server.command.lobbycore');
    }
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof Player)
            return;
        
        if (!isset($args[0])) {
            $sender->sendMessage(TextFormat::colorize('&cUse /server [npc]'));
            return;
        }
        
        switch (strtolower($args[0])) {
            case 'npc':
                if (!isset($args[0])) {
                    $sender->sendMessage(TextFormat::colorize('&cUse /server npc [server]'));
                    return;
                }
                $servers = LobbyCore::getInstance()->getConfig()->get('servers');
                
                if (!isset($servers[$args[1]])) {
                    $sender->sendMessage(TextFormat::colorize('&cThe server does not exist'));
                    return;
                }
                $entity = ServerEntity::create($sender, $args[1]);
                $entity->spawnToAll();
                $sender->sendMessage(TextFormat::colorize('&aNPC created successfully'));
                break;
        }
    }
}
