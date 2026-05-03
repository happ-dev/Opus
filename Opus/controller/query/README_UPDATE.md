## Sample input data configuration for creating SQL query
```php
$updatetQuery = [
	'mode' => self::MODE_QUERY,
	'type' => 'update',
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
	]
];

$updateData = [
	[
		'id__users' => 1,				// id is always needed because the data update depends on it, the WHERE clause in the UPDATE query
		'id_to_group' => 'SELECT id__group FROM groups.groups WHERE gname = \'users\'',
		'login' => 'admin',
		'ulevel' => '8',
		'active' => 'TRUE'
	],
	[
		'id__users' => 2,				// id is always needed because the data update depends on it, the WHERE clause in the UPDATE query
		'id_to_group' => '2',
		'login' => 'user',
		'ulevel' => '3',
		'active' => 'FALSE'
	]
];
```
