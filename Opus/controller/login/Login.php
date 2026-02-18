<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-02-14 09:08:34
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-02-14 09:26:49
 **/

namespace Opus\controller\login;

use OPus\config\Config;
use Opus\controller\exception\ControllerException;
use Opus\controller\request\Request;
use Opus\storage\exception\StorageException;

class Login extends AbstractLogin
{
	/**
	 * Handles different types of login processes based on the login type
	 *
	 * @param string $loginType Type of login to process:
	 * 					- TYPE_LOGIN_PAGE: Web page login
	 * 					- TYPE_LOGIN_CLI: Command line interface login
	 * 					- TYPE_LOGIN_API: API login
	 *
	 * @throws ControllerException When invalid login type is provided
	 * @return void
	 *
	 * @see Login::TYPE_LOGIN_PAGE
	 * @see Login::TYPE_LOGIN_CLI
	 * @see Login::TYPE_LOGIN_API
	 */
	public static function login(string $loginType): void
	{
		$login = new Login();

		try {
			match ($loginType) {
				self::TYPE_LOGIN_PAGE => (function () use ($login) {
					unset($_SESSION['li']);
					$login->loginPage();
				})(),
				self::TYPE_LOGIN_CLI => $login->loginCli(),
				self::TYPE_LOGIN_API => $login->loginApi(),
				default => throw new ControllerException(
					'controller\login\login',
					['message' => $loginType]
				)
			};
		} finally {
			unset($login);
		}
	}

	/**
	 * Logs out the current user and destroys their session
	 *
	 * This method:
	 * - Clears all session data
	 * - Destroys the session
	 * - Resets session variables to default values
	 * - Redirects to the main page
	 *
	 * @return void
	 *
	 * @see session_unset()
	 * @see session_destroy()
	 * @see Request::url()
	 */
	public static function logout(): void
	{
		session_unset();
		session_destroy();
		$_SESSION['login'] = 'NoLogged';
		$_SESSION['logged'] = false;
		$_SESSION['level'] = '0';
		$_SESSION['lang'] = Config::getConfig()->langs[0];
		$_SESSION['csrf'] = bin2hex(random_bytes(32));
		header('Location: ' . Request::url('index.php?page=main'));
	}

	/**
	 * Reloads user configuration settings
	 *
	 * Creates a temporary login instance to refresh user configuration
	 * settings without requiring a full re-login. This is typically used
	 * when user settings have been modified and need to be reflected
	 * in the current session.
	 *
	 * @return void
	 *
	 * @see Login::reloadUserConfig()
	 */
	public static function reloadConfig(): void
	{
		$login = new Login();
		$login->reloadUserConfig();
		unset($login);
	}

	/**
	 * Verifies if the provided password matches the stored password for a given user
	 *
	 * Creates a temporary login instance to verify password credentials.
	 * The password verification is done using secure hashing comparison.
	 *
	 * @param string $user Username/login to check
	 * @param string $pass Password to verify
	 *
	 * @throws StorageException When database access fails or user not found
	 * @return bool True if password matches, false otherwise
	 *
	 * @see Login::isPasswordMatches()
	 */
	public static function passwordMatches(string $user, string $pass): bool
	{
		$login = new Login(Login::TYPE_LOGIN_PAGE);
		$result = $login->isPasswordMatches($user, $pass, StorageException::TYPE_PAGE_EXCEPTION);
		unset($login);
		return $result;
	}

	/**
	 * Updates the password for a specified user
	 *
	 * Creates a temporary login instance to handle password update.
	 * The password should be pre-hashed before being passed to this method.
	 *
	 * @param string $user Username/login of the user to update
	 * @param string $pass New password (should be pre-hashed)
	 *
	 * @throws StorageException When database update fails or user not found
	 * @return void
	 *
	 * @see Login::updateUserPassword()
	 */
	public static function updatePassword(string $user, string $pass): void
	{
		$login = new Login();
		$login->updateUserPassword($user, $pass);
		unset($login);
	}

	/**
	 * Resets a user's password by administrator using user ID
	 *
	 * Creates a temporary login instance to handle administrative password reset.
	 * This method is specifically designed for administrators to reset user passwords
	 * using the user's unique ID rather than their login name. The action is logged
	 * as an administrative password change.
	 *
	 * @param string $userId The unique user ID (id__users) of the user whose password to reset
	 * @param string $pass New plain text password to be hashed and stored
	 *
	 * @throws StorageException When database update fails or user ID not found
	 * @return string|false user login or false if login with new password is not found
	 *
	 * @see Login::updateIdPassword()
	 */
	public static function resetPasswordByAdmin(
		string $userId,
		string $pass
	): string|false {
		$login = new Login();
		$result = $login->updateIdPassword($userId, $pass);
		unset($login);
		return $result;
	}
}
