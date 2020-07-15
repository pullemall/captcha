<?php
require_once "getLine.php";

define("STATES", "states.json");
define("IMG_NAME", "123.png");
define("IMG_LINE", "line.png");
define("IMG_LINE_INNER", "line_inner.png");

class ImgLine{
    private $img_name;
    private $black_pixels;
    private $spaces; 
    private $img_height;
    private $sensitivity;

    public function __construct($img_name, $black_pixels, $spaces, $img_height, $sensitivity=150)
    {
        $this->img_name = $img_name;
        $this->black_pixels = $black_pixels;
        $this->spaces = $spaces;
        $this->img_height = $img_height;
        $this->sensitivity = $sensitivity;
    }

    public function writeStates($filename, $value){
        $fd = json_encode($value);
        $fp = fopen($filename, "w");
        fwrite($fp, $fd);
        fclose($fp);
    }
    
    public function getImage(){
        $fd = file_get_contents(STATES);
        $coords = json_decode($fd, true);
        $img = $this->img_name == IMG_LINE_INNER ? @imagecreatefrompng(IMG_LINE_INNER) : @imagecreatefrompng(IMG_LINE);
        
        if(!$img && $this->img_name == IMG_LINE){
            $img = getLine(IMG_NAME, 7, 1);
        }elseif(!$img && $this->img_name == IMG_LINE_INNER){
            $img = getLine(IMG_LINE, 7, 1, 10);
        }
    
        $imgx = imagesx($img);
        $imgy = imagesy($img);
    
        if($imgy < $this->img_height){
            for($x = $coords["x2"]; $x < $imgx; $x++){
                $colors_arr = array();
    
                if($x == $imgx-1){
                    $x = 0;
                    $coords["x1"] = 0;
                    $coords["x2"] = 0;
                    $fd = file_get_contents(STATE_LINE_INNER);
                    $coords_y = json_decode($fd, true);
                    $img = imagecreatefrompng(IMG_LINE);
    
                    if($this->img_name == IMG_LINE_INNER && $coords_y["y2"] == imagesy($img)-1){
                        $coords_y["y1"] = 0;
                        $coords_y["y2"] = 0;
                        $this->writeStates(STATE_LINE_INNER, $coords_y);
                        $this->img_name = IMG_LINE;
                        unlink(IMG_LINE_INNER);
                    }
    
                    $img = $this->img_name == IMG_LINE ? getLine(IMG_NAME, 7, 1) : getLine(IMG_LINE, 7, 1, 10);
                    $imgx = imagesx($img);
                    $imgy = imagesy($img);
                    $this->writeStates(STATES, $coords);
    
                    if($imgy > $this->img_height){
                        $this->getImage(IMG_LINE_INNER, 5, 7, 70);
                    }
                }
                for($y = 0; $y < $imgy; $y++){
            
                    $rgb = imagecolorat($img, $x, $y);
                    $colors = imagecolorsforindex($img, $rgb);
                    array_pop($colors);
            
                    if(array_sum($colors) < $this->sensitivity){
                        $colors_arr[] = 1;
                    }else{
                        $colors_arr[] = 0;
                    }
            
                }
            
                if(array_sum($colors_arr) > $this->black_pixels){
                    $coords["x1"] = $x-3;
                    $count_spaces = 0;
            
                    for($j = $x; $j < $imgx; $j++){
                        $colors_arr = array();
                        for($l = 0; $l < $imgy; $l++){
                            $rgb = imagecolorat($img, $j, $l);
                            $colors = imagecolorsforindex($img, $rgb);
                            array_pop($colors);
            
                            if(array_sum($colors) < $this->sensitivity){
                                $colors_arr[] = 1;
                            }else{
                                $colors_arr[] = 0;
                            }
                        }
            
                        if(array_sum($colors_arr) < 1){
                            $count_spaces++;
                            if($count_spaces > $this->spaces) {
                                $coords["x2"] = $j-3;
            
                                $img = imagecrop($img, [
                                    'x' => $coords["x1"], 
                                    'y' => 0, 
                                    'width'=> $coords["x2"]-$coords["x1"], 
                                    'height' => $imgy
                                    ]);
                                    
                                $this->writeStates(STATES, $coords);
                                
                                imagepng($img);
                                imagedestroy($img);
                                return 0;
                            }
                        }else{
                            $count_spaces = 0;
                        }
                    }
                }
            }
        }else{
            $this->getImage(IMG_LINE_INNER, 5, 7, 70);
        }
    }
}


header("Content-Type: image/png");

$word = new ImgLine(IMG_LINE, 5, 7, 70);
$word->getImage();
?>