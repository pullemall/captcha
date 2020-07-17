<?php
class Captcha {
	public function createWord() {
		$w_arr1 = explode(" ", "Ва Хли шо Пы на мю ры Бра глу Ту гра пы стри Ба хра");
		$w_arr2 = explode(" ", "рка вки рьки ря ве мзи мит нда що тум хнул лка жа раб бро");
		$w_arr3 = explode(" ", "лни те ю щих им пе ра ме ны ле е ки бу ет");
		$w_arr4 = explode(" ", "вля щу ю со ре ви ща пыл ста ми ка ри");
		$w_arr5 = explode(" ", "лось лись ки шмыг бу ет нный ка ов ость");
		$c = rand(0, 1);
		$word = $w_arr1[array_rand($w_arr1)] . $w_arr2[array_rand($w_arr2)] . $w_arr3[array_rand($w_arr3)] . $w_arr4[array_rand($w_arr4)];

		if($c == 1){
			$word .= $w_arr5[array_rand($w_arr5)];
		}

		session_start();
		$_SESSION['captcha'] = $word;
		session_write_close();

		return $word;
	}

	public function createCaptcha() {
		$code = $this->createWord();
		$font = $_SERVER["DOCUMENT_ROOT"] . "/fonts/georgia.ttf";
		$font_size = 20;
		$img = imagecreate($font_size*mb_strlen($code), 30);
		$imgx = imagesx($img);
		$imgy = imagesy($img);
		imagecolorallocate($img, 255, 255, 255);
		$x = 10;		

		imagettftext($img, $font_size, 0, $x, 20, imagecolorallocate($img, 0, 0, 0), $font, $code);	

		$black = imagecolorallocate($img, 0, 0, 0);
		$white = imagecolorallocate($img, 253, 253, 253);

		for($y = 0; $y < $imgy; $y++){
			for($x = 0; $x < $imgx; $x++){
				$rgb = imagecolorat($img, $x, $y);
				$colors = imagecolorsforindex($img, $rgb);
				array_pop($colors);

				if(array_sum($colors) < 220){
					imagesetpixel($img, $x, $y, $black);
				}else{
					imagesetpixel($img, $x, $y, $white);
				}
			}
		}

		$img = imagescale($img, $imgx * 1.5, $imgy * 1.5);

		imagepng($img);
		imagedestroy($img);
	}
}
header("Content-Type: image/jpeg");

$captcha = new Captcha();
$captcha->createCaptcha();