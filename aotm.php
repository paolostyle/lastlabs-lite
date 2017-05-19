<?php
	/*
	 * Artist of the Month script
	 * Author: Paweł Dąbrowski <dabrowskip9@gmail.com>
	 * First version ~2013, some cleaning up in 2017
	 * It's relatively reusable version of the original script
	 * No premium mode
	 * MIT license
	 */

	set_time_limit(0);
	error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE); //only for debbuging stuff
	date_default_timezone_set('Europe/Warsaw');

	require 'config.php';
	require 'functions.php';
	require 'classes/image.class.php';
	require 'classes/lastfmapi.class.php';

	try {
		//PARAMETERS
		$username = ''; // last.fm username
		$wantlogo = 'off'; // 'on'/'off'
		$wantphoto = 'on'; // 'on'/'off'
		$textcolor = '000000'; // hex triplet without # sign, text color on the image
		$bgcolor = 'ffffff'; // bg color, look above

		//MODE GENERATING:
		//based on logo and photo options $mode: 3 => both; 2 => no photo; 1 => no logo; 0 => nothing
		$wantlogo != "off" ? $logob = "1" : $logob = "0";
		$wantphoto != "off" ? $photob = "1" : $photob = "0";
		$mode = bindec($logob.$photob); 

		//DATE OPTIONS
		$daymonths = array(1 => 31, 2 => 28, 3 => 31, 4 => 30, 5 => 31, 6 => 30, 7 => 31, 8 => 31, 9 => 30, 10 => 31, 11 => 30, 12 => 31);
		date("L") == 1 ? $daymonths[2] = 29 : $daymonths[2] = 28; //if leap year, February should have 29 days

		$lastMonth = date("n")-1; //number of last month, used for generating AotM
		$year = date("Y"); //current year
		if (date("n") == 1) { //if January...
			$lastMonth = 12; //...last month should be December...
			$year = $year-1; //and year number should be -1
		}
		$from = gmmktime(0, 0, 0, $lastMonth, 1, $year); //1st day of counting
		$to = gmmktime(23, 59, 59, $lastMonth, $daymonths[$lastMonth], $year); //last day of counting
		$fullName = date("F", $from); //full name in English, e.g. 9 => September

		//DATA AGGREGATION
		$getRecentTracks = LastFmApi::getRecentTracks($username, $from, $to); //return object based on json response from API
		$pages = $getRecentTracks->recenttracks->{'@attr'}->totalPages;
		$total = $getRecentTracks->recenttracks->{'@attr'}->total;

		for ($i=1; $i <= $pages; $i++) {
			if ($i >= 2) { //already loaded first page, so refresh when the last page is called
				$getRecentTracks = LastFmApi::getRecentTracks($username, $from, $to, $i);
			}
			for ($j=0; $j<200; $j++) {
				if($j == ($total - ($pages-1)*200) && $i == $pages) { //prevents non-object reference errors
					break 2;
				}
				else {
					$artist[] = $getRecentTracks->recenttracks->track[$j]->artist->{'#text'};
				}
			}
		}

		$rank = array_count_values($artist); //does whole work
		arsort($rank); //sorts from highest one
		$aotm = key($rank); //and here we have what we wanted
		$myArtist = "My Artist of $fullName is";

		/*
		 * IMPORTANT: you need to provide fonts and logos by yourself
		 * Just put them to fonts directory, you can of course add as many as you want
		 * for specific alphabets, here it only detects Japanese signs.
		 * As for logos, edit line 97 accordingly to logos filenames.
		 * Also keep in mind that if they're too big you may have to resize them
		 * or just change the size of output image.
		 */
		if (isJapanese($aotm) == 0) 
			$artistFont = 'fonts/aotm.ttf';
		else 
			$artistFont = 'fonts/japanese.ttf';

		$imageOptions = array(
			'im_width' => 330,
			'margin' => 20,		// top margin, margin/2 = side margin
			'text_color' => $textcolor,
			'bg_color' => $bgcolor
		);

		if ($mode%2 == 1) { //if $m = 3 or 1, get artist photo
			$getInfo = LastFmApi::artistGetInfo($aotm);
			$photo = new Image($getInfo->artist->image[3]->{'#text'});
			$photoHeight = $photo->getHeight();
		}

		if ($mode > 1) { //generates logo or if image not present, prepares fontsize to insert AotM name
			$path = glob('logos/'.strtolower(str_replace("%", "_", urlencode($aotm))).'.*');
			if (!empty($path) && $path !== false) {
				$logo = new Image($path[0]);
				$logoHeight = $logo->getHeight();
			}
			else {
				$logoHeight = Image::setProperFontSize($aotm, 32, $artistFont, $imageOptions['im_width'] - $imageOptions['margin']);
				$fontsize = $logoHeight;
			}
		}
		else {
			$logoHeight = Image::setProperFontSize($aotm, 32, $artistFont, $imageOptions['im_width'] - $imageOptions['margin']);
			$fontsize = $logoHeight;
		}

		$imageOptions['im_height'] = 80 + $logoHeight + (isset($photoHeight) ? $photoHeight : 0);

		$image = new Image($imageOptions);
		//inserts My artist of ... is
		$image->insertCenterText($myArtist, 18, 'fonts/aotm.ttf');

		//depends on options inserts logo
		if (isset($logo)) $image->insertImage($logo);
		else $image->insertCenterText($aotm, $fontsize, $artistFont);
		//inserts (or not) photo
		if (isset($photo)) $image->insertImage($photo);

		// send to imgur and output
		$link = $image->sendImage();
		echo $link;
	}
	catch(Exception $e) {
		echo $e->getMessage();
	}
?>