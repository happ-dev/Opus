<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-02-13 09:45:59
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-02-13 11:36:43
 **/

namespace Opus\controller\lang;

use Opus\config\Config;
use Opus\storage\json\Json;

class Lang
{
	private static ?Lang $instance = null;
	private static array $translations = [];

	private function __construct()
	{
		$this->load();
	}

	/**
	 * Prevents cloning of the singleton instance
	 */
	private function __clone() {}

	/**
	 * Returns the singleton instance of Lang class
	 *
	 * @return Lang The singleton instance
	 */
	public static function getInstance(): Lang
	{
		if (self::$instance === null) {
			self::$instance = new Lang();
		}
		return self::$instance;
	}

	/**
	 * Retrieves a translation for the given key
	 *
	 * @param string|null $key The translation key (e.g., 'controller.login.user')
	 * @return string The translated string or the key itself if translation not found
	 */
	public function get(?string $key): string
	{
		return self::$translations[$key] ?? $key;
	}

	/**
	 * Loads translation files from Opus framework and all configured applications
	 *
	 * Merges translations from main Opus lang file and all app-specific lang files
	 * based on the current session language.
	 *
	 * @return void
	 */
	private function load(): void
	{
		// main Opus lang file
		$opus = __DIR__ . '/../../../vendor/Opus/controller/lang/' . $_SESSION['lang'] . '_opus.json';
		self::$translations = Json::loadJsonFile($opus, true);

		// Search through all configured applications
		foreach (Config::getConfig()->apps as $app) {
			$file = __DIR__ . '/../../../apps/' . $app . '/lang/' . $_SESSION['lang'] . '_' . $app . '.json';
			$appTranslations = Json::loadJsonFile($file, true);

			if (is_array($appTranslations)) {
				self::$translations = array_merge(self::$translations, $appTranslations);
			}
		}
	}
}
