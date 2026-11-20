/**
 * Fallback for WordPress's react-jsx-runtime script, which WordPress only provides from 6.6.
 *
 * The settings page bundle loads react/jsx-runtime from the ReactJSXRuntime global rather than bundling its own copy,
 * so elements are always created by the React that WordPress loads. Settings_Page.php registers this file under the
 * react-jsx-runtime handle only when WordPress doesn't, and it builds elements with WordPress's React.createElement.
 *
 * @param {Object} React WordPress's React.
 */
( function ( React ) {
	'use strict';

	if ( window.ReactJSXRuntime || ! React ) {
		return;
	}

	/**
	 * Splits JSX props into createElement() config and children.
	 *
	 * @param {Object} props JSX props, including children.
	 * @param {*}      key   The element key, which JSX passes separately.
	 * @return {Array} The config and children.
	 */
	function splitProps( props, key ) {
		const config = {};
		let children;

		Object.keys( props || {} ).forEach( function ( name ) {
			if ( name === 'children' ) {
				children = props.children;
			} else {
				config[ name ] = props[ name ];
			}
		} );

		if ( key !== undefined ) {
			config.key = key;
		}

		return [ config, children ];
	}

	// A single or dynamic child. A dynamic array is passed as one child, so React still checks it for keys.
	function jsx( type, props, key ) {
		const [ config, children ] = splitProps( props, key );
		return children === undefined
			? React.createElement( type, config )
			: React.createElement( type, config, children );
	}

	// Static children written inline in JSX, which don't need keys.
	function jsxs( type, props, key ) {
		const [ config, children ] = splitProps( props, key );
		return Array.isArray( children ) ? React.createElement( type, config, ...children ) : jsx( type, props, key );
	}

	window.ReactJSXRuntime = { Fragment: React.Fragment, jsx, jsxs };
} )( window.React );
