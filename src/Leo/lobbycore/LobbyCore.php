<?php

declare(strict_types=1);

namespace Leo\lobbycore;

use Leo\lobbycore\command\ServerCommand;
use Leo\lobbycore\entity\ServerEntity;
use Leo\lobbycore\item\Servers;
use Leo\lobbycore\session\SessionFactory;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\World;

class LobbyCore extends PluginBase
{
    use SingletonTrait;
    
    /** @var SessionFactory */
    private SessionFactory $sessionFactory;
    
    protected function onLoad(): void
    {
        self::setInstance($this);
    }
    
    protected function onEnable(): void
    {
        # Save config
        $this->saveDefaultConfig();
        # Register session factory
        $this->sessionFactory = new SessionFactory;
        # Register entity
        EntityFactory::getInstance()->register(ServerEntity::class, function (World $world, CompoundTag $nbt): ServerEntity {
            return new ServerEntity(EntityDataHelper::parseLocation($nbt, $world), ServerEntity::parseSkinNBT($nbt), $nbt);
        }, ['ServerEntity']);
        # Register command
        $this->getServer()->getCommandMap()->register('LobbyCore', new ServerCommand());
        # Register event handler
        $this->getServer()->getPluginManager()->registerEvents(new EventHandler(), $this);
        # Register items
        ItemFactory::getInstance()->register(new Servers(new ItemIdentifier(ItemIds::COMPASS, 0), "Compass"), true);
        # Task
        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            foreach ($this->sessionFactory->getSessions() as $session)
                $session->update();
        }), 20);
    }
    
    /**
     * @return SessionFactory
     */
    public function getSessionFactory(): SessionFactory
    {
        return $this->sessionFactory;
    }
}
