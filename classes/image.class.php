<?php
	class Image {
		private $image;
		private $options;
		private $currentTextMargin;
		private $textColor;
		private $bgColor;

		public function __construct($optionsOrPath) {
			if(is_array($optionsOrPath)) {
				if(isset($optionsOrPath['im_height']) && isset($optionsOrPath['im_width']) && isset($optionsOrPath['margin']) &&
					isset($optionsOrPath['text_color']) && isset($optionsOrPath['bg_color'])) {
						$this->options = $optionsOrPath;
						$this->currentTextMargin = $optionsOrPath['margin'];

						$this->image = $this->createFromOptions($this->options['im_width'], $this->options['im_height']);
				}
				else throw new Exception("Constructor exception: Incorrect options array.");
			}
			else {
				$this->image = $this->createFromPath($optionsOrPath);
			}
		}

		public function __destruct() {
			imagedestroy($this->image);
		}

		private function createFromPath($filepath) {
				$type = @exif_imagetype($filepath);
				if($type === false) {
					throw new Exception("createFromPath() exception: Not an image.");
				}
				$allowedTypes = array(
					1,  // gif
					2,  // jpg
					3,  // png
					6   // bmp
				);
				if (!in_array($type, $allowedTypes)) {
					throw new Exception("createFromPath() exception: Image type not allowed.");
				}
				switch ($type) {
					case 1:
						$im = imagecreatefromgif($filepath);
					break;
					case 2:
						$im = imagecreatefromjpeg($filepath);
					break;
					case 3:
						$im = imagecreatefrompng($filepath);
					break;
					case 6:
						$im = imagecreatefrombmp($filepath);
					break;
				}
				return $im; 
		}

		private function createFromOptions($width, $height) {
			$im = imagecreatetruecolor($width, $height);
			$this->textColor = $this->registerColor($this->options['text_color'], $im);
			$this->bgColor = $this->registerColor($this->options['bg_color'], $im);
			imagefilledrectangle($im, 0, 0, $width-1, $height-1, $this->bgColor);
			return $im;
		}

		private function registerColor($hex, $im) {
			$rgb = str_split($hex, 2);
			$rgb[0] = hexdec($rgb[0]);
			$rgb[1] = hexdec($rgb[1]);
			$rgb[2] = hexdec($rgb[2]);
			
			return imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
		}

		public function insertCenterText($string, $fontSize, $font, $margin = 12) {
			$this->currentTextMargin += $fontSize + ($this->currentTextMargin == $this->options['margin'] ? 0 : $margin);
			$bbox = imagettfbbox($fontSize, 0, $font, $string);
			$x = $bbox[0] + ($this->options['im_width'] / 2) - ($bbox[4] / 2);
			imagettftext($this->image, $fontSize, 0, $x, $this->currentTextMargin, $this->textColor, $font, $string);
		}

		public function insertImage($image, $marginBefore=12, $marginAfter=5) {
			$image = $image->asResource();

			$this->currentTextMargin += $marginBefore;
			$x = ($this->options['im_width'] / 2) - (imagesx($image) / 2);
			imagecopy($this->image, $image, $x, $this->currentTextMargin, 0, 0, imagesx($image), imagesy($image));
			$this->currentTextMargin += imagesy($image) + $marginAfter;
		}

		public function addMargin($margin) {
			$this->currentTextMargin += $margin;
		}

		public function asResource() {
			return $this->image;
		}

		public function getHeight() {
			if(isset($this->options))
				return $this->options['im_height'];
			else
				return imagesy($this->image);
		}

		public function getWidth() {
			if(isset($this->options))
				return $this->options['im_height'];
			else
				return imagesx($this->image);
		}

		public function saveImage($output) {
			imagepng($this->image, $output);
		}

		public function sendImage() {
			ob_start();
			imagepng($this->image);
			$imagedata = ob_get_contents();
			ob_end_clean();

			$client_id = IMGUR_API;
			$url = 'https://api.imgur.com/3/image.json';
			$headers = array("Authorization: Client-ID $client_id");
			$pvars  = array('image' => base64_encode($imagedata));

			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL=> $url,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_POST => 1,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_HTTPHEADER => $headers,
				CURLOPT_POSTFIELDS => $pvars,
				CURLOPT_SSL_VERIFYPEER => false
			));

			$reply = json_decode(curl_exec($curl));
			if ($reply == false || empty($reply)) throw new Exception("There are problems with connection to Imgur API. Please try again later.");
			if (property_exists($reply->data, 'error')) throw new Exception('Imgur API error: '.$reply->data->error.'.');
			$url = $reply->data->link;

			curl_close ($curl); 

			return $url;
		}

		static public function setProperFontSize($string, $startingFontSize, $font, $maxWidth) {
			$logobox = imagettfbbox($startingFontSize, 0, $font, $string);
			$logowidth = $logobox[4]-$logobox[6];
			if ($logowidth > $maxWidth) {
				while($logowidth > $maxWidth) {
					$startingFontSize--;
					$logobox = imagettfbbox($startingFontSize, 0, $font, $string);
					$logowidth = $logobox[4]-$logobox[6];
				}
			}
			return $startingFontSize;
		}
	}
?>