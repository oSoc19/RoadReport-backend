<?php
	/**
	 * 
	 */
	class Image
	{
		private $ctx;
		private $ratio;
		private $max_width, $max_height;
		private $quality = 60;

		public function __construct($img, $maxw = -1, $maxh = -1)
		{
			if (file_exists($img))
				$this->ctx = $this->createimagefromformat($img);
			else
				$this->ctx = $this->createimagefromdata($img);
			$this->max_width = is_numeric($maxw) && $maxw > 0 ? $maxw : -1;
			$this->max_height = is_numeric($maxh) && $maxh > 0 ? $maxh : -1;
			$this->ratio = $this->max_width > 0 && $this->max_height > 0 ? $this->max_width/$this->max_height : -1;
			if (!!$this->ctx)
			{
				$this->checkSize();
				return $this;
			}
			else
			{
				//$this->ctx = null;
				$this->__destruct();
				return false;
			}
		}

		public function __destruct()
		{
			if ($this->ctx != null)
				imagedestroy($this->ctx);
		}

		private function createimagefromdata($data)
		{
			if (preg_match("/^data:image\/(\w+);base64,/i", $data, $m))
			{
				$data = substr($data, strlen($m[0]));	// remove data header
				$data = base64_decode($data);
				return imagecreatefromstring($data);
			}
			return false;
		}

		private function createimagefromformat($path='')
		{
			if (!file_exists($path))
				return false;
			if (preg_match("/\.jp(e)g$/i", $path))
				return imagecreatefromjpeg($path);
			elseif (preg_match("/\.png$/i", $path))
				return imagecreatefrompng($path);
			elseif (preg_match("/\.gif$/i", $path))
				return imagecreatefromgif($path);
			elseif (preg_match("/\.bmp$/i", $path))
				return imagecreatefrombmp($path);
			return false;
		}

		private function checkSize()
		{
			if ($this->ctx == null)
				return;
			if (imagesx($this->ctx) / imagesy($this->ctx) > $this->ratio)
			{
				$rot = imagerotate($this->ctx, 270, 0);
				imagedestroy($this->ctx);	// let's to be free
				$this->ctx = $rot;
			}
			if (imagesx($this->ctx) > $this->max_width)
			{
				$im = imagecreatetruecolor($this->max_width, imagesy($this->ctx)/$this->max_width*$this->max_height);
				imagecopyresampled($im, $this->ctx, 0, 0, 0, 0, imagesx($im), imagesy($im), imagesx($this->ctx), imagesy($this->ctx));
				imagedestroy($this->ctx);
				$this->ctx = $im;
			}
		}

		public function md5()
		{
			if ($this->ctx == null)
				return false;
			ob_start();
			imagejpeg($this->ctx);
			$out = ob_get_contents();
			ob_end_clean();
			return md5($out);
		}

		public function save($path='')
		{
			if ($this->ctx == null)
				return false;
			if (!file_exists($path))
				imagejpeg($this->ctx, $path, $quality);
		}
	}