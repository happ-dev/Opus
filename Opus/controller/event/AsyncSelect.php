<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-07-29 19:31:04
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-08-01 18:01:17
 **/

namespace Opus\controller\event;

use stdClass;
use PDO;
use Opus\config\Config;
use Opus\controller\request\Request;
use Opus\controller\exception\ControllerException;
use Opus\controller\lang\Lang;
use Opus\storage\db\Db;

/**
 * Handles asyncSelect API event — provides paginated, searchable data for OpusSelect component.
 *
 * Registered under Event::TYPE_API as 'asyncselect'.
 * URL: index.php?api=asyncselect&app={app}&event={event}&limit={n}&offset={n}&search={text}
 *
 * Reads configuration from app.config.json -> asyncSelect -> {event}.
 * Each entry has "type": "api", "access": N, plus data keys (text/value/opt).
 * AsyncSelectValidate determines one of 8 scenarios based on config structure.
 *
 * Scenarios (4-digit code):
 *   FLAT_TEXT      '1000'  static, text = value
 *   FLAT_VALUE     '1100'  static, text + value
 *   FLAT_TEXT_SQL  '1001'  SQL, single column
 *   FLAT_VALUE_SQL '1101'  SQL, two columns (value, text)
 *   OPT_TEXT       '1010'  static optgroup, text = value
 *   OPT_VALUE      '1110'  static optgroup, text + value
 *   OPT_TEXT_SQL   '1011'  SQL optgroup, single column
 *   OPT_VALUE_SQL  '1111'  SQL optgroup, two columns
 *
 * Static methods filter via stripos, SQL methods via ILIKE on text column alias.
 * Paging: LIMIT/OFFSET for SQL, array_slice for static.
 * Total: full count without filter (separate COUNT query for SQL when search active).
 * Filtered: count after search applied (COUNT(*) OVER() for SQL, count() for static).
 *
 * JSON response: {scenario, total, filtered, limit, offset, data}
 *   data (flat): [{value, text}, ...]
 *   data (opt):  [{label, options: [{value, text}, ...]}, ...]
 */
class AsyncSelect
{
	const FLAT_TEXT = '1000';
	const FLAT_VALUE = '1100';
	const FLAT_TEXT_SQL = '1001';
	const FLAT_VALUE_SQL = '1101';
	const OPT_TEXT = '1010';
	const OPT_VALUE = '1110';
	const OPT_TEXT_SQL = '1011';
	const OPT_VALUE_SQL = '1111';

	private object $config;

	public function __construct()
	{
		$csrf = Request::validateCsrfToken();

		if ($csrf !== true) {
			throw new ControllerException(
				'controller\asyncSelect\csrf',
				['message' => $csrf],
				ControllerException::TYPE_API_EXCEPTION
			);
		}

		$this->config = new stdClass();
		$this->selectConfig();
	}

	public static function doAsyncSelect(): void
	{
		$select = new self();
		$data = $select->fetchData();

		echo json_encode([
			"scenario" => $data->scenario,
			"total" => $data->total,
			"filtered" => $data->filtered,
			"limit" => $data->limit,
			"offset" => $data->offset,
			"data" => $data->data
		]);
	}

	private function selectConfig(): void
	{
		$this->config->app = Request::fromUrl('app');
		$this->config->event = Request::fromUrl('event');
		$limit = Request::fromUrl('limit');
		$offset = Request::fromUrl('offset');
		$this->config->limit = $limit === '/' ? 20 : (int) $limit;
		$this->config->offset = $offset === '/' ? 0 : (int) $offset;
		$search = Request::fromUrl('search');
		$this->config->search = $search === '/' ? false : $search;
		$this->config->async = Config::getConfig($this->config->app)->asyncSelect->{$this->config->event};
		new AsyncSelectValidate($this->config);
	}

	private function fetchData(): object
	{
		return match ($this->config->scenario) {
			self::FLAT_TEXT => $this->flatText(),
			self::FLAT_VALUE => $this->flatValue(),
			self::FLAT_TEXT_SQL => $this->flatTextSql(),
			self::FLAT_VALUE_SQL => $this->flatValueSql(),
			self::OPT_TEXT => $this->optText(),
			self::OPT_VALUE => $this->optValue(),
			self::OPT_TEXT_SQL => $this->optTextSql(),
			self::OPT_VALUE_SQL => $this->optValueSql()
		};
	}

