<?php
namespace MyPlot\provider;

use pocketmine\Player;
use PocketMoney\PocketMoney;

class PocketMoneyProvider implements EconomyProvider
{
	/** @var PocketMoney */
    private $plugin;

    public function __construct(PocketMoney $plugin) {
        $this->plugin = $plugin;
    }

    public function reduceMoney(Player $player, $amount) {
        $money = $this->plugin->getMoney($player->getName());
        if ($money === false or ($money - $amount) < 0) {
            return false;
        }
        if($this->plugin->setMoney($player->getName(), $money - $amount)) {
	        $this->plugin->getLogger()->debug("MyPlot reduced money of ".$player->getName());
	        return true;
        }
	    $this->plugin->getLogger()->debug("MyPlot failed to reduce money of ".$player->getName());
        return false;
    }
}