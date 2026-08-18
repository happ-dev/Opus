<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-08-03 11:57:31
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-08-11 20:17:45
 **/

namespace Opus\apps\demo\src\select;

/**
 * Demo data arrays for OpusSelect component — used by DemoSelect async page.
 *
 * Each static property is a $data array ready for StandardFormElements::selectValue().
 * Covers all OpusSelect variants organized in two regions:
 *
 * #region text (text = value):
 *   - template static (flat / optgroup)
 *   - template SQL (flat / optgroup)
 *   - AJAX static (flat / optgroup)
 *   - AJAX SQL (flat / optgroup)
 *   Each variant in single + multiple mode.
 *
 * #region value (separate text + value):
 *   - template static (flat / optgroup), with default value
 *   - template SQL (flat / optgroup), with default value
 *   - AJAX static (flat / optgroup)
 *   - AJAX SQL (flat / optgroup)
 *   Each variant in single + multiple mode.
 *
 * Template key 'select-opus' => true triggers OpusSelect rendering.
 * Template key 'event' present => AJAX mode (data from asyncSelect API).
 * Template key 'event' absent => static mode (data from DOM <option> elements).
 */
class DemoSelectElements
{
	// #region text
	// Opus select, text from template
	public static array $selectText = [
		'attname' => 'demo-select-text-template',
		'comment' => 'demo.select.tab.preview.text',
		'template' => [
			'text' => ['Kowalska', 'Wiśniewska', 'Dąbrowska', 'Wójcik'],
			'select-opus' => true
		]
	];

	// Opus multiple, text from template
	public static array $multipleText = [
		'attname' => 'demo-multiple-text-template',
		'comment' => 'demo.multiple.tab.preview.text',
		'value' => 'Kowalska',
		'template' => [
			'text' => ['Kowalska', 'Wiśniewska', 'Dąbrowska', 'Wójcik'],
			'select-opus' => true,
			'multiple' => true
		]
	];

	// Opus grouped select text from template
	public static array $selectOptText = [
		'attname' => 'demo-select-text-opt-template',
		'comment' => 'demo.select.tab.preview.text',
		'template' => [
			'select-opus' => true,
			'optgroups' => [
				[
					'label' => 'demo.select.event.opt-1.label',
					'text' => ['Kowalska', 'Wiśniewska', 'Dąbrowska', 'Wójcik']
				],
				[
					'label' => 'demo.select.event.opt-2.label',
					'text' => ['Nowak', 'Zieliński', 'Lewandowski', 'Kamiński']
				]
			]
		]
	];

	// Opus grouped multiple text from template
	public static array $multipleOptText = [
		'attname' => 'demo-multiple-text-opt-template',
		'comment' => 'demo.multiple.tab.preview.text',
		'template' => [
			'select-opus' => true,
			'multiple' => true,
			'optgroups' => [
				[
					'label' => 'demo.select.event.opt-1.label',
					'text' => ['Kowalska', 'Wiśniewska', 'Dąbrowska', 'Wójcik']
				],
				[
					'label' => 'demo.select.event.opt-2.label',
					'text' => ['Nowak', 'Zieliński', 'Lewandowski', 'Kamiński']
				]
			]
		]
	];

	// Opus select text from sql
	public static array $selectTextSql = [
		'attname' => 'demo-select-text-sql',
		'comment' => 'demo.select.tab.preview.text',
		'template' => [
			'text' => <<<SQL
			SELECT CONCAT(lastname, ' ', firstname) AS name FROM demo.payroll;
			SQL,
			'select-opus' => true
		]
	];

	// Opus multiple text from sql
	public static array $multipleTextSql = [
		'attname' => 'demo-multiple-text-sql',
		'comment' => 'demo.multiple.tab.preview.text',
		'template' => [
			'text' => <<<SQL
			SELECT CONCAT(lastname, ' ', firstname) AS name FROM demo.payroll;
			SQL,
			'multiple' => true,
			'select-opus' => true,
			'visible' => true
		]
	];

