/**
 * Small regressions for safe packaging and local-only test entry points.
 *
 * @package Suppeth
 * @since Suppeth 0.1.7
 */
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { mkdtempSync, readFileSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { spawnSync } from 'node:child_process';
import { unzipSync } from 'fflate';
import { packageTheme } from '../bin/package-theme.mjs';

const root = new URL( '../', import.meta.url );

test( 'ZIP is reproducible and contains runtime files only', () => {
	const directory = mkdtempSync( join( tmpdir(), 'suppeth-zip-' ) );
	try {
		const first = readFileSync( packageTheme( join( directory, 'first.zip' ) ) );
		assert.deepEqual( first, readFileSync( packageTheme( join( directory, 'second.zip' ) ) ) );
		const crossTimezone = spawnSync( process.execPath,
			[ new URL( '../bin/package-theme.mjs', import.meta.url ).pathname, join( directory, 'timezone.zip' ) ],
			{ env: { ...process.env, TZ: 'Pacific/Honolulu' }, encoding: 'utf8' } );
		assert.equal( crossTimezone.status, 0, crossTimezone.stderr );
		assert.deepEqual( first, readFileSync( join( directory, 'timezone.zip' ) ) );
		const entries = unzipSync( first );
		for ( const name of [ 'style.css', 'theme.json', 'functions.php', 'LICENSE', 'templates/404.html', 'parts/header.html' ] ) {
			assert.ok( entries[ `suppeth/${ name }` ], name );
		}
		for ( const name of Object.keys( entries ) ) {
			assert.match( name, /^suppeth\/(?:assets\/|parts\/|patterns\/|templates\/|languages\/|styles\/|style\.css$|theme\.json$|functions\.php$|readme\.txt$|LICENSE$|screenshot\.png$)/ );
			assert.ok( ! name.split( '/' ).some( ( part ) => part.startsWith( '.' ) ) );
		}
		const css = Buffer.from( entries[ 'suppeth/style.css' ] ).toString();
		const readme = Buffer.from( entries[ 'suppeth/readme.txt' ] ).toString();
		assert.equal( /^Version: (.+)$/m.exec( css )[ 1 ], /^Version: (.+)$/m.exec( readme )[ 1 ] );
	} finally {
		rmSync( directory, { recursive: true, force: true } );
	}
} );

test( 'editor checks reject hosted sites before opening a browser', () => {
	const result = spawnSync( process.execPath, [ new URL( '../bin/check-editor.mjs', import.meta.url ).pathname, 'https://example.com' ], { encoding: 'utf8' } );
	assert.notEqual( result.status, 0 );
	assert.match( result.stderr, /require a disposable local sandbox/ );
} );

test( 'fixture seeder rejects execution outside local wp-env WP-CLI', () => {
	const result = spawnSync( 'php', [ new URL( 'scripts/seed-wp-env.php', root ).pathname ], { encoding: 'utf8' } );
	assert.notEqual( result.status, 0 );
	assert.match( result.stderr, /requires the local Suppeth wp-env preview/ );
} );
