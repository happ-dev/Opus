## Sample input data configuration for creating SQL query
```php
$selectQuery = [
	'mode' => self::MODE_QUERY,
	'type' => 'select',
	'table' => 'users.users',
	'distinct_on' => 'yes',
	'columns' => [
		[
			'name' => 'id__users',		// column id is always needed!!!
			'type' => 'SERIAL'
		],
		[
			'name' => 'id_to_group',
			'type' => 'INTEGER NOT NULL'
		],
		[
			'name' => 'login',
			'type' => 'CHARACTER VARYING (10) UNIQUE NOT NULL'
		],
		[
			'name' => 'ulevel',
			'type' => 'SMALLINT NOT NULL'
		],
		[
			'name' => 'active',
			'type' => 'BOOLEAN NOT NULL'
		]
	],
	'other_columns_name' => ['lastname', 'firstname', 'email'],
	'left_join' => [
		[
			'table' => 'groups.groups',
			'column' => ['gname', 'glevel'],
			'on' => 'users.ulevel = groups.glevel'
		]
	],
	'where' => [
		[
			'left' => 'users.id__users',
			'param' => '=',
			'right' => '1'
		]
	],
	'order_by' => [
		[
			'column' => 'ulevel',
			'sort' => 'DESC'
		],
		[
			'column' => 'login',
			'sort' => 'ASC'
		]
	],
	'group_by' => ['users.id__users', 'groups.gname', 'groups.glevel'],
	'limit' => 253,
	'offset' => 10
];
```
