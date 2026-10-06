#!/usr/bin/env node
/**
 * Validates what PHPCS can't: theme.json and the style variations against the
 * theme.json schema, the pattern headers, and the block markup in patterns,
 * templates, and template parts.
 *
 * Adapted from WordPress/Ipsum (GPL-2.0-or-later):
 * https://github.com/WordPress/ipsum/blob/trunk/bin/validate-theme.mjs
 *
 * @package Suppeth
 * @since Suppeth 0.1.7
 *
 * Run it with `npm run lint:theme`. Given file paths, as the pre-commit hook
 * does, it still reads the whole theme but only reports problems in those
 * files.
 */

import { existsSync, readdirSync, readFileSync } from 'node:fs';
import { basename, join, relative, resolve, sep } from 'node:path';
import { fileURLToPath } from 'node:url';
import Ajv from 'ajv';

const THEME_DIR = fileURLToPath( new URL( '..', import.meta.url ) );

/**
 * Pattern header keys that core reads, from WP_Theme::get_block_patterns().
 */
const PATTERN_HEADERS = [
	'title',
	'slug',
	'description',
	'viewport width',
	'inserter',
	'categories',
	'keywords',
	'block types',
	'post types',
	'template types',
];

/**
 * Pattern categories that core registers, from
 * _register_core_block_patterns_and_categories() in WordPress 7.1.
 */
const PATTERN_CATEGORIES = [
	'banner',
	'buttons',
	'columns',
	'text',
	'query',
	'featured',
	'call-to-action',
	'team',
	'testimonials',
	'services',
	'contact',
	'about',
	'portfolio',
	'gallery',
	'media',
	'videos',
	'audio',
	'posts',
	'footer',
	'header',
	'navigation',
];

/**
 * Block attributes that hold text visitors see. The block name in
 * metadata is checked separately, since editors see it in the List View.
 */
const TEXT_ATTRIBUTES = [
	'ariaLabel',
	'buttonText',
	'byline',
	'content',
	'label',
	'moreText',
	'placeholder',
	'prefix',
	'suffix',
];

/**
 * Block delimiters, as matched by WP_Block_Parser and
 * @wordpress/block-serialization-default-parser.
 */
const BLOCK_DELIMITER =
	/<!--\s+(\/)?wp:([a-z][a-z0-9_-]*\/)?([a-z][a-z0-9_-]*)\s+({(?:(?=([^}]+|}+(?=})|(?!}\s+\/?-->)[^])*)\5|[^]*?)}\s+)?(\/)?-->/g;

const PHP_SPAN = /<\?(?:php|=)[\s\S]*?(?:\?>|$)/g;

const BLOCK_GAP_POINTER = /^\/styles\/blocks\/[^/]+\/spacing\/blockGap(?=\/|$)/;

const COMBINATOR_ERROR = /^must match (exactly one|a) schema in (oneOf|anyOf)$/;

/**
 * The schema for the next release. "$schema" only drives editor autocomplete,
 * so pointing at trunk is allowed alongside the "Requires at least" version.
 * The validation below stays on that version either way, so a property that
 * trunk offers but the theme's minimum WordPress doesn't know is still caught.
 */
const TRUNK_SCHEMA = 'https://schemas.wp.org/trunk/theme.json';

// Keep the two existing shared-part slugs valid for saved Site Editor content.
const LEGACY_PATTERN_SLUGS = {
	'hidden-default-footer': 'suppeth/default-footer',
	'hidden-default-sidebar': 'suppeth/default-sidebar',
};

const problems = [];

/**
 * The files given on the command line, relative to the theme root. When there
 * are none, every file is reported on.
 */
const reportedFiles = new Set(
	process.argv
		.slice( 2 )
		.map( ( file ) =>
			relative( THEME_DIR, resolve( file ) ).split( sep ).join( '/' )
		)
);

function isReported( file ) {
	return ! reportedFiles.size || reportedFiles.has( file );
}

/**
 * Records a problem to print at the end, if its file is reported on.
 *
 * @param {string} file    Path relative to the theme root.
 * @param {number} line    Line number.
 * @param {string} rule    Rule name.
 * @param {string} message What is wrong.
 */
function report( file, line, rule, message ) {
	if ( isReported( file ) ) {
		problems.push( { file, line, rule, message } );
	}
}

function read( file ) {
	return readFileSync( join( THEME_DIR, file ), 'utf8' );
}

