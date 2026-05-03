## Sample input data configuration for creating SQL query
```php
$tableQuery = [
	'mode' => self::MODE_TRANSACTION,
	'type' => 'table',
	'table' => 'users.users',
	'columns' => [
		[
			'name' => 'id__users',
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
	'foreign_key' => [
		[
			'key' => 'id_to_group',
			'table' => 'users.groups',
			'id' => 'id__groups'
		]
	],
	'drop_table' => 'yes',
	'grant' => [
		[
			'user' => 'app_user',
			'table' => ['SELECT', 'INSERT', 'UPDATE'],
			'sequence' => ['SELECT', 'UPDATE']
		]
	]
];
```
