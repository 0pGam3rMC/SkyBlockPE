<?php
namespace MyPlot\subcommand;

use MyPlot\MyPlot;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class AddHelperSubCommand implements SubCommand
{
    private $plugin;

    public function __construct(MyPlot $plugin) {
        $this->plugin = $plugin;
    }

    public function canUse(CommandSender $sender) {
        return ($sender instanceof Player);
    }

    public function getUsage() {
        return "<player>";
    }

    public function getName() {
        return "addhelper";
    }

    public function getDescription() {
        return "Add a helper to your plot";
    }

    public function getAliases() {
        return ["addh"];
    }

    public function execute(CommandSender $sender, array $args) {
        if (count($args) !== 1) {
            return false;
        }
        $helper = $args[0];
        $player = $sender->getServer()->getPlayer($sender->getName());
        $plot = $this->plugin->getPlotByPosition($player->getPosition());
        if ($plot === null) {
            $sender->sendMessage(TextFormat::RED . "You are not standing inside a plot");
            return true;
        }
        if ($plot->owner !== $sender->getName()) {
            $sender->sendMessage(TextFormat::RED . "You are not the owner of this plot");
            return true;
        }
        if (!$plot->addHelper($helper)) {
            $sender->sendMessage($helper . " was already a helper of this plot");
            return true;
        }
        var_dump($plot);
        if ($this->plugin->getProvider()->savePlot($plot)) {
            $sender->sendMessage(TextFormat::GREEN . $helper . " is now a helper of this plot");
        } else {
            $sender->sendMessage(TextFormat::RED . "Helper could not be added");
        }
        return true;
    }
}