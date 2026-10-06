/**
 * Check staged source files without silently skipping unavailable tools.
 *
 * @package Suppeth
 * @since Suppeth 0.1.7
 */

export default {
	'*.css': 'stylelint',
	'{functions.php,patterns/**/*.php}': 'composer lint --',
	'{theme.json,styles/**/*.json,patterns/**/*.php,templates/**/*.html,parts/**/*.html}':
		'node bin/validate-theme.mjs',
};
