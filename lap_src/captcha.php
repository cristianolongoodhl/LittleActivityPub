<?php 
/*
 * Generate an image containing a captcha, see https://www.html.it/pag/68362/realizzare-un-sistema-captcha-con-php/
 * The captcha value as string is stored in the 'captcha' session variable.
 * 
 * @author Cristiano Longo
 */

/**
 * Generate a random character 0-9  string 
 * 
 * see https://www.php.net/manual/en/function.rand.php#96583, https://www.php.net/manual/en/function.imagestring.php
 * 
 * @param int $stringSize
 * @return string random string with stringSize chars in A-Z0-9 
 */
function generateRandomNumber(int $stringSize){
	$randomString = ''; 
	for ($i=0; $i<$stringSize; $i++) {
		$d=rand(1,30)%2;
		//$randomString.=$d ? chr(rand(65,90)) : chr(rand(48,57));
		$randomString.=chr(rand(48,57));
	}
	return $randomString;
}
session_start();
$stringSize=4;
$stringToGuess=generateRandomNumber($stringSize);
$_SESSION['captcha']=$stringToGuess;
$image = imagecreatetruecolor(16+10*$stringSize, 30);
$backgroundColor = imagecolorallocate($image, 200, 200, 200);
$textColor = imagecolorallocate($image, 0, 0, 0);
imagefill($image, 0, 0, $backgroundColor);
imagestring($image, 5, 8, 8, $stringToGuess, $textColor);

// Output the image
header('Content-type: image/png');

imagepng($image);
imagedestroy($image);
?>