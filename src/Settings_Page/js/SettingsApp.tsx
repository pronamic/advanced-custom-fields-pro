import { useState, useEffect, useRef, useCallback, useReducer, type ComponentType } from 'react';
import { createRoot } from 'react-dom/client';
import SettingsPage from './components/SettingsPage.tsx';
import CustomTabPage from './components/CustomTabPage.tsx';
import { getTab, subscribe, type AcfSettingsTabConfig } from './settingsTabRegistry.ts';

// Base Design System fonts, tokens and type styles. Component CSS is imported by each component.
import '@wpengine/bds-react/fonts.css';
import '@wpengine/bds-react/styles.css';

type AnyConfig = {
	activeTab: string;
	ajaxUrl: string;
	nonce: string;
	tabs: AcfSettingsPageTab[];
	[ key: string ]: unknown;
};

/**
 * A built-in tab that renders its own page. PRO passes its License and Updates pages, so the free bundle doesn't
 * include them. Each page types its own config, so it's passed through untyped.
 */
export type SettingsTabPage = ComponentType< {
	config: any;
	onTabSwitch: ( tabKey: string ) => void;
	onTabCountChange: ( tabKey: string, count: number ) => void;
} >;

const SettingsApp = ( {
	initialConfig,
	tabPages,
}: {
	initialConfig: AnyConfig;
	tabPages: Record< string, SettingsTabPage >;
} ) => {
	const [ config, setConfig ] = useState< AnyConfig >( initialConfig );
	const configRef = useRef( config );
	const isSwitchingRef = useRef( false );
	// Incremented on every tab switch so a slow response for an earlier switch can't overwrite a later one.
	const switchRequestRef = useRef( 0 );
	const noticesRef = useRef< HTMLElement[] >( [] );
	const hasUnsavedChangesRef = useRef( false );

	const onUnsavedChangesChange = useCallback( ( hasUnsavedChanges: boolean ) => {
		hasUnsavedChangesRef.current = hasUnsavedChanges;
	}, [] );

	// Returns true if there are no unsaved changes, or the user confirms discarding them.
	const confirmDiscardChanges = () => {
		if ( ! hasUnsavedChangesRef.current ) {
			return true;
		}
		const strings = configRef.current.strings as Record< string, string > | undefined;
		// A native confirm matches the browser's own beforeunload prompt for a full page load.
		// eslint-disable-next-line no-alert
		if ( ! window.confirm( strings?.unsavedChanges ?? '' ) ) {
			return false;
		}
		// Clear the flag so the beforeunload guard doesn't prompt again if we fall back to a full page load.
		hasUnsavedChangesRef.current = false;
		return true;
	};

	useEffect( () => {
		const handleBeforeUnload = ( e: BeforeUnloadEvent ) => {
			if ( hasUnsavedChangesRef.current ) {
				e.preventDefault();
				e.returnValue = '';
			}
		};
		window.addEventListener( 'beforeunload', handleBeforeUnload );
		return () => window.removeEventListener( 'beforeunload', handleBeforeUnload );
	}, [] );

	// WP core prints admin notices above our app (no h1/h2 exists for core's own JS to reposition them after). Detach them once so we can re-insert them above the page content instead.
	useEffect( () => {
		const notices = document.querySelectorAll< HTMLElement >(
			'#wpbody-content > div.notice, #wpbody-content > div.updated, #wpbody-content > div.error, #wpbody-content > .update-nag'
		);
		noticesRef.current = Array.from( notices );
		noticesRef.current.forEach( ( notice ) => notice.remove() );
	}, [] );

	// Each tab mounts a fresh layout, so move the detached notices into its notices slot every time.
	useEffect( () => {
		const slot = document.querySelector( '#acf-settings-app [data-acf-settings-notices]' );
		if ( ! slot ) {
			return;
		}
		noticesRef.current.forEach( ( notice ) => {
			if ( notice.parentNode !== slot ) {
				slot.appendChild( notice );
			}
		} );
	} );

	useEffect( () => {
		configRef.current = config;
	}, [ config ] );

	// The server renders the loaded tab's label at the start of the title, so swap it for the active tab's after an in-page switch.
	const titleLabelRef = useRef( config.tabs.find( ( t ) => t.key === config.activeTab )?.label ?? '' );
	useEffect( () => {
		const label = config.tabs.find( ( t ) => t.key === config.activeTab )?.label;
		if ( ! label || label === titleLabelRef.current ) {
			return;
		}
		if ( titleLabelRef.current && document.title.startsWith( titleLabelRef.current ) ) {
			document.title = label + document.title.slice( titleLabelRef.current.length );
			titleLabelRef.current = label;
		}
	}, [ config.activeTab, config.tabs ] );

	useEffect( () => {
		const handlePopState = async () => {
			const currentConfig = configRef.current;
			const currentParams = new URLSearchParams( window.location.search );
			const targetPage = currentParams.get( 'page' ) ?? '';
			// Match on the page slug in each tab's URL so the slug format is only defined in PHP.
			const targetTab = currentConfig.tabs.find(
				( t ) => new URL( t.url, window.location.href ).searchParams.get( 'page' ) === targetPage
			);

			if ( ! targetTab ) {
				return;
			}

			// Navigating back to the rendered tab (e.g. Back then Forward mid-switch) must still cancel a pending switch.
			if ( targetTab.key === currentConfig.activeTab ) {
				++switchRequestRef.current;
				return;
			}

			// The browser has already changed the URL, so put it back if the user stays.
			if ( ! confirmDiscardChanges() ) {
				const currentTab = currentConfig.tabs.find( ( t ) => t.key === currentConfig.activeTab );
				if ( currentTab ) {
					window.history.pushState( { acfTab: currentTab.key }, '', currentTab.url );
				}
				return;
			}

			isSwitchingRef.current = true;
			const requestId = ++switchRequestRef.current;
			try {
				const res = await fetch( currentConfig.ajaxUrl, {
					method: 'POST',
					body: new URLSearchParams( {
						action: 'acf_settings_tab_data',
						nonce: currentConfig.nonce,
						tab: targetTab.key,
					} ),
				} );
				const json: { success: boolean; data: Partial< AnyConfig > } = await res.json();
				if ( requestId !== switchRequestRef.current ) {
					return;
				}
				if ( json.success && json.data ) {
					setConfig( ( prev ) => ( { ...prev, ...json.data, activeTab: targetTab.key } ) );
					window.scrollTo( 0, 0 );
					return;
				}
			} catch {
				if ( requestId !== switchRequestRef.current ) {
					return;
				}
				// fall through to reload
			} finally {
				isSwitchingRef.current = false;
			}
			window.location.reload();
		};

		window.addEventListener( 'popstate', handlePopState );
		return () => window.removeEventListener( 'popstate', handlePopState );
	}, [] );

	const onTabSwitch = async ( tabKey: string ) => {
		if ( ! confirmDiscardChanges() ) {
			return;
		}

		const requestId = ++switchRequestRef.current;
		try {
			const res = await fetch( config.ajaxUrl, {
				method: 'POST',
				body: new URLSearchParams( {
					action: 'acf_settings_tab_data',
					nonce: config.nonce,
					tab: tabKey,
				} ),
			} );
			const json: { success: boolean; data: Partial< AnyConfig > } = await res.json();
			// A later tab switch has started, so let that one win.
			if ( requestId !== switchRequestRef.current ) {
				return;
			}
			if ( json.success && json.data ) {
				setConfig( ( prev ) => ( { ...prev, ...json.data, activeTab: tabKey } ) );
				const targetTab = config.tabs.find( ( t ) => t.key === tabKey );
				if ( targetTab ) {
					window.history.pushState( { acfTab: tabKey }, '', targetTab.url );
				}
				window.scrollTo( 0, 0 );
				return;
			}
		} catch {
			if ( requestId !== switchRequestRef.current ) {
				return;
			}
			// fall through to page navigation
		}
		const targetTab = config.tabs.find( ( t ) => t.key === tabKey );
		if ( targetTab ) {
			window.location.href = targetTab.url;
		}
	};

	const onTabCountChange = useCallback( ( tabKey: string, count: number ) => {
		setConfig( ( prev ) => ( {
			...prev,
			tabs: prev.tabs.map( ( tab ) => ( tab.key === tabKey ? { ...tab, count } : tab ) ),
		} ) );
	}, [] );

	// Re-render when a tab script registers, in case it loads after the app has mounted.
	const [ , onTabRegistered ] = useReducer( ( count: number ) => count + 1, 0 );
	useEffect( () => subscribe( onTabRegistered ), [] );

	const shared = { onTabSwitch };

	// Tabs registered with acf.settingsPage.registerTab() render their own UI inside ACF's layout.
	const customTab = getTab( config.activeTab );
	if ( customTab ) {
		return (
			<CustomTabPage
				key={ config.activeTab }
				tab={ customTab }
				config={ config as unknown as AcfSettingsTabConfig }
				{ ...shared }
			/>
		);
	}

	// Own properties only, so a tab keyed e.g. "constructor" doesn't pick up Object.prototype members.
	const TabPage = Object.prototype.hasOwnProperty.call( tabPages, config.activeTab )
		? tabPages[ config.activeTab ]
		: undefined;
	if ( TabPage ) {
		return <TabPage config={ config } onTabCountChange={ onTabCountChange } { ...shared } />;
	}

	return (
		<SettingsPage
			key={ config.activeTab }
			config={ config as unknown as Parameters< typeof SettingsPage >[ 0 ][ 'config' ] }
			onUnsavedChangesChange={ onUnsavedChangesChange }
			{ ...shared }
		/>
	);
};

/**
 * Renders the settings app into #acf-settings-app once the page has loaded.
 *
 * @param tabPages Built-in tabs that render their own page, keyed by tab.
 */
export function mountSettingsApp( tabPages: Record< string, SettingsTabPage > = {} ) {
	document.addEventListener( 'DOMContentLoaded', () => {
		const container = document.getElementById( 'acf-settings-app' );
		if ( container ) {
			createRoot( container ).render(
				<SettingsApp initialConfig={ acfSettingsPage as unknown as AnyConfig } tabPages={ tabPages } />
			);
		}
	} );
}
