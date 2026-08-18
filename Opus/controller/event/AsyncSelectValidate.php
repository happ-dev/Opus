<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-07-29 19:31:33
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-08-01 15:34:40
 **/

namespace Opus\controller\event;

use Opus\controller\exception\ControllerException;
use Opus\libs\Common;

/**
 * Validates asyncSelect configuration and determines the processing scenario.
 *
 * Receives $config object by reference from AsyncSelect::selectConfig().
 * After construction, $this->config->scenario is set to one of 8 scenario constants
 * accessible by the caller via the same reference.
 *
 * Config structure (from app.config.json asyncSelect entry):
 *   Each entry is an object with "type": "api", "access": N, plus data keys:
 *   - Flat: "text" (array|string) + optional "value" (array)
 *   - Optgroup: "opt" (array of objects with "label", "text", optional "value")
 *
 * Scenario codes (4-digit binary-like string):
 *   Position [0]: always '1'
 *   Position [1]: '0' = text only, '1' = text + value
 *   Position [2]: '0' = flat, '1' = optgroup
 *   Position [3]: '0' = static, '1' = SQL
 *
 *   FLAT_TEXT      '1000'  static flat, text = value
 *   FLAT_VALUE     '1100'  static flat, text + value
 *   FLAT_TEXT_SQL  '1001'  SQL flat, 1 column
 *   FLAT_VALUE_SQL '1101'  SQL flat, 2 columns
 *   OPT_TEXT       '1010'  static optgroup, text = value
 *   OPT_VALUE      '1110'  static optgroup, text + value
 *   OPT_TEXT_SQL   '1011'  SQL optgroup, 1 column
 *   OPT_VALUE_SQL  '1111'  SQL optgroup, 2 columns
 *
 * @param object &$config Reference to AsyncSelect config object.
 *   Required properties:
 *     ->async (object) Raw asyncSelect entry (stdClass with type, access, text/opt/value)
 *     ->event (string) Event name for error messages
 *   Set by this class:
 *     ->scenario (string) One of the 8 scenario constants
 */
class AsyncSelectValidate
{
	const FLAT_TEXT = AsyncSelect::FLAT_TEXT;
	const FLAT_VALUE = AsyncSelect::FLAT_VALUE;
	const FLAT_TEXT_SQL = AsyncSelect::FLAT_TEXT_SQL;
	const FLAT_VALUE_SQL = AsyncSelect::FLAT_VALUE_SQL;
	const OPT_TEXT = AsyncSelect::OPT_TEXT;
	const OPT_VALUE = AsyncSelect::OPT_VALUE;
	const OPT_TEXT_SQL = AsyncSelect::OPT_TEXT_SQL;
	const OPT_VALUE_SQL = AsyncSelect::OPT_VALUE_SQL;

	public function __construct(public object &$config)
	{
		$this->selectScenario();
		$this->validate();
	}

	/**
	 * Determines scenario based on $this->config->async structure.
	 * Uses Common::isQuery() to detect SQL vs static and column count.
	 * Sets $this->config->scenario.
	 *
	 * @throws ControllerException When config structure does not match any known scenario
	 */
	private function selectScenario(): void
	{
		$async = $this->config->async;

		$this->config->scenario = match (true) {
			isset($async->opt) => match (Common::isQuery($async->opt[0]->text ?? '')) {
				false   => isset($async->opt[0]->value) ? self::OPT_VALUE : self::OPT_TEXT,
				'text'  => self::OPT_TEXT_SQL,
				'value' => self::OPT_VALUE_SQL,
			},
			isset($async->text) => match (Common::isQuery($async->text)) {
				false   => isset($async->value) ? self::FLAT_VALUE : self::FLAT_TEXT,
				'text'  => self::FLAT_TEXT_SQL,
				'value' => self::FLAT_VALUE_SQL,
			},
			default => throw new ControllerException(
				'controller\asyncSelect\validate\scenario',
				['message' => $this->config->event],
				ControllerException::TYPE_API_EXCEPTION
			)
		};
	}

	/**
	 * Validates static configurations (scenarios ending with '0').
	 * SQL scenarios are skipped — isQuery() already confirmed valid SELECT.
	 *
	 * Checks per group:
	 *   - OPT_*: 'label' must be a non-empty string
	 *   - 'text' must be a non-empty array
	 *   - 'value' (if present) must have the same length as 'text'
	 *
	 * @throws ControllerException When static config structure is invalid
	 */
	private function validate(): void
	{
		if (str_ends_with($this->config->scenario, '1')) return;

		$async = $this->config->async;
		$event = $this->config->event;
		$isOpt = $this->config->scenario[2] === '1';

		$groups = $isOpt ? $async->opt : [$async];

		foreach ($groups as $i => $group) {
			if ($isOpt) {
				(empty($group->label) || !is_string($group->label))
					? throw new ControllerException(
						'controller\asyncSelect\validate\label',
						['message' => [$event, $i]],
						ControllerException::TYPE_API_EXCEPTION
					) : null;
			}

			(!is_array($group->text) || empty($group->text))
				? throw new ControllerException(
					'controller\asyncSelect\validate\text',
					['message' => [$event, $i]],
					ControllerException::TYPE_API_EXCEPTION
				) : null;

			(isset($group->value) && count($group->value) !== count($group->text))
				? throw new ControllerException(
					'controller\asyncSelect\validate\value',
					['message' => [$event, $i]],
					ControllerException::TYPE_API_EXCEPTION
				) : null;
		}
	}
}
