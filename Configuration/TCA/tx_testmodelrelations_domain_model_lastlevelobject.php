<?php
return [
	'ctrl' => [
		'label' => 'name',
		'title' => 'Model Relations LastLevelObject',		
		'tstamp' => 'tstamp',
		'type' => 'field_type',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'crdate',		
		'delete' => 'deleted',		
		'hideAtCopy' => true,
		'prependAtCopy' => ' - Copy',
		'enableColumns' => [
			'disabled' => 'hidden',
			],
		'searchFields' => 'title, property',
	],
	'columns' => [
		'name' => [
			'label' => 'Name',
			'config' => [
				'type' => 'input',
				'max' => 255,
				'eval' => 'trim',
			]
		],
		'field_type' => [
			'label' => 'fieldType',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => [
					['LastLevelObject','RubenSteeb\TestModelRelations\Domain\Model\LastLevelObject'],
				]
			]
		]
	],
	'types' => [
		'0' => [
			'showitem' => 'field_type, name',
		]
	]
];