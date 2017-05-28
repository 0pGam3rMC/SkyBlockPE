<?php
namespace MyPlot\subcommand;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;

class ListSubCommand extends SubCommand
{
	/**
	 * @param CommandSender $sender
	 * @return bool
	 */
	public function canUse(CommandSender $sender) {
		return ($sender instanceof Player) and $sender->hasPermission("myplot.command.list");
	}

	/**
	 * @param Player $sender
	 * @param string[] $args
	 * @return bool
	 */
	public function execute(CommandSender $sender, array $args) {
		if($sender->hasPermission("myplot.admin.list")) {
			if(count($args) == 1) {
				$plots = $this->getPlugin()->getPlotsOfPlayer($sender->getName(), $sender->getLevel()->getName());
				foreach($plots as $plot) {
					$name = $plot->name;
					$x = $plot->X;
					$z = $plot->Z;

					$sender->sendMessage(TF::YELLOW.$this->translateString("list.found", [$name, $x, $z]));
				}
			}else{
				$plots = $this->getPlugin()->getPlotsOfPlayer($sender->getName(), $sender->getLevel()->getName());
				foreach($plots as $plot) {
					$name = $plot->name;
					$x = $plot->X;
					$z = $plot->Z;

					$sender->sendMessage(TF::YELLOW.$this->translateString("list.found", [$name, $x, $z]));
				}
				return true;
			}
		}elseif($sender->hasPermission("myplot.command.list")) {
			$plots = $this->getPlugin()->getPlotsOfPlayer($sender->getName(), $sender->getLevel()->getName());
			foreach($plots as $plot) {
				$name = $plot->name;
				$x = $plot->X;
				$z = $plot->Z;

				$sender->sendMessage(TF::YELLOW.$this->translateString("list.found", [$name, $x, $z]));
			}
		}
		return true;
	}
}