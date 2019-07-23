<?php
	/**
	 * 
	 */
	class Lang
	{
		public static $dictionnay = null;

		public static function analyze($force = null)
		{
			global $settings;
			$lang = $force != null ? $force : $settings['lang_default'];
			$path = $settings['lang_path'];
			if (isset($_COOKIE['sLang'])&&is_file($path.substr($_COOKIE['sLang'], 0, 2).'.json'))
			{
				$lang = substr($_COOKIE['sLang'], 0, 2);
			}
			else
			{
				if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])&&@is_file($path.substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2).'.json'))
					$lang  = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
			}
			if (is_file($path."{$lang}.json"))
			{
				$json = file_get_contents($path."{$lang}.json");
				$a_lang = json_decode($json, true);
				self::$dictionnay=$a_lang;
			}
			else
			{
				die("LANG FAILLED");
			}
		}

		public static function get($key)
		{
			if (self::$dictionnay == null)
				self::analyze();
			if (isset(self::$dictionnay[$key]))
				return self::$dictionnay[$key];
			return $key;
		}

		public static function replaceTags($str, $force = null)
		{
			if ($force != null)
				self::analyze($force);
			if (self::$dictionnay == null)
				self::analyze();
			$l = self::$dictionnay;
			preg_match_all("|\{\{lang=([a-z]+)\}\}(.+)\{\{/lang\}\}|siU", $str, $r);
			foreach ($r[2] as $k => $v)
				$str = str_replace($r[0][$k], ((strtolower($r[1][$k])==strtolower($l['LANGCODE']))?$r[2][$k]:''), $str);
			preg_match_all("/\{\{([A-Z0-9_]+)\}\}/", $str, $r2);
			foreach ($r2[1] as $k => $v)
				if (isset($l[$v])) $str = str_replace('{{'.$v.'}}', $l[$v], $str);
			return $str;
		}
	} ?>