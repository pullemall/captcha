<?php
session_start();

$users_input = mb_split("\s", htmlentities($_POST['code']));
$fd = file_get_contents("states.json");
$coords = json_decode($fd, true);
$fd = file_get_contents("state_line.json");
$coords_y = json_decode($fd, true);

if(@imagecreatefrompng("line_inner.png")){
    $fd = file_get_contents("state_line_inner.json");
    $coords_y_inner = json_decode($fd, true);
    $y1 = $coords_y["y1"] + $coords_y_inner["y1"];
    $y2 = $coords_y["y1"] + $coords_y_inner["y2"];
}else{
    $y1 = $coords_y["y1"];
    $y2 = $coords_y["y2"];
}
$fd = file_get_contents("words.json");
$word = json_decode($fd, true);
$hash = md5($coords["x1"] . $y1 . ($coords['x2']-$coords['x1']) . ($y2 - $y1));

function writeStates($filename, $value){
    $fd = json_encode($value);
    $fp = fopen($filename, "w");
    fwrite($fp, $fd);
    fclose($fp);
}

function levenshtein_check($users_input, $words){
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

function check_code($users_input, $word, $hash){
    $cap = $_SESSION["captcha"] ?? '';

	if($users_input[0] == $cap && mb_strlen($users_input[1]) > 0){
        if(isset($word[$hash])){
            $words = $word[$hash];
            if(levenshtein_check($users_input[1], $words)){
                $words[] = $users_input[1];
                $word[$hash] = $words;
            }else{
                unset($_SESSION['captcha']);
                return false;
            }
            
        }else{
            $word[$hash] = array($users_input[1]);
        }

        writeStates("words.json", $word);

        unset($_SESSION['captcha']);
        return true;
    }else{
        unset($_SESSION['captcha']);
        return false;
    }
}

if (isset($_POST['code']))
{
        if ($_POST['code'] == '')
        {
            exit("Input field is empty");
        }
        if (check_code($users_input, $word, $hash))
        {
            echo "Done!"; 
        }else{
            exit("Failed!");
        }
    }else{
    exit("Access denied");
}
	
?>