	// Opus grouped select text from sql
	public static array $selectOptTextSql = [
		'attname' => 'demo-select-text-opt-sql',
		'comment' => 'demo.select.tab.preview.text',
		'template' => [
			'select-opus' => true,
			'optgroups' => [
				[
					'label' => 'demo.select.event.opt-1.label',
					'text' => <<<SQL
					SELECT firstname FROM demo.payroll WHERE id__payroll % 2 = 0;
					SQL
				],
				[
					'label' => 'demo.select.event.opt-2.label',
					'text' => <<<SQL
					SELECT firstname FROM demo.payroll WHERE id__payroll % 2 = 1;;
					SQL,
				]
			]
		]
	];

	// Opus grouped multiple text from sql
	public static array $multipleOptTextSql = [
		'attname' => 'demo-multiple-text-opt-sql',
		'comment' => 'demo.multiple.tab.preview.text',
		'template' => [
			'select-opus' => true,
			'multiple' => true,
			'optgroups' => [
				[
					'label' => 'demo.select.event.opt-1.label',
					'text' => <<<SQL
					SELECT firstname FROM demo.payroll WHERE id__payroll % 2 = 0;
					SQL
				],
				[
					'label' => 'demo.select.event.opt-2.label',
					'text' => <<<SQL
					SELECT firstname FROM demo.payroll WHERE id__payroll % 2 = 1;;
					SQL,
				]
			]
		]
	];

	// Opus select text from ajax
	public static array $selectAjaxText = [
		'attname' => 'demo-select-text-ajax',
		'comment' => 'demo.select.tab.preview.text',
		'template' => [
			'app' => 'demo',
			'event' => 'demoSelectText',
			'select-opus' => true
		]
	];

	// Opus multiple text from ajax
	public static array $multipleAjaxText = [
		'attname' => 'demo-multiple-text-ajax',
		'comment' => 'demo.multiple.tab.preview.text',
		'template' => [
			'app' => 'demo',
			'event' => 'demoSelectText',
			'select-opus' => true,
			'multiple' => true
		]
	];

	// Opus grouped select text from ajax
	public static array $selectAjaxOptText = [
		'attname' => 'demo-select-text-opt-ajax',
		'comment' => 'demo.select.tab.preview.text',
		'template' => [
			'app' => 'demo',
			'event' => 'demoSelectOptText',
			'select-opus' => true
		]
	];

	// Opus grouped multiple text from ajax
	public static array $multipleAjaxOptText = [
		'attname' => 'demo-multiple-text-opt-ajax',
		'comment' => 'demo.multiple.tab.preview.text',
		'template' => [
			'app' => 'demo',
			'event' => 'demoSelectOptText',
			'select-opus' => true,
			'multiple' => true
		]
	];

	// Opus select text from ajax, sql
	public static array $selectAjaxTextSql = [
		'attname' => 'demo-select-text-ajax-sql',
		'comment' => 'demo.select.tab.preview.text',
		'template' => [
			'app' => 'demo',
			'event' => 'demoSelectTextSql',
			'select-opus' => true
		]
	];

	// Opus multiple text from ajax, sql
	public static array $multipleAjaxTextSql = [
		'attname' => 'demo-multiple-text-ajax-sql',
		'comment' => 'demo.multiple.tab.preview.text',
		'template' => [
			'app' => 'demo',
			'event' => 'demoSelectTextSql',
			'select-opus' => true,
			'multiple' => true
		]
	];

	// Opus grouped select text from ajax, sql
	public static array $selectOptAjaxTextSql = [
		'attname' => 'demo-select-text-opt-ajax-sql',
		'comment' => 'demo.select.tab.preview.text',
		'template' => [
			'app' => 'demo',
			'event' => 'demoSelectOptTextSql',
			'select-opus' => true
		]
	];

	// Opus grouped multiple text from ajax, sql
	public static array $multipleOptAjaxTextSql = [
		'attname' => 'demo-multiple-text-opt-ajax-sql',
		'comment' => 'demo.multiple.tab.preview.text',
		'template' => [
			'app' => 'demo',
			'event' => 'demoSelectOptTextSql',
			'select-opus' => true,
			'multiple' => true
		]
	];

