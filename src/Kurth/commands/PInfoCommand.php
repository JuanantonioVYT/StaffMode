<?php

namespace Kurth\commands;


use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;

use Kurth\StaffMode;

class PInfoCommand extends Command {

    public StaffMode $plugin;

    public function __construct(StaffMode $plugin) {
        parent::__construct("pinfo", "see the information of connected players");
        parent::setPermission("pinfo.use.cmd");
        parent::setAliases(["playerinfo"]);
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
        if (!($sender instanceof Player)) {
            $sender->sendMessage("Usage this command in-game");
            return;
        }

        if (!$sender->hasPermission("pinfo.use.cmd")) {
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

        $messages = new Config(StaffMode::getInstance()->getDataFolder()."messages.yml", Config::YAML);
        $sender->sendMessage(TextFormat::colorize(str_replace(["{player}", "{ping}", "{health}", "{address}", "{platform}"], [$player->getName(), $player->getNetworkSession()->getPing(), (int)$player->getHealth(), $player->getNetworkSession()->getIp(), StaffMode::getInstance()->getUtilsManager()->getPlayerPlatform($player)], $messages->get("pinfo-message"))));
        return;
    }
}