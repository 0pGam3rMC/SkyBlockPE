<?php
namespace MyPlot;

use pocketmine\level\generator\Generator;
use pocketmine\level\Level;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\utils\Random;

class MyPlot_Generator extends Generator{
    private $level, $settings, $columnPlot, $airChunk, $fillChunk;

    public function __construct(array $settings = array()){
        if(empty($settings)){
            $this->settings = array(
                'PlotSize' => 20,
                'RoadWidth' => 7,
                'Height' => 64,
                'PlotFloorBlockId' => [2,0], // grass
                'PlotFillingBlockId' => [3,0], // dirt
                'RoadBlockId' => [5,0], // wooden planks
                'WallBlockId' => [44,0], // stone slab
                'BottomBlockId' => [7,0] // bedrock
            );
        }else{
            $this->settings = $settings;
        }

        $defaultColumn = chr($this->settings['BottomBlockId'][0]);
        $defaultColumn .= str_repeat(chr($this->settings['PlotFillingBlockId'][0]), $this->settings['Height'] - 1);
        $defaultColumn .= str_repeat(chr(0), 128 - $this->settings['Height']);

        $defaultColumnMeta = substr(dechex($this->settings['BottomBlockId'][1]), -1);
        $defaultColumnMeta .= str_repeat(substr(dechex($this->settings['PlotFillingBlockId'][1]), -1), $this->settings['Height'] - 1);
        $defaultColumnMeta .= str_repeat('0', 128 - $this->settings['Height']);


        $this->columnPlot = $defaultColumn;
        $meta = $defaultColumnMeta;
        $this->columnPlot[$this->settings['Height']-1] = chr($this->settings['PlotFloorBlockId'][0]);
        $meta[$this->settings['Height']-1] = substr(dechex($this->settings['PlotFloorBlockId'][1]), -1);
        $this->columnPlot .= hex2bin($meta);

        $this->columnRoad = $defaultColumn;
        $meta = $defaultColumnMeta;
        $this->columnRoad[$this->settings['Height']-1] = chr($this->settings['RoadBlockId'][0]);
        $meta[$this->settings['Height']-1] = substr(dechex($this->settings['RoadBlockId'][1]), -1);
        $this->columnRoad .= hex2bin($meta);

        $this->columnWall = $defaultColumn;
        $meta = $defaultColumnMeta;
        $this->columnWall[$this->settings['Height']-1] = chr($this->settings['RoadBlockId'][0]);
        $this->columnWall[$this->settings['Height']] = chr($this->settings['WallBlockId'][0]);
        $meta[$this->settings['Height']-1] = substr(dechex($this->settings['RoadBlockId'][1]), -1);
        $meta[$this->settings['Height']] = substr(dechex($this->settings['WallBlockId'][1]), -1);
        $this->columnWall .= hex2bin($meta);

        $this->airChunk = str_repeat("\x00", 8192);
        $this->fillChunk = str_repeat(chr($this->settings['PlotFillingBlockId'][0]), 16).hex2bin(str_repeat(substr(dechex($this->settings['PlotFillingBlockId'][1]), -1), 16))."\x00\x00\x00\x00\x00\x00\x00\x00";
        $this->fillChunk = str_repeat($this->fillChunk, 256);
    }

    public function getName(){
        return 'MyPlot_Generator';
    }

    public function getSettings(){
        return $this->settings;
    }

    public function init(Level $level, Random $random){
        $this->level = $level;
    }

    public function generateChunk($chunkX, $chunkZ){
        $shape = $this->getShape($chunkX*16, $chunkZ*16);

        for($chunkY = 0; $chunkY < 8; ++$chunkY){
            $chunk = '';
            $startY = $chunkY << 4;
            $endY = $startY + 16;
            if($endY < ($this->settings['Height']-1) and $endY !== 0){
                $this->level->setMiniChunk($chunkX, $chunkZ, $chunkY, $this->fillChunk);
                continue;
            }elseif($startY > $this->settings['Height']){
                $this->level->setMiniChunk($chunkX, $chunkZ, $chunkY, $this->airChunk);
                continue;
            }
            for($z = 0; $z < 16; ++$z){
                for($x = 0; $x < 16; ++$x){
                    if($shape[$z][$x] === 0){
                        $chunk .= substr($this->columnPlot, $startY, 16);
                        $chunk .= substr($this->columnPlot, ($startY/2)+127, 8);
                        $chunk .= "\x00\x00\x00\x00\x00\x00\x00\x00";
                    }elseif($shape[$z][$x] === 1){
                        $chunk .= substr($this->columnRoad, $startY, 16);
                        $chunk .= substr($this->columnRoad, ($startY/2)+127, 8);
                        $chunk .= "\x00\x00\x00\x00\x00\x00\x00\x00";
                    }else{
                        $chunk .= substr($this->columnWall, $startY, 16);
                        $chunk .= substr($this->columnWall, ($startY/2)+127, 8);
                        $chunk .= "\x00\x00\x00\x00\x00\x00\x00\x00";
                    }
                }
            }
            $this->level->setMiniChunk($chunkX, $chunkZ, $chunkY, $chunk);
        }
    }

    public function getShape($x, $z){
        $plotSize = $this->settings['PlotSize'];
        $roadWidth = $this->settings['RoadWidth'];
        $totalSize = $plotSize + $roadWidth;

        if($x >= 0){
            $X = $x % $totalSize;
        }else{
            $X = $totalSize - abs($x % $totalSize);
        }

        if($z >= 0){
            $Z = $z % $totalSize;
        }else{
            $Z = $totalSize - abs($z % $totalSize);
        }

        $startX = $X;

        $shape = array();

        for($z=0; $z<16; $z++, $Z++){

            if($Z === $totalSize)
                $Z = 0;
            if($Z < $plotSize){
                $typeZ = 0; // plot
            }elseif($Z === $plotSize or $Z === ($totalSize-1)){
                $typeZ = 2; // wall
            }else{
                $typeZ = 1; // road
            }

            for($x=0, $X=$startX; $x<16; $x++, $X++){

                if($X === $totalSize)
                    $X = 0;
                if($X < $plotSize){
                    $typeX = 0; // plot
                }elseif($X === $plotSize or $X === ($totalSize-1)){
                    $typeX = 2; // wall
                }else{
                    $typeX = 1; // road
                }

                if($typeX === $typeZ){
                    $type = $typeX;
                }elseif($typeX === 0){
                    $type = $typeZ;
                }elseif($typeZ === 0){
                    $type = $typeX;
                }else{
                    $type = 1;
                }
                $shape[$z][$x] = $type;
            }
        }
        return $shape;
    }

    public function populateChunk($chunkX, $chunkZ){

    }

    public function getSpawn(){
        return new Vector3(127, $this->settings['Height']-1, 127);
    }
}