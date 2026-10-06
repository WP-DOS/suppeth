/**
 * Build a reproducible ZIP from an explicit runtime-file allowlist.
 *
 * @package Suppeth
 * @since Suppeth 0.1.7
 */
import { existsSync, lstatSync, readdirSync, readFileSync, mkdirSync, writeFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { zipSync } from 'fflate';

const root = fileURLToPath( new URL( '../', import.meta.url ) );
const directories = [ 'assets', 'parts', 'patterns', 'templates', 'languages', 'styles' ];
const files = [ 'style.css', 'theme.json', 'functions.php', 'readme.txt', 'LICENSE', 'screenshot.png' ];

function visit( relative ) {
	const path = resolve( root, relative );
	if ( ! existsSync( path ) || relative.split( '/' ).some( ( part ) => part.startsWith( '.' ) ) ) {
		return [];
	}
	const stat = lstatSync( path );
	if ( stat.isSymbolicLink() ) {
		throw new Error( `Runtime files must not be symlinks: ${ relative }` );
	}
	return stat.isDirectory()
		? readdirSync( path ).flatMap( ( name ) => visit( `${ relative }/${ name }` ) )
		: [ relative ];
}

export function packageTheme( output = resolve( root, 'dist/suppeth.zip' ) ) {
	const entries = Object.fromEntries( [ ...files, ...directories ].flatMap( visit ).sort().map( ( file ) => [
		`suppeth/${ file }`, [ readFileSync( resolve( root, file ) ), {
			mtime: new Date( 2026, 0, 1, 0, 0, 0 ), os: 3, attrs: 0o100644 << 16,
		} ],
	] ) );
	mkdirSync( dirname( output ), { recursive: true } );
	writeFileSync( output, zipSync( entries, { level: 9 } ) );
	return output;
}

if ( process.argv[ 1 ] && resolve( process.argv[ 1 ] ) === fileURLToPath( import.meta.url ) ) {
	console.log( packageTheme( process.argv[ 2 ] && resolve( process.argv[ 2 ] ) ) );
}
