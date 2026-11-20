import { useEffect, useRef } from 'react';

import SettingsPageLayout from './SettingsPageLayout.tsx';
import SettingsTabBar from './SettingsTabBar.tsx';
import type { AcfSettingsTabConfig, AcfSettingsTabDefinition } from '../settingsTabRegistry.ts';

/**
 * Renders a tab registered with acf.settingsPage.registerTab() inside ACF's page layout and tab bar.
 *
 * @param root0             Props.
 * @param root0.tab         The registered tab definition.
 * @param root0.config      The settings page config for this tab.
 * @param root0.onTabSwitch Called when another tab is selected.
 */
const CustomTabPage = ( {
	tab,
	config,
	onTabSwitch,
}: {
	tab: AcfSettingsTabDefinition;
	config: AcfSettingsTabConfig;
	onTabSwitch?: ( tab: string ) => void;
} ) => {
	const containerRef = useRef< HTMLDivElement >( null );
	// Read the latest config when mounting without remounting on every config change.
	const configRef = useRef( config );
	configRef.current = config;

	useEffect( () => {
		const container = containerRef.current;
		if ( ! container ) {
			return;
		}

		try {
			tab.mount( container, configRef.current );
		} catch ( error ) {
			// eslint-disable-next-line no-console
			console.error( error );
		}

		return () => {
			try {
				tab.unmount?.( container );
			} catch ( error ) {
				// eslint-disable-next-line no-console
				console.error( error );
			}
			container.replaceChildren();
		};
	}, [ tab ] );

	return (
		<SettingsPageLayout
			title={ config.strings.settings }
			tabs={
				<SettingsTabBar
					activeTab={ config.activeTab }
					tabs={ config.tabs }
					ariaLabel={ config.strings.settingsTabs }
					onTabSwitch={ onTabSwitch }
				/>
			}
			strings={ config.strings }
		>
			<div ref={ containerRef } data-acf-settings-tab={ config.activeTab } />
		</SettingsPageLayout>
	);
};

export default CustomTabPage;