function listFiles( dir, extension, recursive = false ) {
	if ( ! existsSync( join( THEME_DIR, dir ) ) ) {
		return [];
	}

	return readdirSync( join( THEME_DIR, dir ), { recursive } )
		.filter( ( file ) => file.endsWith( extension ) )
		.map( ( file ) => `${ dir }/${ file.split( sep ).join( '/' ) }` )
		.sort();
}

function lineAt( text, index ) {
	return text.slice( 0, index ).split( '\n' ).length;
}

function escapeRegExp( text ) {
	return text.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' );
}

function hasLetters( text ) {
	return /\p{L}/u.test( text.replace( /&[#\w]+;/g, '' ) );
}

function quote( text ) {
	const trimmed = text.trim().replace( /\s+/g, ' ' );
	return JSON.stringify(
		trimmed.length > 60 ? `${ trimmed.slice( 0, 57 ) }...` : trimmed
	);
}

/**
 * Reads a header value the way get_file_data() does.
 *
 * @param {string} source File contents.
 * @param {string} name   Header name.
 * @return {string} The value, or an empty string.
 */
function getFileHeader( source, name ) {
	const match = new RegExp(
		`^(?:[ \\t]*<\\?php)?[ \\t/*#@]*${ escapeRegExp( name ) }:(.*)$`,
		'mi'
	).exec( source.slice( 0, 8192 ) );

	return match ? match[ 1 ].replace( /\s*(?:\*\/|\?>).*/, '' ).trim() : '';
}

/**
 * Replaces each PHP span, keeping track of the line breaks it removes so
 * problems still point at the right line.
 *
 * @param {string} source      File contents.
 * @param {string} replacement Text to put in place of each span.
 * @return {{text: string, line: Function}} The result, and a function that
 *         maps an index in it to a line in the source.
 */
function stripPhp( source, replacement ) {
	const removed = [];
	let shift = 0;
	let lines = 0;

	const text = source.replace( PHP_SPAN, ( php, offset ) => {
		lines += php.split( '\n' ).length - 1;
		removed.push( { end: offset - shift + replacement.length, lines } );
		shift += php.length - replacement.length;
		return replacement;
	} );

	return {
		text,
		line( index ) {
			const before = removed.findLast( ( { end } ) => end <= index );
			return lineAt( text, index ) + ( before?.lines ?? 0 );
		},
	};
}

function escapePointer( key ) {
	return key.replace( /~/g, '~0' ).replace( /\//g, '~1' );
}

function pointerKeys( pointer ) {
	return pointer
		.split( '/' )
		.slice( 1 )
		.map( ( key ) => key.replace( /~1/g, '/' ).replace( /~0/g, '~' ) );
}

function valueAt( data, pointer ) {
	return pointerKeys( pointer ).reduce(
		( value, key ) => value?.[ key ],
		data
	);
}

function describePath( pointer ) {
	return pointerKeys( pointer ).reduce( ( path, key ) => {
		if ( /^\d+$/.test( key ) ) {
			return `${ path }[${ key }]`;
		}
		return path ? `${ path }.${ key }` : key;
	}, '' );
}

function describeValue( value ) {
	if ( value === undefined ) {
		return 'nothing';
	}
	if ( Array.isArray( value ) ) {
		return 'an array';
	}
	if ( value !== null && typeof value === 'object' ) {
		return 'an object';
	}
	return typeof value === 'string' ? quote( value ) : String( value );
}

/**
 * Maps each JSON pointer in a valid JSON document to the line of its key, or
 * of its value for array items.
 *
 * @param {string} source JSON that parses.
 * @return {Map<string, number>} Lines by pointer.
 */
function mapPointerLines( source ) {
	const lines = new Map();
	let index = 0;
	let line = 1;

	const skipWhitespace = () => {
		while ( /\s/.test( source[ index ] ?? '' ) ) {
			if ( source[ index ] === '\n' ) {
				line++;
			}
			index++;
		}
	};

	const readString = () => {
		const start = index++;
		while ( source[ index ] !== '"' ) {
			index += source[ index ] === '\\' ? 2 : 1;
		}
		return JSON.parse( source.slice( start, ++index ) );
	};

	const readValue = ( pointer ) => {
		skipWhitespace();
		if ( ! lines.has( pointer ) ) {
			lines.set( pointer, line );
		}

		const char = source[ index ];
		if ( char === '{' || char === '[' ) {
			const isObject = char === '{';
			const end = isObject ? '}' : ']';
			let item = 0;
			index++;
			skipWhitespace();
			while ( source[ index ] !== end ) {
				if ( isObject ) {
					const key = escapePointer( readString() );
					const child = `${ pointer }/${ key }`;
					lines.set( child, line );
					skipWhitespace();
					index++; // The colon.
					readValue( child );
				} else {
					readValue( `${ pointer }/${ item++ }` );
				}
				skipWhitespace();
				if ( source[ index ] === ',' ) {
					index++;
					skipWhitespace();
				}
			}
			index++;
		} else if ( char === '"' ) {
			readString();
		} else {
			while (
				index < source.length &&
				! /[\s,\]}]/.test( source[ index ] )
			) {
				index++;
			}
		}
	};

	readValue( '' );
	return lines;
}

/**
 * Whether a schema error is about a split { top, left } block gap on a block.
 * Core supports those (WP_Theme_JSON::get_layout_styles()), but the schema
 * only allows a string.
 *
 * @param {Object} data    The theme.json data.
 * @param {string} pointer Where the error is.
 * @return {boolean} Whether to skip the error.
 */
function isSplitBlockGap( data, pointer ) {
	const match = BLOCK_GAP_POINTER.exec( pointer );
	if ( ! match ) {
		return false;
	}

	const value = valueAt( data, match[ 0 ] );
	return (
		value !== null &&
		typeof value === 'object' &&
		! Array.isArray( value ) &&
		Object.entries( value ).every(
			( [ side, gap ] ) =>
				[ 'top', 'left' ].includes( side ) && typeof gap === 'string'
		)
	);
}

/**
 * Reports schema errors, one per JSON path.
 *
 * @param {string}              file   Path relative to the theme root.
 * @param {Map<string, number>} lines  Result of mapPointerLines().
 * @param {Object}              data   The parsed file.
 * @param {Object[]}            errors Ajv errors.
 */
function reportSchemaErrors( file, lines, data, errors ) {
	const paths = new Map();

	for ( const error of errors ) {
		let pointer = error.instancePath;
		let message = error.message;

		if ( error.keyword === 'additionalProperties' ) {
			pointer += `/${ escapePointer( error.params.additionalProperty ) }`;
			message = 'is not a valid property';
		} else if ( error.keyword === 'enum' ) {
			const allowed = error.params.allowedValues.map( ( value ) =>
				JSON.stringify( value )
			);
			message = `must be one of ${ allowed.join( ', ' ) }`;
		}

		if ( isSplitBlockGap( data, pointer ) ) {
			continue;
		}

		// Combinator errors only repeat what their branches already said.
		if ( COMBINATOR_ERROR.test( message ) ) {
			continue;
		}

		if ( ! paths.has( pointer ) ) {
			paths.set( pointer, { messages: new Set(), keywords: new Set() } );
		}
		paths.get( pointer ).messages.add( message );
		paths.get( pointer ).keywords.add( error.keyword );
	}

	for ( const [ pointer, { messages, keywords } ] of paths ) {
		const list = [ ...messages ];
		let text = list.join( '; ' );
		if (
			list.length > 1 &&
			list.every( ( item ) => item.startsWith( 'must be ' ) )
		) {
			const types = list.map( ( item ) => item.slice( 8 ) );
			text = `must be ${ types.join( ' or ' ) }`;
		}
		if (
			! keywords.has( 'additionalProperties' ) &&
			! keywords.has( 'required' )
		) {
			text += `, found ${ describeValue( valueAt( data, pointer ) ) }`;
		}

		report(
			file,
			lines.get( pointer ) ?? 1,
			'theme-json-schema',
			`${ describePath( pointer ) || 'The file' } ${ text }.`
		);
	}
}

/**
 * Validates theme.json and the style variations that are reported on.
 *
 * @param {string} requiresAtLeast The theme's minimum WordPress version.
 */
async function validateThemeJson( requiresAtLeast ) {
	const files = [
		'theme.json',
		...listFiles( 'styles', '.json', true ),
	].filter( isReported );

	// Skips the schema download when there's nothing to validate.
	if ( ! files.length ) {
		return;
	}

	const schemaUrl =
		requiresAtLeast &&
		`https://schemas.wp.org/wp/${ requiresAtLeast }/theme.json`;
	let validate;

	if ( schemaUrl ) {
		try {
			const response = await fetch( schemaUrl, {
				signal: AbortSignal.timeout( 30000 ),
			} );
			if ( ! response.ok ) {
				throw new Error( `HTTP ${ response.status }` );
			}
			validate = new Ajv( { strict: false, allErrors: true } ).compile(
				await response.json()
			);
		} catch ( error ) {
			report(
				files[ 0 ],
				1,
				'theme-json-schema',
				`Could not load the schema from ${ schemaUrl }: ${ error.message }`
			);
		}
	}

	for ( const file of files ) {
		const source = read( file );
		let data;

		try {
			data = JSON.parse( source );
		} catch ( error ) {
			const position = /line (\d+)/.exec( error.message );
			report(
				file,
				position ? Number( position[ 1 ] ) : 1,
				'theme-json',
				`Invalid JSON: ${ error.message }`
			);
			continue;
		}

		const lines = mapPointerLines( source );

		if (
			schemaUrl &&
			! [ schemaUrl, TRUNK_SCHEMA ].includes( data.$schema )
		) {
			const found = describeValue( data.$schema );
			report(
				file,
				lines.get( '/$schema' ) ?? 1,
				'theme-json-schema-version',
				`"$schema" should be ${ schemaUrl } to match "Requires at least" in style.css, or ${ TRUNK_SCHEMA }, found ${ found }.`
			);
		}

		if ( data.version !== 3 ) {
			const found = describeValue( data.version );
			report(
				file,
				lines.get( '/version' ) ?? 1,
				'theme-json',
				`"version" should be 3, found ${ found }.`
			);
		}

		if ( validate && ! validate( data ) ) {
			reportSchemaErrors( file, lines, data, validate.errors );
		}
	}
}

/**
 * Validates the pattern file headers.
 *
 * @param {string} slugPrefix The theme's pattern slug prefix.
 * @return {Set<string>} The pattern slugs.
 */
function validatePatternHeaders( slugPrefix ) {
	const slugs = new Map();

	for ( const file of listFiles( 'patterns', '.php' ) ) {
		const source = read( file );
		const docblock = /\/\*\*([\s\S]*?)\*\//.exec( source );

		if ( ! docblock ) {
			report(
				file,
				1,
				'pattern-header',
				'Missing the pattern header comment.'
			);
			continue;
		}

		const firstLine = lineAt( source, docblock.index );
		const headers = new Map();

		docblock[ 1 ].split( '\n' ).forEach( ( text, offset ) => {
			const content = text.replace( /^\s*\*?\s*/, '' );
			const match = /^([A-Za-z][A-Za-z ]*):(.*)$/.exec( content );
			if ( content.startsWith( '@' ) || ! match ) {
				return;
			}

			const key = match[ 1 ].trim().toLowerCase();
			const line = firstLine + offset;
			if ( ! PATTERN_HEADERS.includes( key ) ) {
				report(
					file,
					line,
					'pattern-header',
					`Unknown pattern header "${ match[ 1 ].trim() }".`
				);
			} else if ( headers.has( key ) ) {
				report(
					file,
					line,
					'pattern-header',
					`"${ match[ 1 ].trim() }" is set more than once.`
				);
			} else {
				headers.set( key, { value: match[ 2 ].trim(), line } );
			}
		} );

		for ( const required of [ 'Title', 'Slug' ] ) {
			if ( ! headers.get( required.toLowerCase() )?.value ) {
				report(
					file,
					firstLine,
					'pattern-header',
					`Missing the "${ required }" header.`
				);
			}
		}

		const slug = headers.get( 'slug' );
		if ( slug?.value ) {
			const expected = LEGACY_PATTERN_SLUGS[ basename( file, '.php' ) ] ??
				`${ slugPrefix }/${ basename( file, '.php' ) }`;
			if ( slug.value !== expected ) {
				report(
					file,
					slug.line,
					'pattern-slug',
					`Slug "${ slug.value }" should be "${ expected }" to match the file name.`
				);
			}
			const duplicate = slugs.get( slug.value );
			if ( duplicate ) {
				report(
					file,
					slug.line,
					'pattern-slug',
					`Slug "${ slug.value }" is already used by ${ duplicate }.`
				);
			} else {
				slugs.set( slug.value, file );
			}
		}

		const categories = headers.get( 'categories' );
		for ( const category of ( categories?.value ?? '' ).split( ',' ) ) {
			if (
				category.trim() &&
				! PATTERN_CATEGORIES.includes( category.trim() )
			) {
				report(
					file,
					categories.line,
					'pattern-header',
					`Unknown category "${ category.trim() }". Use a category registered by core.`
				);
			}
		}

		const inserter = headers.get( 'inserter' );
		if ( inserter && ! /^(yes|no|true|false)$/i.test( inserter.value ) ) {
			report(
				file,
				inserter.line,
				'pattern-header',
				`Inserter should be "yes" or "no", found "${ inserter.value }".`
			);
		}

		const viewportWidth = headers.get( 'viewport width' );
		if ( viewportWidth && ! /^\d+$/.test( viewportWidth.value ) ) {
			report(
				file,
				viewportWidth.line,
				'pattern-header',
				`Viewport Width should be a number of pixels, found "${ viewportWidth.value }".`
			);
		}
	}

	return new Set( slugs.keys() );
}

/**
 * Checks the block delimiters and attributes in a file.
 *
 * @param {string}      file         Path relative to the theme root.
 * @param {Object}      markup       Result of stripPhp().
 * @param {string}      slugPrefix   The theme's pattern slug prefix.
 * @param {Set<string>} patternSlugs The theme's pattern slugs.
 */
function checkBlocks( file, markup, slugPrefix, patternSlugs ) {
	const open = [];

	for ( const match of markup.text.matchAll( BLOCK_DELIMITER ) ) {
		const [ , closer, namespace, name, json, , selfClosing ] = match;
		const block = `${ namespace ?? 'core/' }${ name }`;
		const label = `wp:${ namespace ?? '' }${ name }`;
		const line = markup.line( match.index );

		if ( closer ) {
			const index = open.findLastIndex(
				( item ) => item.block === block
			);
			if ( index === -1 ) {
				report(
					file,
					line,
					'block-markup',
					`"/${ label }" closes a block that isn't open.`
				);
				continue;
			}
			for ( const unclosed of open.splice( index ).slice( 1 ) ) {
				report(
					file,
					unclosed.line,
					'block-markup',
					`"${ unclosed.label }" is never closed.`
				);
			}
			continue;
		}

		if ( ! selfClosing ) {
			open.push( { block, label, line } );
		}

		let attributes = {};
		if ( json ) {
			try {
				attributes = JSON.parse( json );
			} catch ( error ) {
				report(
					file,
					line,
					'block-attributes',
					`"${ label }" has invalid attribute JSON: ${ error.message }`
				);
			}
		}

		const slug = typeof attributes.slug === 'string' ? attributes.slug : '';

		if ( block === 'core/pattern' ) {
			if ( ! slug ) {
				report(
					file,
					line,
					'pattern-reference',
					`"${ label }" has no slug.`
				);
			} else if (
				slug.startsWith( `${ slugPrefix }/` ) &&
				! patternSlugs.has( slug )
			) {
				report(
					file,
					line,
					'pattern-reference',
					`No pattern has the slug "${ slug }".`
				);
			}
		}

		if ( block === 'core/template-part' ) {
			if ( ! slug ) {
				report(
					file,
					line,
					'template-part-reference',
					`"${ label }" has no slug.`
				);
			} else if (
				! existsSync( join( THEME_DIR, 'parts', `${ slug }.html` ) )
			) {
				report(
					file,
					line,
					'template-part-reference',
					`No template part at parts/${ slug }.html.`
				);
			}
			if ( 'theme' in attributes ) {
				report(
					file,
					line,
					'editor-leftovers',
					`Remove the "theme" attribute from "${ label }".`
				);
			}
		}

		if ( block === 'core/query' && 'queryId' in attributes ) {
			report(
				file,
				line,
				'editor-leftovers',
				`Remove "queryId" from "${ label }".`
			);
		}

		if ( block === 'core/image' && 'id' in attributes ) {
			report(
				file,
				line,
				'editor-leftovers',
				`Remove the media "id" from "${ label }".`
			);
		}

		for ( const key of TEXT_ATTRIBUTES ) {
			const value = attributes[ key ];
			if ( typeof value === 'string' && hasLetters( value ) ) {
				const text = quote( value );
				report(
					file,
					line,
					'untranslated-text',
					`"${ label }" has untranslated text in "${ key }": ${ text }`
				);
			}
		}

		// The block name shows in the List View, so editors see it.
		const blockName = attributes.metadata?.name;
		if ( typeof blockName === 'string' && hasLetters( blockName ) ) {
			report(
				file,
				line,
				'untranslated-text',
				`"${ label }" has an untranslated block name: ${ quote(
					blockName
				) }`
			);
		}
	}

	for ( const unclosed of open ) {
		report(
			file,
			unclosed.line,
			'block-markup',
			`"${ unclosed.label }" is never closed.`
		);
	}
}

/**
 * Checks a file for visible text that doesn't come from PHP.
 *
 * @param {string} file Path relative to the theme root.
 * @param {Object} html Result of stripPhp().
 */
function checkText( file, html ) {
	// Blank out comments, block delimiters included, without moving anything.
	const text = html.text.replace( /<!--[\s\S]*?-->/g, ( comment ) =>
		comment.replace( /[^\n]/g, ' ' )
	);

	for ( const match of text.matchAll( /(?:^|>)([^<>]+)/g ) ) {
		if ( hasLetters( match[ 1 ] ) ) {
			const index = match.index + match[ 0 ].indexOf( match[ 1 ] );
			const offset = match[ 1 ].search( /\S/ );
			report(
				file,
				html.line( index + offset ),
				'untranslated-text',
				`Untranslated text: ${ quote( match[ 1 ] ) }`
			);
		}
	}

	for ( const match of text.matchAll(
		/\s(alt|aria-label|placeholder|title)\s*=\s*"([^"]*)"/g
	) ) {
		if ( hasLetters( match[ 2 ] ) ) {
			report(
				file,
				html.line( match.index ),
				'untranslated-text',
				`Untranslated ${ match[ 1 ] }: ${ quote( match[ 2 ] ) }`
			);
		}
	}
}

