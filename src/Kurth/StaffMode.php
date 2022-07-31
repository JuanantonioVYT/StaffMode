<?php

namespace Kurth;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

use Kurth\utils\KitManager;

use Kurth\commands\StaffCommand;
use Kurth\commands\ChatCommand;

class StaffMode extends PluginBase implements Listener {

    public $staffmode = [];
    public $backup_items = [];
    public $backup_armor = [];
    public $backup_gamemode = [];
    public $backup_effects = [];

    private static $instance;

    public static function getInstance() : StaffMode {
        return self::$instance;
    }

    public function onLoad() : void {
        self::$instance = $this;
    }

    public function onEnable() : void {
        $logger = $this->getLogger();
        $logger->notice("StaffMode for PocketMine-API 4 make by iKurth");
        $logger->notice("Download in: ");
        $logger->notice("Subscribe to Kurth in YouTube");

        $plugin = $this->getServer()->getPluginManager();
        $plugin->registerEvents($this, $this);

        $command = $this->getServer()->getCommandMap();
        $command->register("staff", new StaffCommand($this));
        $command->register("sc", new ChatCommand($this));

        $this->saveResource("config.yml");
        $this->saveResource("messages.yml");
    }

    public static function getKitManager() : KitManager {
        return new KitManager();
    }
}