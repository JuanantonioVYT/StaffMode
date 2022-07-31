<?php

namespace Kurth\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;

use Kurth\StaffMode;

class ChatCommand extends Command {

    public StaffMode $plugin;

    public function __construct(StaffMode $plugin) {
        parent::__construct("sc", "send private messages to all connected staff");
        parent::setPermission("staffchat.use.cmd");
        parent::setAliases(["staffchat"]);
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
        if (!($sender instanceof Player)) {
            $sender->sendMessage("Usage this command in-game");
            return;
        }

        if (!$sender->hasPermission("staffchat.use.cmd")) {
            $sender->sendMessage(TextFormat::colorize("&c[StaffMode] &7You need permissions to access the options of this command"));
            return;
        }

        if (!isset ($args[0])) {
            $sender->sendMessage(TextFormat::colorize("&c[StaffMode] &7Verify that the argument is placed correctly"));
            return;
        }

        $config = new Config(StaffMode::getInstance()->getDataFolder()."config.yml", Config::YAML);
        $messages = new Config(StaffMode::getInstance()->getDataFolder()."messages.yml", Config::YAML);
        if (isset ($args[0])) {
            $message = implode(" ", $args);
            foreach (StaffMode::getInstance()->getServer()->getOnlinePlayers() as $players) {
                if ($players->hasPermission("staffchat.use.view")) {
                    $players->sendMessage(TextFormat::colorize(str_replace(["{player}", "{message}"], [$sender->getName(), $message], $messages->get("staffchat-send-message"))));
                }
            }
            return;
        }
        return;
    }
}