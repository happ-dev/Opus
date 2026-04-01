<?php

/**
 * @Project: Opus
 * @Version: 1.0
 * @Author: Tomasz Ulazowski
 * @Date:   2026-03-28 20:32:57
 * @Last Modified by:   Tomasz Ulazowski
 * @Last Modified time: 2026-03-28 20:34:50
 **/

namespace Opus\html\form;

trait TraitValidHtmlTags
{

	const HTML_TAGS_REQUIRING_CLOSING = [
		// Document structure
		'html',
		'head',
		'body',
		'title',

		// Content sectioning
		'address',
		'article',
		'aside',
		'footer',
		'header',
		'h1',
		'h2',
		'h3',
		'h4',
		'h5',
		'h6',
		'main',
		'nav',
		'section',

		// Text content
		'blockquote',
		'dd',
		'div',
		'dl',
		'dt',
		'figcaption',
		'figure',
		'li',
		'ol',
		'p',
		'pre',
		'ul',

		// Inline text semantics
		'a',
		'abbr',
		'b',
		'bdi',
		'bdo',
		'cite',
		'code',
		'data',
		'dfn',
		'em',
		'i',
		'kbd',
		'mark',
		'q',
		'rp',
		'rt',
		'ruby',
		's',
		'samp',
		'small',
		'span',
		'strong',
		'sub',
		'sup',
		'time',
		'u',
		'var',

		// Image and multimedia
		'audio',
		'video',
		'picture',

		// Embedded content
		'iframe',
		'object',

		// Scripting
		'canvas',
		'noscript',
		'script',

		// Demarcating edits
		'del',
		'ins',

		// Table content
		'caption',
		'col',
		'colgroup',
		'table',
		'tbody',
		'td',
		'tfoot',
		'th',
		'thead',
		'tr',

		// Forms
		'button',
		'datalist',
		'fieldset',
		'form',
		'label',
		'legend',
		'meter',
		'optgroup',
		'option',
		'output',
		'progress',
		'select',
		'textarea',

		// Interactive elements
		'details',
		'dialog',
		'menu',
		'summary',

		// Web Components
		'slot',
		'template'
	];

	const HTML_TAGS_NOT_REQUIRING_CLOSING = [
		// Void elements (cannot have content)
		'area',
		'base',
		'br',
		'col',
		'embed',
		'hr',
		'img',
		'input',
		'link',
		'meta',
		'param',
		'source',
		'track',
		'wbr',

		// Self-closing in HTML5
		'command',
		'keygen',
		'menuitem'
	];

	const HTML_BOOLEAN_ATTRIBUTES = [
		'allowfullscreen',
		'allowpaymentrequest',
		'async',
		'autofocus',
		'autoplay',
		'checked',
		'controls',
		'default',
		'defer',
		'disabled',
		'formnovalidate',
		'hidden',
		'ismap',
		'itemscope',
		'loop',
		'multiple',
		'muted',
		'nomodule',
		'novalidate',
		'open',
		'playsinline',
		'readonly',
		'required',
		'reversed',
		'selected',
		'truespeed'
	];

	const HTML_VALID_FORM_ATTRIBUTES = [
		'accept-charset',
		'action',
		'autocomplete',
		'enctype',
		'method',
		'name',
		'novalidate',
		'target',
		'class',
		'id',
		'style',
		'data-*',
		'onsubmit',
		'onreset'
	];

	const HTML_INPUT_TYPES = [
		'button',			// A push button with no default behavior
		'checkbox',			// A check box allowing single values to be selected/deselected
		'color',			// A control for specifying a color
		'date',				// A control for entering a date
		'datetime-local',	// A control for entering a date and time, with no time zone
		'email',			// A field for editing an email address
		'file',				// A control that lets the user select a file
		'hidden',			// A control that is not displayed but whose value is submitted to the server
		'image',			// A graphical submit button
		'month',			// A control for entering a month and year
		'number',			// A control for entering a number
		'password',			// A single-line text field whose value is obscured
		'radio',			// A radio button, allowing a single value to be selected out of multiple choices
		'range',			// A control for entering a number whose exact value is not important
		'reset',			// A button that resets the contents of the form to default values
		'search',			// A single-line text field for entering search strings
		'submit',			// A button that submits the form
		'tel',				// A control for entering a telephone number
		'text',				// A single-line text field (default)
		'time',				// A control for entering a time value with no time zone
		'url',				// A field for entering a URL
		'week'				// A control for entering a date consisting of a week-year number and a week number
	];
}
