<?php
	class LastFmApi {
		static public function getRecentTracks($username, $from, $to, $page=1, $limit=200) {
			$response = json_decode(file_get_contents('http://ws.audioscrobbler.com/2.0/?method=user.getrecenttracks&format=json&extended=0&api_key='.LAST_FM_API.'&page='.$page.'&limit='.$limit.'&user='.urlencode($username).'&from='.$from.'&to='.$to));

			if ($response == false || empty($response)) throw new Exception("There are problems with connection to Last.fm database. Please try again later.");
			if (property_exists($response, 'error')) throw new Exception('Last.fm error '.$response->error.': '.$response->message.'.');
			if (!property_exists($response->recenttracks, "@attr")) throw new Exception("There is no such nick in Last.fm database. Please check your username.");
			
			return $response;
		}

		static public function getTopTracks($username, $period, $page=1, $limit=200) {
			$response = json_decode(file_get_contents('http://ws.audioscrobbler.com/2.0/?method=user.gettoptracks&format=json&period='.$period.'&api_key='.LAST_FM_API.'&page='.$page.'&limit='.$limit.'&user='.urlencode($username)));

			if ($response == false || empty($response)) throw new Exception("There are problems with connection to Last.fm database. Please try again later.");
			if (property_exists($response, 'error')) throw new Exception('Last.fm error '.$response->error.': '.$response->message.'.');
			
			return $response;
		}

		static public function getUserInfo($username) {
			$response = json_decode(file_get_contents('http://ws.audioscrobbler.com/2.0/?method=user.getinfo&format=json&api_key='.LAST_FM_API.'&user='.urlencode($username)));

			if ($response == false || empty($response)) throw new Exception("There are problems with connection to Last.fm database. Please try again later.");
			if (property_exists($response, 'error')) throw new Exception('Last.fm error '.$response->error.': '.$response->message.'.');
			
			return $response;
		}

		static public function artistGetInfo($artist) {
			$response = json_decode(file_get_contents('http://ws.audioscrobbler.com/2.0/?method=artist.getinfo&format=json&api_key='.LAST_FM_API.'&artist='.urlencode($artist)));
			
			if ($response == false || empty($response)) throw new Exception("There are problems with connection to Last.fm database. Please try again later.");
			if (property_exists($response, 'error')) throw new Exception('Last.fm error '.$response->error.': '.$response->message.'.');

			return $response;
		}
	}
?>