	/**
	 * Static flat scenario — text only (text = value).
	 * Filters by search (stripos), paginates, maps to {value, text} pairs.
	 *
	 * https://14.6.83.14/opus/index.php?api=asyncselect&app=demo&event=demoSelectTextSql
	 * https://14.6.83.14/opus/index.php?api=asyncselect&app=demo&event=demoSelectTextSql&search=kow
	 */
	private function flatText(): object
	{
		$data = $this->config->async->text;
		$total = count($data);

		// Filter
		if ($this->config->search !== false) {
			$data = array_values(array_filter(
				$data,
				fn($text) => stripos($text, $this->config->search) !== false
			));
		}

		$filtered = count($data);

		// Paging
		$data = array_slice($data, $this->config->offset, $this->config->limit);

		return (object) [
			'scenario' => $this->config->scenario,
			'total' => $total,
			'filtered' => $filtered,
			'limit' => $this->config->limit,
			'offset' => $this->config->offset,
			'data' => array_map(fn($text) => ['value' => $text, 'text' => $text], $data)
		];
	}

	/**
	 * Static flat scenario — text + separate value array.
	 * Filters by text (stripos), keeps value in sync, paginates both arrays.
	 * https://14.6.83.14/opus/index.php?api=asyncselect&app=demo&event=demoSelectValue
	 * https://14.6.83.14/opus/index.php?api=asyncselect&app=demo&event=demoSelectValue&search=kow
	 */
	private function flatValue(): object
	{
		$texts = $this->config->async->text;
		$values = $this->config->async->value;
		$total = count($texts);

		// Filter
		if ($this->config->search !== false) {
			$keys = array_keys(array_filter(
				$texts,
				fn($text) => stripos($text, $this->config->search) !== false
			));
			$texts = array_values(array_intersect_key($texts, array_flip($keys)));
			$values = array_values(array_intersect_key($values, array_flip($keys)));
		}

		$filtered = count($texts);

		// Paging
		$texts = array_slice($texts, $this->config->offset, $this->config->limit);
		$values = array_slice($values, $this->config->offset, $this->config->limit);

		return (object) [
			'scenario' => $this->config->scenario,
			'total' => $total,
			'filtered' => $filtered,
			'limit' => $this->config->limit,
			'offset' => $this->config->offset,
			'data' => array_map(
				fn($t, $v) => ['value' => $v, 'text' => $t],
				$texts,
				$values
			)
		];
	}

	/**
	 * SQL flat scenario — single column (text = value).
	 * Wraps config SQL in subquery, filters by alias ILIKE, paginates via LIMIT/OFFSET.
	 * Uses COUNT(*) OVER() for filtered count without separate query.
	 *
	 * https://14.6.83.14/opus/index.php?api=asyncselect&app=demo&event=demoSelectTextSql
	 * https://14.6.83.14/opus/index.php?api=asyncselect&app=demo&event=demoSelectTextSql&search=ann
	 */
	private function flatTextSql(): object
	{
		$sql = rtrim(trim($this->config->async->text), ';');

		preg_match('/SELECT\s+(.+?)\s+FROM/i', $sql, $m);
		$col = trim($m[1]);
		$alias = preg_match('/\bAS\s+(\w+)\s*$/i', $col, $a) ? $a[1] : $col;

		if ($this->config->search !== false) {
			$params = [
				'prepare' => <<<SQL
				SELECT t.{$alias}, COUNT(*) OVER() AS __filtered FROM ({$sql}) AS t WHERE t.{$alias} ILIKE :search LIMIT :limit OFFSET :offset
				SQL,
				'params' => [':search', ':limit', ':offset'],
				':search' => '%' . $this->config->search . '%',
				':limit' => $this->config->limit,
				':offset' => $this->config->offset,
				'pdoTypes' => [PDO::PARAM_STR, PDO::PARAM_INT, PDO::PARAM_INT]
			];
		} else {
			$params = [
				'prepare' => <<<SQL
				SELECT t.{$alias}, COUNT(*) OVER() AS __filtered FROM ({$sql}) AS t LIMIT :limit OFFSET :offset
				SQL,
				'params' => [':limit', ':offset'],
				':limit' => $this->config->limit,
				':offset' => $this->config->offset,
				'pdoTypes' => [PDO::PARAM_INT, PDO::PARAM_INT]
			];
		}

		$data = Db::dbExecute($params);
		$filtered = (int) ($data[0]['__filtered'] ?? 0);
		$total = $this->config->search !== false ? $this->countTotal($sql) : $filtered;

		return (object) [
			'scenario' => $this->config->scenario,
			'total' => $total,
			'filtered' => $filtered,
			'limit' => $this->config->limit,
			'offset' => $this->config->offset,
			'data' => array_map(fn($row) => ['value' => $row[$alias], 'text' => $row[$alias]], $data)
		];
	}