	// #endregion text

	// #region value
	// Opus select, value from template
	public static array $selectValue = [
		'attname' => 'demo-select-value-template',
		'comment' => 'demo.select.tab.preview.value',
		'value' => 5,
		'template' => [
			'text' => ['Kowalska', 'Wiśniewska', 'Dąbrowska', 'Wójcik'],
			'value' => ['1', '3', '5', '7'],
			'select-opus' => true
		]
	];

	// Opus select multiple, value from template
	public static array $multipleValue = [
		'attname' => 'demo-multiple-value-template',
		'comment' => 'demo.multiple.tab.preview.value',
		'value' => [5, 7],
		'template' => [
			'text' => ['Kowalska', 'Wiśniewska', 'Dąbrowska', 'Wójcik'],
			'value' => ['1', '3', '5', '7'],
			'multiple' => true,
			'select-opus' => true
		]
	];

	// Opus grouped select value from template
	public static array $selectOptValue = [
		'attname' => 'demo-select-value-opt-template',
		'comment' => 'demo.select.tab.preview.value',
		'template' => [
			'select-opus' => true,
			'optgroups' => [
				[
					'label' => 'demo.select.event.opt-1.label',
					'text' => ['Kowalska', 'Wiśniewska', 'Dąbrowska', 'Wójcik'],
					'value' => ['1', '3', '5', '7']
				],
				[
					'label' => 'demo.select.event.opt-2.label',
					'text' => ['Nowak', 'Zieliński', 'Lewandowski', 'Kamiński'],
					'value' => ['2', '4', '6', '8']
				]
			]
		]
	];

	// Opus grouped multiple value from template
	public static array $multipleOptValue = [
		'attname' => 'demo-multiple-value-opt-template',
		'comment' => 'demo.multiple.tab.preview.value',
		'template' => [
			'select-opus' => true,
			'multiple' => true,
			'optgroups' => [
				[
					'label' => 'demo.select.event.opt-1.label',
					'text' => ['Kowalska', 'Wiśniewska', 'Dąbrowska', 'Wójcik'],
					'value' => ['1', '3', '5', '7']
				],
				[
					'label' => 'demo.select.event.opt-2.label',
					'text' => ['Nowak', 'Zieliński', 'Lewandowski', 'Kamiński'],
					'value' => ['2', '4', '6', '8']
				]
			]
		]
	];

	// Opus select text, value from sql, with default value
	public static array $selectValueSql = [
		'attname' => 'demo-select-value-sql',
		'comment' => 'demo.select.tab.preview.value',
		'value' => 2,
		'template' => [
			'text' => <<<SQL
			SELECT id__payroll, CONCAT(lastname, ' ', firstname) AS name FROM demo.payroll ORDER BY id__payroll ASC;
			SQL,
			'select-opus' => true
		]
	];

	// Opus select multiple text, value from sql
	public static array $multipleValueSql = [
		'attname' => 'demo-multiple-value-sql',
		'comment' => 'demo.multiple.tab.preview.value',
		'value' => [1, 2],
		'template' => [
			'text' => <<<SQL
			SELECT id__payroll, CONCAT(lastname, ' ', firstname) AS name FROM demo.payroll ORDER BY id__payroll ASC;
			SQL,
			'multiple' => true,
			'select-opus' => true
		]
	];

	// Opus grouped select text, value from sql
	public static array $selectOptValueSql = [
		'attname' => 'demo-select-value-opt-sql',
		'comment' => 'demo.select.tab.preview.value',
		'template' => [
			'select-opus' => true,
			'optgroups' => [
				[
					'label' => 'demo.select.event.opt-1.label',
					'text' => <<<SQL
					SELECT id__payroll, firstname FROM demo.payroll WHERE id__payroll % 2 = 0 ORDER BY id__payroll ASC;
					SQL
				],
				[
					'label' => 'demo.select.event.opt-2.label',
					'text' => <<<SQL
					SELECT id__payroll, firstname FROM demo.payroll WHERE id__payroll % 2 = 1 ORDER BY id__payroll ASC;
					SQL,
				]
			]
		]
	];

