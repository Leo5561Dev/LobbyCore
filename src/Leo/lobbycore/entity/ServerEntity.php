<?php

declare(strict_types=1);

namespace Leo\lobbycore\entity;

use Leo\lobbycore\LobbyCore;
use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\TransferPacket;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class ServerEntity extends Human
{
    
    /**
     * @param Player $player
     * @param string $server
     */
    public static function create(Player $player, string $server): self
    {
        $nbt = CompoundTag::create()
			->setTag("Pos", new ListTag([
				new DoubleTag($player->getLocation()->x),
				new DoubleTag($player->getLocation()->y),
				new DoubleTag($player->getLocation()->z)
			]))
			->setTag("Motion", new ListTag([
				new DoubleTag($player->getMotion()->x),
				new DoubleTag($player->getMotion()->y),
				new DoubleTag($player->getMotion()->z)
			]))
			->setTag("Rotation", new ListTag([
				new FloatTag($player->getLocation()->yaw),
				new FloatTag($player->getLocation()->pitch)
			]));
        $nbt->setString('serverName', $server);
        $entity = new self($player->getLocation(), $player->getSkin(), $nbt);
        return $entity;
    }
    
    /** @var string|null */
    private ?string $serverName = null;
    
    /**
     * @return CompoundTag
     */
    public function saveNBT(): CompoundTag
    {
        $nbt = parent::saveNBT();
        
        if ($this->serverName !== null)
            $nbt->setString('serverName', $this->serverName);
        return $nbt;
    }
    /**
     * @param CompoundTag $nbt
     */
    protected function initEntity(CompoundTag $nbt): void
    {
        parent::initEntity($nbt);
        
        if ($nbt->getTag('serverName') === null) {
            $this->flagForDespawn();
            return;
        }
        $this->serverName = $nbt->getString('serverName');
        $this->setNameTagAlwaysVisible(true);
        $this->setImmobile(true);
    }
    
    /**
     * @param int $currentTick
     * @return bool
     */
    public function onUpdate(int $currentTick): bool
    {
        $parent = parent::onUpdate($currentTick);
        
         if ($this->serverName !== null) {
            $nametag = str_replace(['{server_name}', '{space}'], [strtoupper($this->serverName), PHP_EOL], LobbyCore::getInstance()->getConfig()->get('entity.nametag.online'));
            $this->setNameTag(TextFormat::colorize($nametag));
        } else $this->setNameTag(TextFormat::colorize('&cERROR'));
        return $parent;
    }
    
    /**
     * @param EntityDamageEvent $source
     */
    public function attack(EntityDamageEvent $source): void
    {
        $source->cancel();
        
        if ($source instanceof EntityDamageByEntityEvent) {
            $damager = $source->getDamager();
            
            if (!$damager instanceof Player) return;
            
            if ($damager->getInventory()->getItemInHand()->getId() === 276 && $damager->hasPermission('removeEntity.lobbycore.permission')) {
                $this->kill();
                return;
            }
            $servers = LobbyCore::getInstance()->getConfig()->get('servers');
            
            if (!isset($servers[$this->serverName])) return;
            $data = $servers[$this->serverName];
            
            $pk = new TransferPacket;
            $pk->address = $data['address'];
            $pk->port = (int) $data['port'];
            
            $damager->getNetworkSession()->sendDataPacket($pk);
        }
    }
}
