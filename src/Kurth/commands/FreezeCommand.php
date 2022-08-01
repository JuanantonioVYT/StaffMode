<?php

namespace Kurth\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;

use Kurth\StaffMode;

class FreezeCommand extends Command {

    public StaffMode $plugin;

    public function __construct(StaffMode $plugin) {
        parent::__construct("freeze", "freeze suspected cheating players");
        parent::setPermission("freeze.use.cmd");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
        if (!($sender instanceof Player)) {
            $sender->sendMessage("Usage this command in-game");
            return;
        }

        if (!$sender->hasPermission("freeze.use.cmd")) {
            $sender->sendMessage(TextFormat::colorize("&c[StaffMode] &7You need permissions to access the options of this command"));
            return;
        }

        if (!isset ($args[0])) {
            $sender->sendMessage(TextFormat::colorize("&c[StaffMode] &7Verify that the argument is placed correctly"));
            return;
        }

        $player = StaffMode::getInstance()->getServer()->getPlayerByPrefix(array_shift($args));
        if (!$player instanceof Player) {
            $sender->sendMessage(TextFormat::colorize("&c[StaffMode] &7Player not found"));
            return;
        }

        $config = new Config(StaffMode::getInstance()->getDataFolder()."config.yml", Config::YAML);
        $messages = new Config(StaffMode::getInstance()->getDataFolder()."messages.yml", Config::YAML);
        if ($config->get("not-freeze-yourself") === true) {
            if ($player->getName() === $sender->getName()) {
                $sender->sendMessage(TextFormat::colorize("&c[StaffMode] &7Error occurred while executing the command, you cannot freeze yourself"));
                return;
            }
        }

        if (isset ($args[0])) {
            if (!in_array ($player->getName(), $this->plugin->freeze)) {
                $this->plugin->freeze[] = $player->getName();

                if ($config->get("allow-title-freeze") === true) {
                    $player->sendTitle(TextFormat::colorize($messages->get("freeze-title")));
                }
                
                if ($config->get("allow-message-freeze") === true) {
                    $player->sendMessage(TextFormat::colorize(str_replace(["{player}", "{staff}"], [$player->getName(), $sender->getName()], $messages->get("freeze-message"))));
                }
                
                if ($config->get("allows-broadcast-freeze") === true) {
                    StaffMode::getInstance()->getServer()->broadcastMessage(TextFormat::colorize(str_replace(["{player}", "{staff}"], [$player->getName(), $sender->getName()], $messages->get("server-broadcast-freeze"))));
                }
            } else if (in_array ($player->getName(), $this->plugin->freeze)) {
                unset($this->plugin->freeze[array_search($player->getName(), $this->plugin->freeze)]);

                if ($config->get("allow-title-freeze") === true) {
                    $player->sendTitle(TextFormat::colorize($messages->get("unfreeze-title")));
                }
                
                if ($config->get("allow-message-freeze") === true) {
                    $player->sendMessage(TextFormat::colorize(str_replace(["{player}", "{staff}"], [$player->getName(), $sender->getName()], $messages->get("unfreeze-message"))));
                }
                
                if ($config->get("allows-broadcast-freeze") === true) {
                    StaffMode::getInstance()->getServer()->broadcastMessage(TextFormat::colorize(str_replace(["{player}", "{staff}"], [$player->getName(), $sender->getName()], $messages->get("server-broadcast-unfreeze"))));
                }
            }
        }
        return;
    }
}