/**
 * Validates the block markup in patterns, templates, and template parts.
 *
 * @param {string}      slugPrefix   The theme's pattern slug prefix.
 * @param {Set<string>} patternSlugs The theme's pattern slugs.
 */
function validateBlockMarkup( slugPrefix, patternSlugs ) {
	const files = [
		...listFiles( 'patterns', '.php' ),
		...listFiles( 'templates', '.html' ),
		...listFiles( 'parts', '.html' ),
	];

	for ( const file of files ) {
		const source = read( file );

		// "0" stands in for PHP output both inside and outside JSON strings.
		checkBlocks( file, stripPhp( source, '0' ), slugPrefix, patternSlugs );
		checkText( file, stripPhp( source, '' ) );
	}
}

function escapeAnnotation( text, isProperty = false ) {
	const escaped = text
		.replace( /%/g, '%25' )
		.replace( /\r/g, '%0D' )
		.replace( /\n/g, '%0A' );
	return isProperty
		? escaped.replace( /:/g, '%3A' ).replace( /,/g, '%2C' )
		: escaped;
}

const styleCss = read( 'style.css' );
const textDomain = getFileHeader( styleCss, 'Text Domain' );
const requiresAtLeast = getFileHeader( styleCss, 'Requires at least' )
	.split( '.' )
	.slice( 0, 2 )
	.join( '.' );

