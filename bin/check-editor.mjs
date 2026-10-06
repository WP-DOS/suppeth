/**
 * Verify PHP-generated patterns with the real Site Editor parser in wp-env.
 *
 * @package Suppeth
 * @since Suppeth 0.1.7
 */
import { readFileSync } from 'node:fs';
import { spawnSync } from 'node:child_process';

const url = new URL( process.argv[ 2 ] ?? 'http://localhost:8899' );
if ( url.protocol !== 'http:' || ! [ 'localhost', '127.0.0.1' ].includes( url.hostname ) || url.username || url.password ) {
	throw new Error( 'Editor regression checks require a disposable local sandbox.' );
}
const response = await fetch( url.origin, { signal: AbortSignal.timeout( 10000 ), redirect: 'manual' } );
if ( ! response.ok ) {
	throw new Error( 'Start the local preview with npm run env:setup before testing.' );
}
const session = `suppeth-pattern-validation-${ process.pid }`;
function browser( args, input, required = true ) {
	const result = spawnSync( 'npx', [ '--yes', 'agent-browser@0.38.2', '--session', session, ...args ], {
		encoding: 'utf8', input, timeout: 90000,
	} );
	if ( result.stderr ) process.stderr.write( result.stderr );
	if ( required && ( result.error || result.status !== 0 ) ) {
		throw result.error ?? new Error( result.stdout || 'Browser validation failed.' );
	}
	return result.stdout?.trim();
}
try {
	browser( [ 'open', url.origin + '/wp-admin/site-editor.php' ] );
	if ( browser( [ 'eval', "Boolean(document.querySelector('#loginform'))" ] ) === 'true' ) {
		// Only the disposable wp-env account is used here.
		browser( [ 'fill', '#user_login', 'admin' ] );
		browser( [ 'fill', '#user_pass', 'password' ] );
		browser( [ 'click', '#wp-submit' ] );
	}
	browser( [ 'wait', '--fn', 'Boolean(window.wp?.blocks && window.wp?.apiFetch)' ] );
	const result = JSON.parse( browser( [ 'eval', '--stdin' ],
		readFileSync( new URL( '../tests/pattern-validation.browser.js', import.meta.url ), 'utf8' ) ) );
	if ( result.passed !== true ) throw new Error( 'Missing editor validation success receipt.' );
	console.log( JSON.stringify( result, null, 2 ) );
} finally {
	browser( [ 'close' ], undefined, false );
}
