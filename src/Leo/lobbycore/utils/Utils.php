<?php

declare(strict_types=1);

namespace Leo\lobbycore\utils;

use dktapps\pmforms\FormIcon;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use Leo\lobbycore\LobbyCore;
use pocketmine\network\mcpe\protocol\TransferPacket;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class Utils
{
    
    /**
     ,* @param Player $player
      */
    public static function createMenuServers(Player $player): void
    {
        $config = LobbyCore::getInstance()->getConfig();
        $servers = [];
        
        foreach ($config->get('servers') as $name => $data) {
            if (isset($data['img']) && $data['img'] !== null)
                $servers[] = new MenuOption(strtoupper($name), new FormIcon($data['img'], $data['img-type']));
            else $servers[] = new MenuOption(strtoupper($name));
        }
        
        $menu = new MenuForm(
            TextFormat::colorize($config->get('form.title')),
            TextFormat::colorize($config->get('form.description')),
            $servers,
            function (Player $submitter, int $selected) use($config): void
            {
                $srvs = array_values($config->get('servers'));
                
                if (isset($srvs[$selected])) {
                    $pk = new TransferPacket;
                    $pk->address = $srvs[$selected]['address'];
                    $pk->port = (int) $srvs[$selected]['port'];
                    
                    $submitter->getNetworkSession()->sendDataPacket($pk);
                }
            }
        );
        $player->sendForm($menu);
    }
}