	// Opus grouped multiple text, value from sql
	public static array $multipleOptValueSql = [
		'attname' => 'demo-multiple-value-opt-sql',
		'comment' => 'demo.multiple.tab.preview.value',
		'template' => [
			'select-opus' => true,
			'multiple' => true,
			'optgroups' => [
				[
					'label' => 'demo.select.event.opt-1.label',
					'text' => <<<SQL
					SELECT id__payroll, firstname FROM demo.payroll WHERE id__payroll % 2 = 0 ORDER BY id__payroll ASC;
					SQL
				],
				[
					'label' => 'demo.select.event.opt-2.label',
					'text' => <<<SQL
					SELECT id__payroll, firstname FROM demo.payroll WHERE id__payroll % 2 = 1 ORDER BY id__payroll ASC;
					SQL,
				]
			]
		]
	];

	// Opus select text, value from ajax
	public static array $selectAjaxValue = [
		'attname' => 'demo-select-value-ajax',
		'comment' => 'demo.select.tab.preview.value',
		'template' => [
			'app' => 'demo',
			'event' => 'demoSelectValue',
			'select-opus' => true
		]
	];

	// Opus multiple text, value from ajax
	public static array $multipleAjaxValue = [
		'attname' => 'demo-multiple-value-ajax',
		'comment' => 'demo.multiple.tab.preview.value',
		'template' => [
			'app' => 'demo',
			'event' => 'demoSelectValue',
			'select-opus' => true,
			'multiple' => true,
			'visible' => true
		]
	];

	// Opus grouped select text, value from ajax
	public static array $selectAjaxOptValue = [
		'attname' => 'demo-select-value-opt-ajax',
		'comment' => 'demo.select.tab.preview.value',
		'template' => [
			'app' => 'demo',
			'event' => 'demoSelectOptValue',
			'select-opus' => true
		]
	];

	// Opus grouped multiple text, value from ajax
	public static array $multipleAjaxOptValue = [
		'attname' => 'demo-multiple-value-opt-ajax',
		'comment' => 'demo.multiple.tab.preview.value',
		'template' => [
			'app' => 'demo',
			'event' => 'demoSelectOptValue',
			'select-opus' => true,
			'multiple' => true
		]
	];

	// Opus select text, value from ajax, sql
	public static array $selectAjaxValueSql = [
		'attname' => 'demo-select-text-ajax-sql',
		'comment' => 'demo.select.tab.preview.text',
		'template' => [
			'app' => 'demo',
			'event' => 'demoSelectValueSql',
			'select-opus' => true
		]
	];

	// Opus multiple text, value from ajax, sql
	public static array $multipleAjaxValueSql = [
		'attname' => 'demo-multiple-text-ajax-sql',
		'comment' => 'demo.multiple.tab.preview.text',
		'template' => [
			'app' => 'demo',
			'event' => 'demoSelectValueSql',
			'select-opus' => true,
			'multiple' => true
		]
	];

	// Opus grouped select text, value from ajax, sql
	public static array $selectOptAjaxValueSql = [
		'attname' => 'demo-select-text-opt-ajax-sql',
		'comment' => 'demo.select.tab.preview.text',
		'template' => [
			'app' => 'demo',
			'event' => 'demoSelectOptValueSql',
			'select-opus' => true
		]
	];

	// Opus grouped multiple text, value from ajax, sql
	public static array $multipleOptAjaxValueSql = [
		'attname' => 'demo-multiple-text-opt-ajax-sql',
		'comment' => 'demo.multiple.tab.preview.text',
		'template' => [
			'app' => 'demo',
			'event' => 'demoSelectOptValueSql',
			'select-opus' => true,
			'multiple' => true
		]
	];
	// #endregion value

}
