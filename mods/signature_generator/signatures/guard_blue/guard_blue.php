<?php
/**
 * @package EDK
 */

define('MFONT', dirname(__FILE__).'/evesansmm.ttf');
define('FSIZE', 12);

$im = imagecreatefromjpeg(dirname(__FILE__).'/base.jpg');

$red = imagecolorallocate($im, 255, 10, 10);
$orange = imagecolorallocate($im, 150, 120, 20);
$blue = imagecolorallocate($im, 0, 0, 200);
$white = imagecolorallocate($im, 255, 255, 255);
$black = imagecolorallocate($im, 0, 0, 0);

$grey_trans = imagecolorallocatealpha($im, 50, 50, 50, 50);
$grey_transblue = imagecolorallocatealpha($im, 50, 50, 110, 10);
$grey_transbluel = imagecolorallocatealpha($im, 50, 50, 110, 100);

$name = strtoupper($pilot->getName());

$list = new KillList();
$list->setOrdered(true);
$list->setPodsNoobships(false);
$list->addInvolvedPilot($pilot);
$kill = $list->getKill();
$list->getallKills();

imagefttext($im, FSIZE, 0, 80, 21, $grey_trans, MFONT, $name);
imagefttext($im, FSIZE, 0, 80, 20, $white, MFONT, $name, array('hdpi'=>200));

$no = $list->getCount();
$string = 'KILL# '.$no.'     TOTAL POINTS '.$list->getPoints();
imagettftext($im, FSIZE, 0, 80, 41, $grey_trans, MFONT, $string);
imagettftext($im, FSIZE, 0, 80, 40, $white, MFONT, $string);

$string = strtoupper($kill->getVictimName().' '.$kill->getVictimCorpName());
$box = imagettfbbox(FSIZE, 0, MFONT, $string);
$width = $box[4];
imagettftext($im, FSIZE, 0, 349-$width, 21, $grey_trans, MFONT, $string);
imagettftext($im, FSIZE, 0, 349-$width, 20, $white, MFONT, $string);

$string = strtoupper($kill->getVictimShipName());
$box = imagettfbbox(FSIZE, 0, MFONT, $string);
$width = $box[4];
imagettftext($im, FSIZE, 0, 349-$width, 41, $grey_trans, MFONT, $string);
imagettftext($im, FSIZE, 0, 349-$width, 40, $white, MFONT, $string);

$string = $kill->getSolarSystemName();
$box = imagettfbbox(FSIZE, 0, MFONT, $string);
$width = $box[4];
imagettftext($im, FSIZE, 0, 394-$width, 71, $grey_trans, MFONT, $string);
imagettftext($im, FSIZE, 0, 394-$width, 70, $white, MFONT, $string);

function bevel($x, $y, $size)
{
    global $im, $grey_transblue, $grey_transbluel,$red;
    imagefilledrectangle($im, $x+1, $y+$size-3, $x+$size-1, $y+$size, $grey_transbluel);
    imagefilledrectangle($im, $x+$size-3, $y+1, $x+$size, $y+$size-1, $grey_transbluel);
    imageline($im, $x+1, $y-1, $x+$size, $y-1, $grey_transbluel);
    imagerectangle($im, $x, $y, $x+$size, $y+$size, $grey_transblue);
}

// ship
$sid = $kill->getVictimShipExternalID();
$img = shipImage::get($sid);
imagecopyresampled($im, $img, 354, 6, 0, 0, 40, 40, 64, 64);

bevel(354, 6, 40);

// player portrait
$img = imagecreatefromjpeg(Pilot::getPortraitPath(256,$pid));
//imagefilledrectangle($im, 318, 18, 392, 92, $greyred_trans);
imagecopyresampled($im, $img, 6, 6, 0, 0, 63, 63, 256,256);
imagedestroy($img);

bevel(6, 6, 63);
?>