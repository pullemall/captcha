<?php
spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});

class ImgWord extends ImgLine {
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
    
    public function getImage(){
        $fd = file_get_contents(self::STATES);
        $coords = json_decode($fd, true);
        $img = $this->img_name == self::IMG_LINE_INNER ? @imagecreatefrompng(self::IMG_LINE_INNER) : @imagecreatefrompng(self::IMG_LINE);
        
        if(!$img && $this->img_name == self::IMG_LINE){
            $img = $this->getLine(self::IMG_NAME, 7, 1);
        }elseif(!$img && $this->img_name == self::IMG_LINE_INNER){
            $img = $this->getLine(self::IMG_LINE, 7, 1, 10);
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
                    $fd = file_get_contents(self::STATE_LINE_INNER);
                    $coords_y = json_decode($fd, true);
                    $img = imagecreatefrompng(self::IMG_LINE);
    
                    if($this->img_name == self::IMG_LINE_INNER && $coords_y["y2"] == imagesy($img)-1){
                        $coords_y["y1"] = 0;
                        $coords_y["y2"] = 0;
                        $this->writeStates(self::STATE_LINE_INNER, $coords_y);
                        $this->img_name = self::IMG_LINE;
                        unlink(self::IMG_LINE_INNER);
                    }
    
                    $img = $this->img_name == self::IMG_LINE ? $this->getLine(self::IMG_NAME, 7, 1) : $this->getLine(self::IMG_LINE, 7, 1, 10);
                    $imgx = imagesx($img);
                    $imgy = imagesy($img);
                    $this->writeStates(self::STATES, $coords);
    
                    if($imgy > $this->img_height){
                        $this->getImage(self::IMG_LINE_INNER, 5, 7, 70);
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
                                    
                                $this->writeStates(self::STATES, $coords);
                                
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
            $this->getImage(self::IMG_LINE_INNER, 5, 7, 70);
        }
    }
}

header("Content-Type: image/png");

$word = new ImgWord("line.png", 5, 7, 70);
$word->getImage();
