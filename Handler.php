<?php
spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});

class Handler extends ImgLine {
    public function __construct() {
        $this->users_input = mb_split("\s", htmlentities($_POST['code']));
        $this->fd = file_get_contents("states.json");
        $this->coords = json_decode($this->fd, true);
        $this->fd = file_get_contents("state_line.json");
        $this->coords_y = json_decode($this->fd, true);

        if(@imagecreatefrompng("line_inner.png")){
            $fd = file_get_contents("state_line_inner.json");
            $coords_y_inner = json_decode($fd, true);
            $y1 = $this->coords_y["y1"] + $coords_y_inner["y1"];
            $y2 = $this->coords_y["y1"] + $coords_y_inner["y2"];
        }else{
            $y1 = $this->coords_y["y1"];
            $y2 = $this->coords_y["y2"];
        }
        $this->fd = file_get_contents("words.json");
        $this->word = json_decode($this->fd, true);
        $this->hash = md5($this->coords["x1"] . $y1 . ($this->coords['x2']-$this->coords['x1']) . ($y2 - $y1));
    }

    public function levenshteinCheck($users_input, $words){
        $lev_count = 0;
        $tmp = count($words) > 9 ? 10 : count($words);
        
        for($i = 0; $i < $tmp; $i++){
            if(levenshtein($users_input, $words[array_rand($words)]) <= 2){
                $lev_count++;
            }
        }
        
        if($lev_count >= 5 || ($tmp < 5 && $lev_count >= 1)){
            return true;
        }else{
            return false;
        }
    }

    public function checkCode($users_input, $word, $hash){
        $cap = $_SESSION["captcha"] ?? '';
    
        if($users_input[0] == $cap && mb_strlen($users_input[1]) > 0){
            if(isset($word[$hash])){
                $words = $word[$hash];
                if($this->levenshteinCheck($users_input[1], $words)){
                    $words[] = $users_input[1];
                    $word[$hash] = $words;
                }else{
                    unset($_SESSION['captcha']);
                    return false;
                }
                
            }else{
                $word[$hash] = array($users_input[1]);
            }
    
            $this->writeStates("words.json", $word);
    
            unset($_SESSION['captcha']);
            return true;
        }else{
            unset($_SESSION['captcha']);
            return false;
        }
    }

    public function confirmCode(){
        if (isset($_POST['code']))
        {
            if ($_POST['code'] == '')
            {
                exit("Input field is empty");
            }
            if ($this->checkCode($this->users_input, $this->word, $this->hash))
            {
                echo "Done!"; 
            }else{
                exit("Failed!");
            }
        }else{
            exit("Access denied");
        }
    }
}

session_start();
$handler = new Handler;
$handler->confirmCode();
