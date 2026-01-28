<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-01-27 11:06:49
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-01-27 14:04:08
**/

namespace Opus\config;

use Exception;

abstract class ValidateGlobalConfig {

	protected static object $config;

	const OPUS_APPS = [
		[
			'app' => 'skeleton',
			'config' => 'vendor/Opus/apps/skeleton/config/skeleton.config.json'
		],
		[
			'app' => 'settings',
			'config' => 'vendor/Opus/apps/settings/config/settings.config.json'
		],
		[
			'app' => 'profile',
			'config' => 'vendor/Opus/apps/profile/config/profile.config.json'
		],
		[
			'app' => 'logs',
			'config' => 'vendor/Opus/apps/logs/config/logs.config.json'
		],
		[
			'app' => 'demo',
			'config' => 'vendor/Opus/apps/demo/config/demo.config.json'
		]
	];

	const OPUS_MAIN_APP = self::OPUS_APPS[0]['app'];
	const OPUS_GLOBAL_CONFIG = 'config/global.json';
	const OPUS_LOCAL_CONFIG = 'config/local.json';
	const OPUS_SECRET_KEY = 'config/secret.key';

	private const STORAGE_REQUIRED_TYPES = ['pgsql', 'ibmodbc', 'mysql'];
	private const REGEX_DB_ENCODING = ['options' => ['regexp' => '/^(UTF-?8|LATIN[0-9]|ASCII|UNICODE|WIN(1250|1251|1252)|ISO-?8859-[0-9]|CP[0-9]{3,4})$/i']];
	public const ALLOWED_LANGUAGES = [
		'en' => ['US', 'GB', 'AU'],		// English variants
		'pl' => ['PL']					// Polish variants
	];

	/**
	 * Validates app names array
	 *
	 * @param array $apps Array of app names to validate
	 * @return bool True if all app names are valid
	 * @throws Exception If validation fails
	 */
	final protected function validateAppsNames(array $apps): bool
	{
		if (empty($apps)) {
			throw new Exception('Apps array cannot be empty');
		}

		foreach ($apps as $app) {
			match (true) {
				!is_string($app) => throw new Exception('App name must be a string'),
				!preg_match('/^[a-z0-9_]+$/', $app) => throw new Exception("Invalid app name: '{$app}'. Only lowercase letters, numbers, hyphens and underscores are allowed"),
				strlen($app) < 2 || strlen($app) > 50 => throw new Exception("App name '{$app}' must be between 2 and 50 characters"),
				default => true
			};
		}

		return true;
	}

	/**
	 * Function checks the correctness of storage configuration
	 * 
	 * @param object $config
	 * @return void
	 * @throws Exception if the configuration is incorrect
	 */
	final protected function validateStorageConfig(object $config): void
	{
		$errors = [];
		$required = [
			'type' => fn($v) => in_array($v, self::STORAGE_REQUIRED_TYPES),
			'host' => fn($v) => EncryptStorageConfig::decrypt($v) === 'localhost' || filter_var(EncryptStorageConfig::decrypt($v), FILTER_VALIDATE_IP),
			'port' => fn($v) => filter_var(EncryptStorageConfig::decrypt($v), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]),
			'user' => fn($v) => EncryptStorageConfig::isEncrypted($v),
			'pass' => fn($v) => EncryptStorageConfig::isEncrypted($v)
		];

		foreach ($required as $field => $validator) {

			if (!property_exists($config, $field)) {
				$errors[] = "Missing required field: {$field}";
				continue;
			}

			if ($validator($config->$field) === false) {
				$errors[] = match ($field) {
					'type' => 'Invalid storage type',
					'host' => 'Invalid storage host',
					'port' => 'Storage port must be between 1 and 65535',
					'user' => 'Empty username',
					'pass' => 'Empty password',
					default => "Invalid value for {$field}"
				};
			}

		}

		// Optional encoding validation
		if ((property_exists($config, 'encoding')) && (!filter_var($config->encoding, FILTER_VALIDATE_REGEXP, self::REGEX_DB_ENCODING))) {
			$errors[] = "Invalid encoding format: {$config->encoding}";
		}

		if (!empty($errors)) {
			throw new Exception("Configuration validation failed:\n" . implode(PHP_EOL, $errors));
		}
	}

	/**
	 * @param array $languages The languages to validate
	 * @return void
	 * @throws Exception If validation fails
	 */
	final protected function validateLanguagesConfig(array &$languages): void
	{
		foreach ($languages as $lang) {
			// Handle both simple codes and language-region formats
			$parts = explode('-', str_replace('_', '-', strtolower($lang)));
			$langCode = $parts[0];
			$regionCode = $parts[1] ?? null;

			// Validate language code
			if (!isset(self::ALLOWED_LANGUAGES[$langCode])) {
				throw new Exception(
					sprintf(
						"Unsupported language code: '%s'. Allowed languages are: %s",
						$langCode,
						implode(', ', array_keys(self::ALLOWED_LANGUAGES))
					)
				);
			}

			// Validate region code if provided
			if ($regionCode !== null) {
				$regionCode = strtoupper($regionCode);

				if (!in_array($regionCode, self::ALLOWED_LANGUAGES[$langCode], true)) {
					throw new Exception(
						sprintf(
							"Unsupported region '%s' for language '%s'. Allowed regions are: %s",
							$regionCode,
							$langCode,
							implode(', ', self::ALLOWED_LANGUAGES[$langCode])
						)
					);
				}

			}

		}
	}

	

}