	/**
	 * SQL flat scenario — two columns (first = value, second = text).
	 * Wraps config SQL in subquery, filters by text alias ILIKE, paginates via LIMIT/OFFSET.
	 * Uses COUNT(*) OVER() for filtered count without separate query.
	 *
	 * https://14.6.83.14/opus/index.php?api=asyncselect&app=demo&event=demoSelectValueSql
	 * https://14.6.83.14/opus/index.php?api=asyncselect&app=demo&event=demoSelectValueSql&search=ann
	 */
	private function flatValueSql(): object
	{
		$sql = rtrim(trim($this->config->async->text), ';');

		preg_match('/SELECT\s+(.+?)\s+FROM/i', $sql, $m);
		$cols = $this->splitColumns($m[1]);
		$valueCol = preg_match('/\bAS\s+(\w+)\s*$/i', $cols[0], $a) ? $a[1] : trim($cols[0]);
		$textCol = preg_match('/\bAS\s+(\w+)\s*$/i', $cols[1], $a) ? $a[1] : trim($cols[1]);

		if ($this->config->search !== false) {
			$params = [
				'prepare' => <<<SQL
				SELECT t.{$valueCol}, t.{$textCol}, COUNT(*) OVER() AS __filtered FROM ({$sql}) AS t WHERE t.{$textCol} ILIKE :search LIMIT :limit OFFSET :offset
				SQL,
				'params' => [':search', ':limit', ':offset'],
				':search' => '%' . $this->config->search . '%',
				':limit' => $this->config->limit,
				':offset' => $this->config->offset,
				'pdoTypes' => [PDO::PARAM_STR, PDO::PARAM_INT, PDO::PARAM_INT]
			];
		} else {
			$params = [
				'prepare' => <<<SQL
				SELECT t.{$valueCol}, t.{$textCol}, COUNT(*) OVER() AS __filtered FROM ({$sql}) AS t LIMIT :limit OFFSET :offset
				SQL,
				'params' => [':limit', ':offset'],
				':limit' => $this->config->limit,
				':offset' => $this->config->offset,
				'pdoTypes' => [PDO::PARAM_INT, PDO::PARAM_INT]
			];
		}

		$data = Db::dbExecute($params);
		$filtered = (int) ($data[0]['__filtered'] ?? 0);
		$total = $this->config->search !== false ? $this->countTotal($sql) : $filtered;

		return (object) [
			'scenario' => $this->config->scenario,
			'total' => $total,
			'filtered' => $filtered,
			'limit' => $this->config->limit,
			'offset' => $this->config->offset,
			'data' => array_map(fn($row) => ['value' => $row[$valueCol], 'text' => $row[$textCol]], $data)
		];
	}

	/**
	 * Static optgroup scenario — text only (text = value).
	 * Iterates opt groups, filters by search (stripos), paginates per group.
	 * Resolves label via Lang. Skips empty groups after filtering.
	 *
	 * https://14.6.83.14/opus/index.php?api=asyncselect&app=demo&event=demoSelectOptText
	 * https://14.6.83.14/opus/index.php?api=asyncselect&app=demo&event=demoSelectOptText&search=kow
	 */
	private function optText(): object
	{
		$total = 0;
		$filtered = 0;
		$data = [];

		foreach ($this->config->async->opt as $group) {
			$texts = $group->text;
			$total += count($texts);

			// Filter
			if ($this->config->search !== false) {
				$texts = array_values(array_filter(
					$texts,
					fn($text) => stripos($text, $this->config->search) !== false
				));
			}

			$filtered += count($texts);

			// Paging
			$texts = array_slice($texts, $this->config->offset, $this->config->limit);

			if (!empty($texts)) {
				$data[] = [
					'label' => Lang::getInstance()->get($group->label),
					'options' => array_map(fn($text) => ['value' => $text, 'text' => $text], $texts)
				];
			}
		}

		return (object) [
			'scenario' => $this->config->scenario,
			'total' => $total,
			'filtered' => $filtered,
			'limit' => $this->config->limit,
			'offset' => $this->config->offset,
			'data' => $data
		];
	}

