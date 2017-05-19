<?php
	/*
	 * Musicbar script
	 * Author: Paweł Dąbrowski <dabrowskip9@gmail.com>
	 * First version ~2012 (?)
	 * No caching version - only displays current song in a pretty way
	 * It's not very good for you server, so please do caching
	 * MIT license
	 */

	header("Content-type: image/png");
	error_reporting(0);
	//error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
	require 'config.php';
	require 'functions.php';
	require 'classes/lastfmapi.class.php';

	try {
		$username = $_GET['user']; // last.fm username
		$color = $_GET['color']; // musicbar color - check musicbars folder 
		$unicode = $_GET['uc'];	// yes/no, font 

		$getNowListening = LastFmApi::getRecentTracks($username, 0, 0, 1, 1);
		$song = $getNowListening->recenttracks->track;
		if(is_array($song)) {
			$song = $song[0];
		}
		$nowplay = $song->artist->{'#text'}." - ".$song->name;
		$nowplay = iconv(mb_detect_encoding($nowplay), "utf-8", $nowplay);

		$im = imagecreatefrompng('musicbars/musicbar_'.$color.'.png');
		$fontcol = imagecolorallocate($im, 255, 255, 255);
		$uni = 'fonts/unicode.ttf';
		$normal = 'fonts/Lato-Reg.ttf';
		imageantialias($im, true);

		switch($unicode) {
			case 'yes': $font = $uni; break;
			case 'no': $font = $normal; break;
		}

		$bbox1 = imagettfbbox(10.85, 0, $font, $username);
		$userlen = $bbox1[2] - $bbox1[0];
		
		$bbox2 = imagettfbbox(10.85, 0, $font, $nowplay);
		$songlen = $bbox2[2] - $bbox2[0];
		
		$start = 41+$userlen+5;
		$marg = 349;
		
		$space = $marg - $start;
		
		if ($songlen > $space) {
			if ($songlen > 310) {
				while ($songlen > 310) {
					$nowplay = substr($nowplay, 0, -1);
					$bbox2 = imagettfbbox(10.85, 0, $font, $nowplay);
					$songlen = $bbox2[2] - $bbox2[0];
				}
				$nowplay = substr($nowplay, 0, -2);
				$nowplay .= "...";
				$bbox2 = imagettfbbox(10.85, 0, $font, $nowplay);
				$songlen = $bbox2[2] - $bbox2[0];
				
				$pos = $marg - $songlen;
				imagettftext($im, 10.85, 0, $pos, 19, $fontcol, $font, $nowplay);
			}
			else {
				$pos = $marg - $songlen;
				imagettftext($im, 10.85, 0, $pos, 19, $fontcol, $font, $nowplay);
			}
		}
		else {
			$pos = $marg - $songlen;
			imagettftext($im, 10.85, 0, 41, 19, $fontcol, $normal, $username);
			imagettftext($im, 10.85, 0, $pos, 19, $fontcol, $font, $nowplay);
		}

		imagealphablending($im, false);
		imagesavealpha($im, true);
		imagepng($im);
		imagedestroy($im);
	}
	catch(Exception $e) {
		echo $e->getMessage();
	}
?>