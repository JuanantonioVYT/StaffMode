<?php

namespace Kurth;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

use Kurth\listeners\PlayerListener;
use Kurth\listeners\StaffListener;

use Kurth\commands\StaffCommand;
use Kurth\commands\ChatCommand;
use Kurth\commands\FreezeCommand;
use Kurth\commands\PInfoCommand;

use Kurth\utils\KitManager;
use Kurth\utils\UtilsManager;

class StaffMode extends PluginBase implements Listener {

    public $staffmode = [];
    public $backup_items = [];
    public $backup_armor = [];
    public $backup_gamemode = [];

    public $freeze = [];

    private static $instance;

    public static function getInstance() : StaffMode {
        return self::$instance;
    }

    public function onLoad() : void {
        self::$instance = $this;

        $this->saveResource("config.yml");
        $this->saveResource("messages.yml");
    }

    public function onEnable() : void {
        $logger = $this->getLogger();
        $logger->notice("StaffMode for PocketMine-API 4 make by iKurth");
        $logger->notice("Download in: ");
        $logger->notice("Subscribe to Kurth in YouTube");

        $plugin = $this->getServer()->getPluginManager();
        $plugin->registerEvents($this, $this);
        $plugin->registerEvents(new PlayerListener($this), $this);
        $plugin->registerEvents(new StaffListener($this), $this);

        $command = $this->getServer()->getCommandMap();
        $command->register("staff", new StaffCommand($this));
        $command->register("sc", new ChatCommand($this));
        $command->register("freeze", new FreezeCommand($this));
        $command->register("pinfo", new PInfoCommand($this));
    }

    public static function getKitManager() : KitManager {
        return new KitManager();
    }

    public static function getUtilsManager() : UtilsManager {
        return new UtilsManager();
    }
}