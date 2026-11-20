/**
 * Registry for settings tabs that render their own UI inside the settings page.
 *
 * A tab registered with a 'js' URL in the 'acf/settings_page/tabs' filter has that script loaded after
 * ACF's settings app. The script calls acf.settingsPage.registerTab() with mount and unmount callbacks,
 * and ACF calls them when the tab is shown or hidden, keeping its own page header and tab bar.
 */

export interface AcfSettingsTabConfig {
	ajaxUrl: string;
	nonce: string;
	action: string;
	activeTab: string;
	strings: Record< string, string >;
	tabData: unknown;
	tabs: AcfSettingsPageTab[];
}

export interface AcfSettingsTabDefinition {
	/**
	 * Renders the tab into the container. Called each time the tab is shown.
	 *
	 * Use the config passed here rather than the global acfSettingsPage: when switching tabs in-page,
	 * the global still holds the data for the tab the page was loaded on.
	 */
	mount: ( container: HTMLElement, config: AcfSettingsTabConfig ) => void;

	/**
	 * Cleans up when the tab is hidden, e.g. stopping timers and removing document event listeners.
	 * ACF empties the container afterwards.
	 */
	unmount?: ( container: HTMLElement ) => void;
}

const tabs = new Map< string, AcfSettingsTabDefinition >();
const listeners = new Set< () => void >();

export function registerTab( key: string, definition: AcfSettingsTabDefinition ): void {
	if ( typeof definition?.mount !== 'function' ) {
		// eslint-disable-next-line no-console
		console.error( `acf.settingsPage.registerTab: the "${ key }" tab needs a mount() function.` );
		return;
	}

	tabs.set( key, definition );
	listeners.forEach( ( listener ) => listener() );
}

export function getTab( key: string ): AcfSettingsTabDefinition | undefined {
	return tabs.get( key );
}

/**
 * Calls the listener whenever a tab is registered. Returns a function that unsubscribes.
 *
 * @param listener Called after each registration.
 */
export function subscribe( listener: () => void ): () => void {
	listeners.add( listener );
	return () => {
		listeners.delete( listener );
	};
}

// Expose the registry as acf.settingsPage.registerTab() for tab scripts. ACF's own acf.js (which assigns
// window.acf) loads in the header, so extend the object rather than replacing it.
const win = window as unknown as { acf?: Record< string, unknown > };
win.acf = win.acf || {};
win.acf.settingsPage = { registerTab };
