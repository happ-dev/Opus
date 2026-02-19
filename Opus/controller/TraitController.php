<?php

/**
 * @Project: Opus
 * @Version: 0.9
 * @Author: Tomasz Ulazowski
 * @Date:   2026-02-19 12:37:37
 * @Last Modified by:   Tomasz Ulazowski
 * @Last Modified time: 2026-02-19 16:55:08
 **/

namespace Opus\controller;

trait TraitController
{
	// JavaScript regex patterns for comment removal
	const JS_PATTERN_COMMENTS = [
		'/\/\*[\s\S]*?\*\//',	// Remove block comments
		'/\/\/.*$/',			// Remove line comments
		'/^\s*\n/m',			// Remove empty lines
	];
	const JS_REPLACEMENT_COMMENTS = ['', '', ''];
	const JS_OPUS_LIBS = [
		'vendor/Opus/js/global.js',
		'vendor/Opus/js/dt.js',
		'vendor/Opus/js/modal.js',
		'vendor/Opus/js/chart.js',
		'vendor/Opus/js/injecthtml.js',
		'vendor/Opus/js/offcanvas.js',
		'vendor/Opus/js/multiple.js',
		'vendor/Opus/js/single.js',
		'vendor/Opus/js/collapse.js'
	];
}
