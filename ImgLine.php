<?php
class ImgLine {
    const STATE_LINE = "state_line.json";
    const STATE_LINE_INNER = "state_line_inner.json";
    const STATES = "states.json";
    const IMG_NAME = "123.png";
    const IMG_LINE = "line.png";
    const IMG_LINE_INNER = "line_inner.png";

    public function writeStates($filename, $value){
        $fd = json_encode($value);
        $fp = fopen($filename, "w");
        fwrite($fp, $fd);
        fclose($fp);
    }

    function getLine($img_name, $black_pixels, $spaces, $white_count=1){
        $img_line = imagecreatefrompng($img_name);
        $imgx = imagesx($img_line);
        $imgy = imagesy($img_line);
        $fd = $img_name == self::IMG_LINE ? file_get_contents(self::STATE_LINE_INNER) : file_get_contents(self::STATE_LINE);
        $coords = json_decode($fd, true);
    
        for($y = $coords["y2"]; $y < $imgy; $y++){
            $colors_arr = array();
            for($x = 0; $x < $imgx; $x++){
                $rgb = imagecolorat($img_line, $x, $y);
                $colors = imagecolorsforindex($img_line, $rgb);
                array_pop($colors);
    
                if(array_sum($colors) < 250){
                    $colors_arr[] = 1;
                }else{
                    $colors_arr[] = 0;
                }
            }
    
            if(array_sum($colors_arr) > $black_pixels){
                $coords["y1"] = $y-5 >= 0 ? $y-5 : 0;
                $count_spaces = 0;
    
                for($i = $y; $i < $imgy; $i++){
                    $colors_arr = array();
                    for($j = 0; $j < $imgx; $j++){
                        $rgb = imagecolorat($img_line, $j, $i);
                        $colors = imagecolorsforindex($img_line, $rgb);
                        array_pop($colors);
    
                        if(array_sum($colors) < 250){
                            $colors_arr[] = 1;
                        }else{
                            $colors_arr[] = 0;
                        }
                    }
    
                    if(array_sum($colors_arr) <= $white_count){
                        $count_spaces++;
    
                        if($count_spaces > $spaces || $i == $imgy-1) {
                            $coords["y2"] = $i;
                            if($img_name == self::IMG_LINE){
                                $this->writeStates(self::STATE_LINE_INNER, $coords);
                            }else{
                                $this->writeStates(self::STATE_LINE, $coords);
                            }
                            
                            $img_line = imagecrop($img_line, [
                                'x' => 0, 
                                'y' => $coords["y1"], 
                                'width'=> $imgx, 
                                'height' => $coords["y2"]-$coords["y1"]
                                ]);
                            
                            if($img_name == self::IMG_LINE){
                                imagepng($img_line, self::IMG_LINE_INNER);
                            }else{
                                imagepng($img_line, self::IMG_LINE);
                            }
                            
                            return $img_line;
                        }
                    }else{
                        $count_spaces = 0;
                    }
                }
            }
            if($y == $imgy-1){
                $y = 0;
                $coords["y1"] = 0;
                $coords["y2"] = 0;
                $this->writeStates(self::STATE_LINE, $coords);
            }
        }
    }

}