	/**
	 * Static optgroup scenario — text + separate value array.
	 * Iterates opt groups, filters by text (stripos), keeps value in sync, paginates per group.
	 * Resolves label via Lang. Skips empty groups after filtering.
	 *
	 * https://14.6.83.14/opus/index.php?api=asyncselect&app=demo&event=demoSelectOptValue
	 * https://14.6.83.14/opus/index.php?api=asyncselect&app=demo&event=demoSelectOptValue&search=owa
	 */
	private function optValue(): object
	{
		$total = 0;
		$filtered = 0;
		$data = [];

		foreach ($this->config->async->opt as $group) {
			$texts = $group->text;
			$values = $group->value;
			$total += count($texts);

			// Filter
			if ($this->config->search !== false) {
				$keys = array_keys(array_filter(
					$texts,
					fn($text) => stripos($text, $this->config->search) !== false
				));
				$texts = array_values(array_intersect_key($texts, array_flip($keys)));
				$values = array_values(array_intersect_key($values, array_flip($keys)));
			}

			$filtered += count($texts);

			// Paging
			$texts = array_slice($texts, $this->config->offset, $this->config->limit);
			$values = array_slice($values, $this->config->offset, $this->config->limit);

			if (!empty($texts)) {
				$data[] = [
					'label' => Lang::getInstance()->get($group->label),
					'options' => array_map(
						fn($t, $v) => ['value' => $v, 'text' => $t],
						$texts,
						$values
					)
				];
			}
		}

		return (object) [
			'scenario' => $this->config->scenario,
			'total' => $total,
			'filtered' => $filtered,
			'limit' => $this->config->limit,
			'offset' => $this->config->offset,
			'data' => $data
		];
	}

	/**
	 * SQL optgroup scenario — single column (text = value).
	 * Iterates opt groups, wraps each SQL in subquery, filters by alias ILIKE, paginates.
	 * Resolves label via Lang. Skips empty groups after filtering.
	 *
	 * https://14.6.83.14/opus/index.php?api=asyncselect&app=demo&event=demoSelectOptTextSql
	 * https://14.6.83.14/opus/index.php?api=asyncselect&app=demo&event=demoSelectOptTextSql&search=nna
	 */
	private function optTextSql(): object
	{
		$total = 0;
		$filtered = 0;
		$data = [];

		foreach ($this->config->async->opt as $group) {
			$sql = rtrim(trim($group->text), ';');

			preg_match('/SELECT\s+(.+?)\s+FROM/i', $sql, $m);
			$col = trim($m[1]);
			$alias = preg_match('/\bAS\s+(\w+)\s*$/i', $col, $a) ? $a[1] : $col;

			// Filter + Paging
			if ($this->config->search !== false) {
				$params = [
					'prepare' => <<<SQL
					SELECT t.{$alias}, COUNT(*) OVER() AS __filtered FROM ({$sql}) AS t WHERE t.{$alias} ILIKE :search LIMIT :limit OFFSET :offset
					SQL,
					'params' => [':search', ':limit', ':offset'],
					':search' => '%' . $this->config->search . '%',
					':limit' => $this->config->limit,
					':offset' => $this->config->offset,
					'pdoTypes' => [PDO::PARAM_STR, PDO::PARAM_INT, PDO::PARAM_INT]
				];
			} else {
				$params = [
					'prepare' => <<<SQL
					SELECT t.{$alias}, COUNT(*) OVER() AS __filtered FROM ({$sql}) AS t LIMIT :limit OFFSET :offset
					SQL,
					'params' => [':limit', ':offset'],
					':limit' => $this->config->limit,
					':offset' => $this->config->offset,
					'pdoTypes' => [PDO::PARAM_INT, PDO::PARAM_INT]
				];
			}

			$rows = Db::dbExecute($params);
			$groupFiltered = (int) ($rows[0]['__filtered'] ?? 0);
			$groupTotal = $this->config->search !== false ? $this->countTotal($sql) : $groupFiltered;
			$total += $groupTotal;
			$filtered += $groupFiltered;

			if (!empty($rows)) {
				$data[] = [
					'label' => Lang::getInstance()->get($group->label),
					'options' => array_map(fn($row) => ['value' => $row[$alias], 'text' => $row[$alias]], $rows)
				];
			}
		}

		return (object) [
			'scenario' => $this->config->scenario,
			'total' => $total,
			'filtered' => $filtered,
			'limit' => $this->config->limit,
			'offset' => $this->config->offset,
			'data' => $data
		];
	}

