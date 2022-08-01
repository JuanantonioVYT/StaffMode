<?php

namespace Kurth\listeners;

use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use Kurth\StaffMode;
use Vecnavium\FormsUI\SimpleForm;

class ItemListener implements Listener {

    public StaffMode $plugin;
    public Array $iplayers;
    public Array $teleport;

    public function __construct(StaffMode $plugin) {
        $this->plugin = $plugin;
    }

    public function onItem(PlayerItemUseEvent $event) : void {
        $player = $event->getPlayer();
        $item = $event->getItem();
        if ($item->getName() === TextFormat::colorize("&bTeleport Player(s)")) {
            $this->getTeleport($player);
        }

        if ($item->getName() === TextFormat::colorize("&3Random Teleport Player(s)")) {
            $players = [];
            foreach (StaffMode::getInstance()->getServer()->getOnlinePlayers() as $iplayers) {
                $players[] = $iplayers;
            }

            $iplayer = $players[array_rand($players)];

            if (!$iplayer instanceof Player) {
                $player->sendMessage(TextFormat::colorize("&c[StaffMode] &7There is no player connected"));
            }

            if ($player->getName() === $iplayer->getName()) {
                return;
            }
            
            if ($iplayer instanceof Player) {
                $player->teleport($iplayer->getPosition());
                $player->sendMessage(TextFormat::colorize("&c[StaffMode] &7You have teleported to the player: &f{$iplayer->getName()}"));
            }
        }

        if ($item->getName() === TextFormat::colorize("&aToggle Vanish")) {
            $player->sendMessage(TextFormat::colorize("&c[StaffMode] &7You are now in vanish"));
            StaffMode::getKitManager()->getKitVanish($player);
            foreach (StaffMode::getInstance()->getServer()->getOnlinePlayers() as $players) {
                $players->hidePlayer($player);
            }
        }

        if ($item->getName() === TextFormat::colorize("&cToggle Vanish")) {
            $player->sendMessage(TextFormat::colorize("&c[StaffMode] &7You are not in vanish"));
            StaffMode::getKitManager()->getKitStaff($player);
            foreach (StaffMode::getInstance()->getServer()->getOnlinePlayers() as $players) {
                $players->showPlayer($player);
            }
        }
        return;
    }

    public function getTeleport(Player $player) : SimpleForm {
        $form = new SimpleForm(function (Player $player, $data = null) {
            if ($data === null) {
                return;
            }

            $this->teleport[$player->getName()] = $data;
            if ($this->teleport[$player->getName()] === $player->getName()) {
                return;
            }

            if (isset ($this->teleport[$player->getName()])) {
                $iplayer = StaffMode::getInstance()->getServer()->getPlayerExact($this->teleport[$player->getName()]);
                if ($iplayer instanceof Player) {
                    $player->teleport($iplayer->getPosition());
                    $player->sendMessage(TextFormat::colorize("&c[StaffMode] &7You have teleported to the player: &f{$iplayer->getName()}"));
                }
            }
        });

        $form->setTitle("List connected players");
        foreach (StaffMode::getInstance()->getServer()->getOnlinePlayers() as $players) {
            $form->addButton($players->getName(), -1, "", $players->getName());
        }
        $player->sendForm($form);
        return $form;
    }

    public function onEntity(EntityDamageByEntityEvent $event) : void {
        $entity = $event->getEntity();
        $damager = $event->getDamager();
        $messages = new Config(StaffMode::getInstance()->getDataFolder()."messages.yml", Config::YAML);
        if ($entity instanceof Player and $damager instanceof Player) {
            $item = $damager->getInventory()->getItemInHand();
            if ($item->getName() === TextFormat::colorize("&6Player Information")) {
                $damager->sendMessage(TextFormat::colorize(str_replace(["{player}", "{ping}", "{health}", "{address}", "{platform}"], [$entity->getName(), $entity->getNetworkSession()->getPing(), (int)$entity->getHealth(), $entity->getNetworkSession()->getIp(), StaffMode::getInstance()->getUtilsManager()->getPlayerPlatform($entity)], $messages->get("pinfo-message"))));
                $event->cancel();
            }

            if ($item->getName() === TextFormat::colorize("&cKick Player(s)")) {
                $entity->kick(TextFormat::colorize(str_replace(["{player}", "{staff}"], [$entity->getName(), $damager->getName()], $messages->get("kicked-player"))));
                $event->cancel();
            }

            if ($item->getName() === TextFormat::colorize("&bFreeze Player(s)")) {
                if (!in_array ($entity->getName(), $this->plugin->freeze)) {
                    $this->plugin->freeze[] = $entity->getName();

                    $entity->sendTitle(TextFormat::colorize($messages->get("freeze-title")));
                    $entity->sendMessage(TextFormat::colorize(str_replace(["{player}", "{staff}"], [$entity->getName(), $damager->getName()], $messages->get("freeze-message"))));
                    StaffMode::getInstance()->getServer()->broadcastMessage(TextFormat::colorize(str_replace(["{player}", "{staff}"], [$entity->getName(), $damager->getName()], $messages->get("server-broadcast-freeze"))));
                    $event->cancel();
                } else if (in_array ($entity->getName(), $this->plugin->freeze)) {
                    unset($this->plugin->freeze[array_search($entity->getName(), $this->plugin->freeze)]);

                    $entity->sendTitle(TextFormat::colorize($messages->get("unfreeze-title")));
                    $entity->sendMessage(TextFormat::colorize(str_replace(["{player}", "{staff}"], [$entity->getName(), $damager->getName()], $messages->get("unfreeze-message"))));
                    StaffMode::getInstance()->getServer()->broadcastMessage(TextFormat::colorize(str_replace(["{player}", "{staff}"], [$entity->getName(), $damager->getName()], $messages->get("server-broadcast-unfreeze"))));
                    $event->cancel();
                }
            }
        }
        return;
    }
}