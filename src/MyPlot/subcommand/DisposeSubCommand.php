<?php

namespace MyPlot\subcommand;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class DisposeSubCommand extends SubCommand{
	/**
	 * @param CommandSender $sender
	 * @return bool
	 */
	public function canUse(CommandSender $sender){
		return ($sender instanceof Player) and $sender->hasPermission("skyblock.command.dispose");
	}

	/**
	 * @param CommandSender|Player $sender
	 * @param string[] $args
	 * @return bool
	 */
	public function execute(CommandSender $sender, array $args){
		if (empty($args)){
			return false;
		}
		$plot = $this->getPlugin()->getPlotByPosition($sender->getPosition());
		if ($plot === null){
			$sender->sendMessage(TextFormat::RED . $this->translateString("notinplot"));
			return true;
		}
		if ($plot->owner !== $sender->getName() and !$sender->hasPermission("skyblock.admin.dispose")){
			$sender->sendMessage(TextFormat::RED . $this->translateString("notowner"));
			return true;
		}

		if (isset($args[0]) and $args[0] == $this->translateString("confirm")){
			$economy = $this->getPlugin()->getEconomyProvider();
			$price = $this->getPlugin()->getLevelSettings($plot->levelName)->disposePrice;
			if ($economy !== null and !$economy->reduceMoney($sender, $price)){
				$sender->sendMessage(TextFormat::RED . $this->translateString("dispose.nomoney"));
				return true;
			}

			if ($this->getPlugin()->disposePlot($plot)){
				$sender->sendMessage($this->translateString("dispose.success"));
			} else{
				$sender->sendMessage(TextFormat::RED . $this->translateString("error"));
			}
		} else{
			$plotId = TextFormat::GREEN . $plot . TextFormat::WHITE;
			$sender->sendMessage($this->translateString("dispose.confirm", [$plotId]));
		}
		return true;
	}
}