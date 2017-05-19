<?php
	/*
	 * Artist of the Month script
	 * Author: Paweł Dąbrowski <dabrowskip9@gmail.com>
	 * First version ~2013, some cleaning up in 2017
	 * No point using it, really, because Last.fm added Listening Report
	 * MIT license
	 */

	set_time_limit(0);
	error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE); //only for debbuging stuff
	require 'config.php';
	require 'functions.php';
	require 'classes/image.class.php';
	require 'classes/lastfmapi.class.php';

	try {
		$username = ''; // lastfm username
		$period = '7day'; // 7day or 3month
		$calculate = 'total'; // perday or total
		$textcolor = '000000'; // hex triplet without # sign, text color on the image
		$bgcolor = 'ffffff'; // bg color, look above

		//DATA AGGREGATION
		$getTopTracks = LastFmApi::getTopTracks($username, $period);
		switch ($period) {
			case '7day': $timestamp = time() - 7*24*3600; break;
			case '3month': $timestamp = time() - 91.31*24*3600; break;
		}
		$getRecentTracks = LastFmApi::getRecentTracks($username, $timestamp, 0, 1, 1);
		$playcount = $getRecentTracks->recenttracks->{'@attr'}->total;
		$pages = $getTopTracks->toptracks->{'@attr'}->totalPages;
		$total = $getTopTracks->toptracks->{'@attr'}->total;
		$listeningTime = 0;
		
		for ($i=1; $i <= $pages; $i++) {
			if ($i >= 2) {
				$getTopTracks = get_top_tracks($username, $period, $i);
			}
			for ($j=0; $j < 200; $j++) {
				if($j != ($total - ($pages-1)*200)) {
					$durat = $getTopTracks->toptracks->track[$j]->duration;
					$playc = $getTopTracks->toptracks->track[$j]->playcount;
					if (!preg_match('/^[0-9]{1,}$/', $durat)) {
						$durat = 200;
					}
					if ($durat > 3600) {
						$durat = 3600;
					}
					$product = $durat*$playc;
					$listeningTime += $product;
				}
				else break 2;
			}
		}
		
		//RESULT CALCULATING
		$string1 = "I spent";
		$string3 = "listening to music";
		$string4 = "during ".($period == '7day' ? 'last 7 days' : 'last 3 months');

		if ($calculate == 'perday') {
			$result = ($playcount / ((time() - $timestamp) / 60 / 60 / 24)) * ($listeningTime / $playcount);
			if ($result < 3600) {
				$minutes = round($result / 60);
				$result = $minutes." minute";
				$result .= $minutes == 1 ? '' : 's';
			}
			else {
				$result = round($result / 3600, 2);
				$result = explode(".", $result);
				$minutes = round((float)('0.'.$result[1])*60);
				$result = $result[0]."h ".$minutes." min";
			}
			$string2 = "$result per day";
		}
		elseif ($calculate == 'total') {
			$listeningTime = round($listeningTime / 3600, 2);
			$listeningTime = explode(".", $listeningTime);
			$minutes = round((float)('0.'.$listeningTime[1])*60);
			if($listeningTime[0] != 0) $result = $listeningTime[0]." hours ";
			$result .= $minutes." minute";
			$result .= $minutes == 1 ? '' : 's';
			$string2 = "$result";
		}
		
		//IMAGE CREATING
		$image_options = array(
			'im_width' => 294,
			'im_height' => 155,
			'margin' => 4,
			'text_color' => $textcolor,
			'bg_color' => $bgcolor,
		);

		$image = new Image($image_options);
		$image->insertCenterText($string1, 24, 'fonts/musictime.ttf');
		$image->insertCenterText($string2, 28, 'fonts/musictime.ttf');
		$image->insertCenterText($string3, 24, 'fonts/musictime.ttf');
		$image->insertCenterText($string4, 20, 'fonts/musictime.ttf');
		$link = $image->sendImage();

		echo $link;
	} 
	catch (Exception $e) {
		$e->getMessage();
	}
?>