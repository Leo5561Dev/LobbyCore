<?php

declare(strict_types=1);

namespace Leo\lobbycore\item;

use Leo\lobbycore\utils\Utils;
use pocketmine\item\Compass;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class Servers extends Compass
{
    
    /**
     * @param Player $player
     * @param Vector3 $directionVector
     * @return ItemUseResult
     */
    public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult
    {
        Utils::createMenuServers($player);
		return ItemUseResult::SUCCESS();
	}
}
