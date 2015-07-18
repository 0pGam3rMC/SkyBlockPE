<?php
namespace MyPlot\subcommand;

use MyPlot\MyPlot;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class GenerateSubCommand implements SubCommand
{
    private $plugin;

    public function __construct(MyPlot $plugin) {
        $this->plugin = $plugin;
    }

    public function canUse(CommandSender $sender) {
        return $sender->hasPermission("myplot.command.generate");
    }

    public function getUsage() {
        return "<name>";
    }

    public function getName() {
        return "generate";
    }

    public function getDescription() {
        return "Generate a new plot world";
    }

    public function getAliases() {
        return ["gen"];
    }

    public function execute(CommandSender $sender, array $args) {
        if (count($args) !== 1) {
            return false;
        }
        $levelName = $args[0];
        if ($sender->getServer()->isLevelGenerated($levelName)) {
            $sender->sendMessage(TextFormat::RED . "A world with that name already exists");
            return true;
        }
        if ($this->plugin->generateLevel($levelName)) {
            $sender->sendMessage(TextFormat::GREEN . "Successfully generated a new plot world: " . $levelName);
        } else {
            $sender->sendMessage(TextFormat::RED . "The world could not be generated");
        }
        return true;
    }
}