<?php

namespace Kurth\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;

use Kurth\StaffMode;
use pocketmine\player\GameMode;

class StaffCommand extends Command {

    public StaffMode $plugin;

    public function __construct(StaffMode $plugin) {
        parent::__construct("staff", "toggle staffmode on or off");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
        if (!($sender instanceof Player)) {
            $sender->sendMessage("Usage this command in-game");
            return;
        }

        if (!$sender->hasPermission("staffmode.use.cmd")) {
            $sender->sendMessage(TextFormat::colorize("&c[StaffMode] &7You need permissions to access the options of this command"));
            return;
        }
        
        $config = new Config(StaffMode::getInstance()->getDataFolder()."config.yml", Config::YAML);
        $messages = new Config(StaffMode::getInstance()->getDataFolder()."messages.yml", Config::YAML);
        if (!in_array ($sender->getName(), $this->plugin->staffmode)) {
            $this->plugin->staffmode[] = $sender->getName();
            $this->plugin->backup_items[$sender->getName()] = $sender->getInventory()->getContents();
            $this->plugin->backup_armor[$sender->getName()] = $sender->getArmorInventory()->getContents();
            $this->plugin->backup_gamemode[$sender->getName()] = $sender->getGamemode();
            $this->plugin->backup_effects[$sender->getName()] = $sender->getEffects();

            if ($config->get("change-gamemode-in-staffmode") === true) {
                $sender->setGamemode(GameMode::CREATIVE());
            } else if ($config->get("allow-flight-in-staffmode") === true) {
                $sender->setAllowFlight(true);
            } else if ($config->get("allow-title-staffmode") === true) {
                $sender->sendTitle(TextFormat::colorize($messages->get("staffmode-title-enabled")));
            } else if ($config->get("allow-message-enable-staffmode") === true) {
                $sender->sendMessage(TextFormat::colorize($messages->get("staffmode-message-enabled")));
            }

            $sender->getInventory()->clearAll();
            $sender->getArmorInventory()->clearAll();
            $sender->getEffects()->clear();
            $sender->extinguish();

            StaffMode::getKitManager()->getKitStaff($sender);
        } else if (in_array ($sender->getName(), $this->plugin->staffmode)) {
            $sender->getInventory()->clearAll();
            $sender->getArmorInventory()->clearAll();
            $sender->getEffects()->clear();
            $sender->extinguish();
            $sender->setFlying(false);
            $sender->setAllowFlight(false);

            unset($this->plugin->staffmode[array_search($sender->getName(), $this->plugin->staffmode)]);
            $sender->getInventory()->setContents($this->plugin->backup_items[$sender->getName()]);
            $sender->getInventory()->setContents($this->plugin->backup_armor[$sender->getName()]);
            $sender->setGamemode($this->plugin->backup_gamemode[$sender->getName()]);
            $sender->getEffects()->add($this->plugin->backup_effects[$sender]);

            if ($config->get("allow-title-staffmode") === true) {
                $sender->sendTitle(TextFormat::colorize($messages->get("staffmode-title-disable")));
            } else if ($config->get("allow-message-staffmode") === true) {
                $sender->sendMessage(TextFormat::colorize($messages->get("staffmode-message-disabled")));
            }

            foreach (StaffMode::getInstance()->getServer()->getOnlinePlayers() as $players) {
                $players->showPlayer($sender);
            }
        }
    }
}