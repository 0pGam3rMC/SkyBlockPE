<?php
namespace MyPlot\subcommand;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class AutoSubCommand extends SubCommand
{
    public function canUse(CommandSender $sender) {
        return ($sender instanceof Player) and $sender->hasPermission("myplot.command.auto");
    }

    public function execute(CommandSender $sender, array $args) {
        if (count($args) >= 2) {
            return false;
        }
        $player = $sender->getServer()->getPlayer($sender->getName());
        $levelName = $player->getLevel()->getName();
        if (!$this->getPlugin()->isLevelLoaded($levelName)) {
            $sender->sendMessage(TextFormat::RED . $this->translateString("auto.notplotworld"));
            return true;
        }
        if (($plot = $this->getPlugin()->getNextFreePlot($levelName)) !== null) {
        	if($this->getPlugin()->getConfig()->getNested("Commands.Auto.AutoCenter", false) === true) {
		        $this->getPlugin()->teleportMiddle($plot, $player);
	        }else{
		        $this->getPlugin()->teleportPlayerToPlot($player, $plot);
	        }
            $sender->sendMessage($this->translateString("auto.success", [$plot->X, $plot->Z]));
        } else {
            $sender->sendMessage(TextFormat::RED . $this->translateString("auto.noplots"));
        }
        if($this->getPlugin()->getConfig()->getNested("Commands.Auto.AutoClaim", false) === true) {
        	$c = new ClaimSubCommand($this->getPlugin(),"claim");
        	if($c->canUse($sender)){
		        if(!isset($args[0])) {
			        $args[0] = "";
		        }
		        $c->execute($sender,[$args[0]]);
	        }
        	unset($c);
        }
        return true;
    }
}