if ( ! textDomain ) {
	report(
		'style.css',
		1,
		'theme-header',
		'Missing the "Text Domain" header.'
	);
}
if ( ! requiresAtLeast ) {
	report(
		'style.css',
		1,
		'theme-header',
		'Missing the "Requires at least" header, so theme.json can\'t be checked against its schema.'
	);
}

// Pattern slugs are prefixed with the text domain.
const slugPrefix = textDomain || basename( THEME_DIR );

await validateThemeJson( requiresAtLeast );
validateBlockMarkup( slugPrefix, validatePatternHeaders( slugPrefix ) );

problems.sort( ( a, b ) => a.file.localeCompare( b.file ) || a.line - b.line );

for ( const { file, line, rule, message } of problems ) {
	console.log( `${ file }:${ line }  ${ message }  [${ rule }]` );
	if ( process.env.GITHUB_ACTIONS ) {
		// Shows the problem on the pull request's changed files.
		const properties = [
			`file=${ escapeAnnotation( file, true ) }`,
			`line=${ line }`,
			`title=${ escapeAnnotation( rule, true ) }`,
		].join( ',' );
		console.log(
			`::error ${ properties }::${ escapeAnnotation( message ) }`
		);
	}
}

if ( problems.length ) {
	const noun = problems.length === 1 ? 'problem' : 'problems';
	console.log( `\n${ problems.length } ${ noun } found.` );
	process.exitCode = 1;
} else {
	console.log( 'No problems found.' );
}
