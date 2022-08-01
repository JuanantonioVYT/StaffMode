<?php

namespace Kurth\listeners;

use pocketmine\event\Listener;

use Kurth\StaffMode;

class PlayerListener implements Listener {

    public StaffMode $plugin;

    public function __construct(StaffMode $plugin) {
        $this->plugin = $plugin;
    }
}