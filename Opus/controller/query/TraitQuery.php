<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-04-28 20:56:14
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-05-03 15:18:07
 **/

namespace Opus\controller\query;

trait TraitQuery
{
	// Used in Query class
	const MODE_TRANSACTION = 1;
	const MODE_EXECUTE = 2;
	const MODE_PREPARE = 3;
	const MODE_QUERY = 4;
	const MODE_STR = ['CHARACTER', 'DATE', 'TIMESTAMP'];
	const VALID_MODES = [
		self::MODE_TRANSACTION,
		self::MODE_EXECUTE,
		self::MODE_PREPARE,
		self::MODE_QUERY
	];
	const QINPUT_EXEPTIONS_PAGE = 'typep';
	const QINPUT_EXEPTIONS_HASHTAG = 'hashtag';
	const QINPUT_EXEPTIONS_PLUS = 'plus';
	const QINPUT_STRATEGY = ['table', 'insert', 'update', 'select', 'delete'];

	// Used  in QueryValidate class
	const QVALID_CHUNK = ['options' => ['regexp' => '/^[\d]+$/']];
	const QVALID_LIMIT = ['options' => ['regexp' => '/^[\d]+$/']];
	const QVALID_OFFSET = ['options' => ['regexp' => '/^[\d]+$/']];
	const QVALID_TABLE = ['options' => ['regexp' => '/^[a-z_]+\.[a-z_]+$/']];
	const QVALID_COLUMN = ['options' => ['regexp' => '/^[a-zA-z0-9\._]+$/']];
	const QVALID_COLUMN_TYPE = ['options' => [
		'regexp' => '/^(SMALLINT|INTEGER|BIGINT|DECIMAL|NUMERIC|REAL|DOUBLE PRECISION|SERIAL|BIGSERIAL|SMALLSERIAL|MONEY|CHARACTER|CHARACTER VARYING|VARCHAR|CHAR|TEXT|BYTEA|TIMESTAMP|DATE|TIME|INTERVAL|BOOLEAN|POINT|LINE|LSEG|BOX|PATH|POLYGON|CIRCLE|CIDR|INET|MACADDR|UUID|XML|JSON|JSONB|ARRAY)(?:\s*\(\d+\))?(?:\s+(?:UNIQUE|NOT NULL|NULL))*$/i'
	]];
	const QVALID_WHERE_PARAM = ['options' => ['regexp' => '/^(BETWEEN|LIKE|IN|NOT)$|^(\bNOT LIKE|\bNOT IN)$|^(<|>|=)$|^(<>|>=|<=)$/']];
	const QVALID_LEFT_JOIN_ON = ['options' => ['regexp' => '/^[a-z_]+\.[a-zA-z0-9\._]+\s\=\s+[a-z_]+\.[a-zA-z0-9\._]+$/']];
	const QVALID_WHERE_LEFT = ['options' => ['regexp' => '/^[a-z_]+\.[a-zA-z0-9\._]+$/']];
	const QVALID_SORT_ORDER_BY = ['options' => ['regexp' => '/^(ASC|DESC)$/']];
	const QVALID_DB_CONFIG = ['options' => ['regexp' => '/^[a-z]+$/']];
	const QVALID_FOREIGN_KEY_KEY = ['options' => ['regexp' => '/^(id_to_)+[^_]+[\w\D]+$/']];
	const QVALID_FOREIGN_KEY_ID = ['options' => ['regexp' => '/^(id__)+[^_]+[\w\D]+$/']];
	const QVALID_GRANT_TABLE_USER = ['options' => ['regexp' => '/^[a-zA-z0-9_]+$/']];
	const QVALID_GRANT_TABLE_SEQ = ['options' => ['regexp' => '/^(SELECT|INSERT|UPDATE|DELETE|USAGE|ALL\sPRIVILEGES)$/']];
	const QVALID_MODE = ['options' => ['regexp' => '/^[1-4]{1}$/']];
	const QVALID_COLUMN_STRING_TYPE = ['CHARACTER', 'DATE', 'TIMESTAMP'];
	const QVALID_COLUMN_QUERY_TYPES = ['options' => ['regexp' => '/^(SELECT|INSERT|UPDATE|DELETE|CREATE)$/']];
	const QVALID_INSERT_MODE = [self::MODE_PREPARE, self::MODE_QUERY, self::MODE_TRANSACTION];
	const QVALID_DELETE_MODE = [self::MODE_PREPARE, self::MODE_QUERY, self::MODE_TRANSACTION];
	const QVALID_UPDATE_MODE = [self::MODE_PREPARE, self::MODE_QUERY, self::MODE_TRANSACTION];
	const QVALID_TABLE_MODE = [self::MODE_TRANSACTION, self::MODE_QUERY];
	const QVALID_SELECT_MODE = [self::MODE_EXECUTE, self::MODE_PREPARE, self::MODE_QUERY];
}