	/**
	 * SQL optgroup scenario — two columns (first = value, second = text).
	 * Iterates opt groups, wraps each SQL in subquery, filters by text alias ILIKE, paginates.
	 * Resolves label via Lang. Skips empty groups after filtering.
	 *
	 * https://14.6.83.14/opus/index.php?api=asyncselect&app=demo&event=demoSelectOptValueSql
	 * https://14.6.83.14/opus/index.php?api=asyncselect&app=demo&event=demoSelectOptValueSql&search=nna
	 */
	private function optValueSql(): object
	{
		$total = 0;
		$filtered = 0;
		$data = [];

		foreach ($this->config->async->opt as $group) {
			$sql = rtrim(trim($group->text), ';');

			preg_match('/SELECT\s+(.+?)\s+FROM/i', $sql, $m);
			$cols = $this->splitColumns($m[1]);
			$valueCol = preg_match('/\bAS\s+(\w+)\s*$/i', $cols[0], $a) ? $a[1] : trim($cols[0]);
			$textCol = preg_match('/\bAS\s+(\w+)\s*$/i', $cols[1], $a) ? $a[1] : trim($cols[1]);

			// Filter + Paging
			if ($this->config->search !== false) {
				$params = [
					'prepare' => <<<SQL
					SELECT t.{$valueCol}, t.{$textCol}, COUNT(*) OVER() AS __filtered FROM ({$sql}) AS t WHERE t.{$textCol} ILIKE :search LIMIT :limit OFFSET :offset
					SQL,
					'params' => [':search', ':limit', ':offset'],
					':search' => '%' . $this->config->search . '%',
					':limit' => $this->config->limit,
					':offset' => $this->config->offset,
					'pdoTypes' => [PDO::PARAM_STR, PDO::PARAM_INT, PDO::PARAM_INT]
				];
			} else {
				$params = [
					'prepare' => <<<SQL
					SELECT t.{$valueCol}, t.{$textCol}, COUNT(*) OVER() AS __filtered FROM ({$sql}) AS t LIMIT :limit OFFSET :offset
					SQL,
					'params' => [':limit', ':offset'],
					':limit' => $this->config->limit,
					':offset' => $this->config->offset,
					'pdoTypes' => [PDO::PARAM_INT, PDO::PARAM_INT]
				];
			}

			$rows = Db::dbExecute($params);
			$groupFiltered = (int) ($rows[0]['__filtered'] ?? 0);
			$groupTotal = $this->config->search !== false ? $this->countTotal($sql) : $groupFiltered;
			$total += $groupTotal;
			$filtered += $groupFiltered;

			if (!empty($rows)) {
				$data[] = [
					'label' => Lang::getInstance()->get($group->label),
					'options' => array_map(fn($row) => ['value' => $row[$valueCol], 'text' => $row[$textCol]], $rows)
				];
			}
		}

		return (object) [
			'scenario' => $this->config->scenario,
			'total' => $total,
			'filtered' => $filtered,
			'limit' => $this->config->limit,
			'offset' => $this->config->offset,
			'data' => $data
		];
	}

	/**
	 * Splits SQL column list by commas at top level (ignores commas inside parentheses).
	 */
	private function splitColumns(string $columnList): array
	{
		$cols = [];
		$depth = 0;
		$current = '';

		for ($i = 0, $len = strlen($columnList); $i < $len; $i++) {
			$ch = $columnList[$i];
			if ($ch === '(') $depth++;
			elseif ($ch === ')') $depth--;
			elseif ($ch === ',' && $depth === 0) {
				$cols[] = trim($current);
				$current = '';
				continue;
			}
			$current .= $ch;
		}

		$cols[] = trim($current);
		return $cols;
	}

	/**
	 * Returns total row count for SQL (without filter). Used only when search is active.
	 */
	private function countTotal(string $sql): int
	{
		$result = Db::dbExecute([
			'prepare' => <<<SQL
			SELECT COUNT(*) AS __total FROM ({$sql}) AS t
			SQL,
			'params' => [],
			'pdoTypes' => []
		]);

		return (int) ($result[0]['__total'] ?? 0);
	}
}
