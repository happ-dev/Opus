## Sample input data configuration for creating SQL query
```php
$deleteQuery = [
	'mode' => self::MODE_TRANSACTION,
	'type' => 'delete',
	'table' => 'users.users',
	'columns' => [
		[
			'name' => 'id__users',
			'type' => 'SERIAL'
		]
	]
];

$deleteData = [
	[
		'id__users' => 1
	],
	[
		'id__users' => 2
	]
];
```
