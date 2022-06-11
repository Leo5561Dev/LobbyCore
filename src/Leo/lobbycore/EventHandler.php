<?php

declare(strict_types=1);

namespace Leo\lobbycore;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\particle\LavaDripParticle;

class EventHandler implements Listener
{
    
    /** @var array */
    private array $cooldowns = [];
    
    /**
     * @param BlockBreakEvent $event
     */
    public function handleBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();
        
        if ($player->hasPermission('break.block.lobbycore.permission'))
            return;
        
        $event->cancel();
    }
    
    /**
     * @param BlockPlaceEvent $event
     */
    public function handlePlace(BlockPlaceEvent $event): void
    {
        $player = $event->getPlayer();
        
        if ($player->hasPermission('place.block.lobbycore.permission'))
            return;
        
        $event->cancel();
    }
    
    /**
     * @param PlayerDamageEvent $event
     */
    public function handleDamage(EntityDamageEvent $event): void
    {
        $cause = $event->getCause();
        $entity = $event->getEntity();
        
        if (!$entity instanceof Player)
            return;
        
        if ($cause === EntityDamageEvent::CAUSE_VOID)
            $entity->teleport($entity->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
        
        $event->cancel();
    }
    
    /**
     * @param PlayerDropItemEvent $event
     */
    public function handleDropItem(PlayerDropItemEvent $event): void
    {
        $item = $event->getItem();
        
        if ($item->getNamedTag()->getTag('no_drop') !== null)
            $event->cancel();
    }
    
    /**
     * @param PlayerJoinEvent $event
     */
    public function handleJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        LobbyCore::getInstance()->getSessionFactory()->createSession($player);
        
        # Message
        $message = str_replace('{player_name}', $player->getName(), LobbyCore::getInstance()->getConfig()->get('player.join.message'));
        $event->setJoinMessage(TextFormat::colorize($message));
    }
    
    /**
     * @param PlayerQuitEvent $event
     */
    public function handleQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        LobbyCore::getInstance()->getSessionFactory()->removeSession($player);
        
        # Message
        $message = str_replace('{player_name}', $player->getName(), LobbyCore::getInstance()->getConfig()->get('player.quit.message'));
        $event->setQuitMessage(TextFormat::colorize($message));
    }
    
    /**
     * @param DataPacketReceiveEvent $event
     */
    public function handlePacketReceive(DataPacketReceiveEvent $event): void
    {
        $packet = $event->getPacket();
        $origin = $event->getOrigin();
        
        if (!$packet instanceof AnimatePacket) return;
        $player = $origin->getPlayer();
        
        if ($player === null) return;
       
       if ($packet->action !== 1) return;
       
       if ($player->isOnGround()) return;
       
       if (!$player->isSurvival()) return;
       
       if (isset($this->cooldowns[$player->getName()]) && $this->cooldowns[$player->getName()] > time()) return;
       $player->getWorld()->addParticle($player->getPosition()->asVector3(), new LavaDripParticle(), [$player]);
       $player->getWorld()->addParticle($player->getPosition()->asVector3(), new LavaDripParticle(), [$player]);
       $player->setMotion($player->getDirectionVector()->multiply(0.8)->add(0, $player->getEyeHeight(), 0));
       $this->cooldowns[$player->getName()] = time() + 5;
    }
}
