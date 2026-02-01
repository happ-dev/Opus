<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-02-01 20:04:47
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-02-01 20:09:05
**/

namespace Opus\controller\cli;

class CliColor {

	const COLOR_NORMAL = '0;39';
	const COLOR_BOLD = '1';
	const COLOR_DIM = '2';
	const COLOR_BLACK = '0;30';
	const COLOR_DARK_GRAY = '1;30';
	const COLOR_BLUE = '0;34';
	const COLOR_LIGHT_BLUE = '1;34';
	const COLOR_GREEN = '0;32';
	const COLOR_LIGHT_GREEN = '1;32';
	const COLOR_CYAN = '0;36';
	const COLOR_LIGHT_CYAN = '1;36';
	const COLOR_RED = '0;31';
	const COLOR_LIGHT_RED = '1;31';
	const COLOR_PURPLE = '0;35';
	const COLOR_LIGHT_PURPLE = '1;35';
	const COLOR_BROWN = '0;33';
	const COLOR_YELLOW = '1;33';
	const COLOR_LIGHT_GRAY = '0;37';
	const COLOR_WHITE = '1;37';
	const BGCOLOR_BLACK = '40';
	const BGCOLOR_RED = '41';
	const BGCOLOR_GREEN = '42';
	const BGCOLOR_YELLOW = '43';
	const BGCOLOR_BLUE = '44';
	const BGCOLOR_MAGENTA = '45';
	const BGCOLOR_CYAN = '46';
	const BGCOLOR_LIGHT_GRAY = '47';

	/**
	 * Formats text with ANSI color codes for CLI output
	 * 
	 * This method applies foreground color, background color, and styling to text
	 * for display in terminal environments that support ANSI escape sequences.
	 * 
	 * @param string $str The text to be colored
	 * @param string|null $color The foreground color/style code (use class constants like COLOR_RED)
	 * @param string|null $bgColor The background color code (use class constants like BGCOLOR_BLUE)
	 * @param bool $newLine Whether to append a newline character to the output
	 * @return string The formatted text with ANSI color codes
	 */
	final public static function write(string $str, ?string $color = self::COLOR_NORMAL, ?string $bgColor = null, bool $newLine = true): string
	{
		// Determine color code based on provided parameters
		$strColor = match(true) {
			!is_null($color) && !is_null($bgColor) => "\e[" . $color . ';' . $bgColor . "m",
			!is_null($color) => "\e[" . $color . "m",
			!is_null($bgColor) => "\e[" . $bgColor . "m",
			default => ''
		};

		// Apply colors and optionally add newline
		return $strColor . $str . "\e[0m" . ($newLine ? PHP_EOL : '');
	}

}