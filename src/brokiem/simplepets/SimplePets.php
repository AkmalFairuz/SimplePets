<?php

declare(strict_types=1);

namespace brokiem\simplepets;

use brokiem\simplepets\command\Command;
use brokiem\simplepets\database\DatabaseQuery;
use brokiem\simplepets\manager\PetManager;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

final class SimplePets extends PluginBase {
    use SingletonTrait;

    private DataConnector $database;
    private PetManager $petManager;

    public function getDatabase(): DataConnector {
        return $this->database;
    }

    public function getPetManager(): PetManager {
        return $this->petManager;
    }

    protected function onEnable(): void {
        $this->getLogger()->debug("Registering listener");
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

        $this->getLogger()->debug("Registering command");
        $this->getServer()->getCommandMap()->register("spet", new Command("spet", "SimplePet commands"));

        $this->getLogger()->debug("Loading database");
        $this->initDatabase();

        $this->getLogger()->debug("Loading pets");
        $this->initPets();

        $this->getLogger()->debug("Plugin successfully enabled");
    }

    private function initDatabase(): void {
        $this->database = libasynql::create($this, $this->getConfig()->get("database"), [
            "sqlite" => "sqlite.sql",
            "mysql" => "mysql.sql"
        ]);

        $this->database->executeGeneric(DatabaseQuery::SIMPLEPETS_INIT_INFO);
        $this->database->executeGeneric(DatabaseQuery::SIMPLEPETS_INIT_DATA);

        $this->database->waitAll();
    }

    private function initPets(): void {
        $this->petManager = new PetManager();
    }
}