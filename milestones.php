<?php
	/*
	 * Milestones
	 * Author: Paweł Dąbrowski <dabrowskip9@gmail.com>
	 * First version ~2011, some cleaning up in 2017
	 * Truncated version of the original, generates only "graphic" mode
	 * and uploads it to Imgur
	 * MIT license
	 */

	set_time_limit(0);
	error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE); //only for debbuging stuff
	//error_reporting(0);

	require 'config.php';
	require 'functions.php';
	require 'classes/image.class.php';
	require 'classes/lastfmapi.class.php';

	try {
		$username = ''; // lastfm username
		$method = 'ten-thous'; //which plays should the script fetch - check lines 46-
		$textcolor = '000000'; // hex triplet without # sign, text color on the image
		$bgcolor = 'ffffff'; // bg color, look above
		$title = 'Milestones'; // title shown at the top
		$imagedisplay = 'album'; // album or none - shows album cover if exists or no
		$unicode = 'no'; // yes or no - unicode font
		$customnum = array(); // custom numbers to add as array of integers
		$unwantednum = array();  //remove existing numbers as array of integers


		$getInfo = LastFmApi::getUserInfo($username);
		$totalPlaycount = (int) $getInfo->user->playcount;
		$getRecentTracks = LastFmApi::getRecentTracks($username, 0, 0, 1, 1);
		$playcount = (int) $getRecentTracks->recenttracks->{'@attr'}->total;

		// Check if playcount is equal to number of songs listened to in Last.fm database
		// After all the stuff Last.fm did with their databases it might as well trigger every time
		// You should check it, but do it with proper error handling
		/*
		if ($totalPlaycount != $playcount) {
			echo "Unfortunately, an error ocurred while comparing playcount number and number of songs, which their data are stored in your Last.fm Library. The most probable reason of this situation was importing songs to Last.fm, which were played before setting up an account. The other reason is listening music while using Milestones - this also may cause this message. However, we have no influence on it and Milestones are counted from the first song in database, so it may be not accurate. Sorry.";
		}
		*/

		//METHODS
		switch ($method) {
			case 'each-thous': // 1, 1000, 2000, 3000...
				$dividedPlaycount = $playcount / 1000; 
				$l = ceil($dividedPlaycount);

				for ($i=0; $i < $l; $i++) {
					$recordsRaw[$i] = $i*1000;
				}
				$recordsRaw[0] = 1;
				
				$recordsCustom = array_unique(array_merge($recordsRaw, $customnum));
				sort($recordsCustom);
				$records = array_reverse(array_diff($recordsCustom, $unwantednum));
				$r = count($records);
				$rc = count($recordsCustom);
				$next_playcount = $recordsCustom[$rc-1] + 1000;
			break;
			case 'five-thous': // 1, 5000, 10000, 15000...
				$dividedPlaycount = $playcount / 5000;
				$l = ceil($dividedPlaycount);

				for ($i=0; $i < $l; $i++) {
					$recordsRaw[$i] = $i*5000;
				}
				$recordsRaw[0] = 1;
				
				$recordsCustom = array_unique(array_merge($recordsRaw, $customnum));
				sort($recordsCustom);
				$records = array_reverse(array_diff($recordsCustom, $unwantednum));
				$r = count($records);
				$rc = count($recordsCustom);
				$next_playcount = $recordsCustom[$rc-1] + 5000;
			break;
			case 'ten-thous': // 1, 10000, 20000, 30000
				$dividedPlaycount = $playcount / 10000;
				$l = ceil($dividedPlaycount);

				for ($i=0; $i < $l; $i++) {
					$recordsRaw[$i] = $i*10000;
				}
				$recordsRaw[0] = 1;
				
				$recordsCustom = array_unique(array_merge($recordsRaw, $customnum));
				sort($recordsCustom);
				$records = array_reverse(array_diff($recordsCustom, $unwantednum));
				$r = count($records);
				$rc = count($recordsCustom);
				$next_playcount = $recordsCustom[$rc-1] + 10000;
			break;
			case 'add-zero': // 1, 10, 100, 1000, 10000, 100000...
				$l = strlen($playcount);
				$v = 1;
				
				for ($i=0; $i < $l; $i++) {
					$recordsRaw[$i] = $v;
					$v .= "0";
				}
				
				$recordsCustom = array_unique(array_merge($recordsRaw, $customnum));
				sort($recordsCustom);
				$records = array_reverse(array_diff($recordsCustom, $unwantednum));
				$r = count($records);
				$rc = count($recordsCustom);
				$next_playcount = (string) $recordsCustom[$rc-1]."0";
				$next_playcount = (int) $next_playcount;
			break;
			case 'custom': // only $customnum
				if(empty($customnum)) throw new Exception("You haven't entered any custom numbers!");
				sort($customnum);
				$records = array_reverse(array_unique(array_diff($customnum, $unwantednum)));
				$r = count($records);
				$next_playcount = closest($records, $playcount);
			break;
			case 'repeating-digits': // 1, 1111, 2222, 3333...9999, 11111, 22222...
				$multiplier = 111;
				for ($j=0; $j < 4; $j++) {
					$multiplier = $multiplier*10+1;
					
					$dividedPlaycount = $playcount / $multiplier;
					$l = ceil($dividedPlaycount);

					for ($i=1; $i < $l; $i++) {
						$recordsRaw[$i+($j*9)] = $i*$multiplier;
						if ($i == 9) {
							break;
						}
					}
				}

				$recordsRaw[0] = 1;
				$recordsCustom = array_unique(array_merge($recordsRaw, $customnum));
				sort($recordsCustom);
				$records = array_reverse(array_diff($recordsCustom, $unwantednum));
				$r = count($records);
				$rc = count($recordsCustom);
				
				switch($records[$r-1]) {
					case '1': $next_playcount = '1111'; break;
					case '9999': $next_playcount = '11111'; break;
					case '99999': $next_playcount = '111111'; break;
					default: 
						switch (floor(log10($records[$r-1]))+1) {
							case 4: $next_playcount = $recordsCustom[$rc-1] + 1111; break;
							case 5: $next_playcount = $recordsCustom[$rc-1] + 11111; break;
							case 6: $next_playcount = $recordsCustom[$rc-1] + 111111; break;
						}
				}
			break;
		}	

		// fetch songs from database
		for($i=0; $i < $r; $i++) {
			$p = $playcount-($records[$i]-1);
			try {
				$getRecentTracks = LastFmApi::getRecentTracks($username, 0, 0, $p, 1);
			}
			catch (Exception $e) {
				continue;
			}

			$song = $getRecentTracks->recenttracks->track;
			if(is_array($song)) $song = $song[0];
			$milestones[$i]['title'] = $song->name;
			$milestones[$i]['artist'] = $song->artist->{'#text'};
			$milestones[$i]['time'] = date('d M Y', $song->date->uts);
			if($imagedisplay == 'artist') {
				try {
					$getArtistInfo = LastFmApi::artistGetInfo($milestones[$i]['artist']);
				}
				catch (Exception $e) {
					echo $e->getMessage();
				}
				$milestones[$i]['image'] = $getArtistInfo->artist->image[3]->{'#text'};
			}
			elseif ($imagedisplay == 'album') {
				$milestones[$i]['image'] = changeCover($song->image[2]->{'#text'});
			}
		}

		$milestones = array_reverse($milestones);
		$records = array_reverse($records);
		$m = count($milestones);

		$coversTotal = 0;
		if ($unicode == 'yes') $font_song = 'fonts/unicode.ttf';
		else $font_song = 'fonts/Lato-Reg.ttf';

		if (!empty($title)) $title = cutString($title, 30);

		if($imagedisplay == 'album') {
			for ($i=0; $i < $m; $i++) {
				$str = substr($milestones[$i]['image'], 0, 4);
				if ($str == 'http') {
					$coversTotal += 1;
				}
			}
		}

		$image_options = array(
			'im_width' => 294,
			'im_height' => 38*$m+65 + (!empty($title) ? 19 : 0) + ($imagedisplay == 'album' ? 181*$coversTotal : 0),
			'margin' => 20,
			'text_color' => $textcolor,
			'bg_color' => $bgcolor,
		);
		
		$image = new Image($image_options);
		if (!empty($title)) $image->insertCenterText($title, 13, 'fonts/Lato-Bol.ttf');
		
		for($i=0; $i < $m; $i++) {
			$string1 = showOrdinal($records[$i])." track: "."(".$milestones[$i]['time'].")";
			$string2 = $milestones[$i]['artist']." - ".$milestones[$i]['title'];

			if($imagedisplay == 'album') $doesCoverExist = substr($milestones[$i]['image'], 0, 4);

			$string2 = cutString($string2, 50);
			
			$image->insertCenterText($string1, 9, 'fonts/Lato-Bol.ttf', 10);
			$image->insertCenterText($string2, 9, $font_song, 3);
			
			if ($imagedisplay == 'album' && $doesCoverExist == 'http') {
				$imageCover = new Image($milestones[$i]['image']);
				$image->insertImage($imageCover, 8);
			}
			else 
				$image->addMargin(7);
		}
		
		$string3 = "Generated ".date('d.m.Y');
		$string4 = "LastLabs Milestones";
		$image->insertCenterText($string3, 9, 'fonts/Lato-Bol.ttf', 8);
		$image->insertCenterText($string4, 9, 'fonts/Lato-Bol.ttf', 0);

		$link = $image->sendImage();
		echo $link;
	}
	catch (Exception $e) {
		echo $e->getMessage();
	}

?>