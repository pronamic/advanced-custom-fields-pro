import type { MouseEvent } from 'react';
import { TabLink, TabNav } from '@wpengine/bds-react';
import { TabbedNavigation } from '@wpengine/bds-wordpress-react';

interface Tab {
	key: string;
	label: string;
	url: string;
	count?: number;
}

interface SettingsTabBarProps {
	activeTab: string;
	tabs: Tab[];
	ariaLabel: string;
	onTabSwitch?: ( tab: string ) => void;
}

const SettingsTabBar = ( { activeTab, tabs, ariaLabel, onTabSwitch }: SettingsTabBarProps ) => {
	const handleClick = ( event: MouseEvent< HTMLAnchorElement >, tab: Tab ) => {
		// Let modified clicks (new tab/window) behave as normal links.
		if ( ! onTabSwitch || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0 ) {
			return;
		}
		event.preventDefault();
		if ( tab.key !== activeTab ) {
			onTabSwitch( tab.key );
		}
	};

	return (
		<TabbedNavigation>
			<TabNav aria-label={ ariaLabel } appearance="line">
				{ tabs.map( ( tab ) => (
					<TabLink
						key={ tab.key }
						href={ tab.url }
						current={ tab.key === activeTab }
						// TabLink renders the count in its own Badge.
						badge={ tab.count ? tab.count : undefined }
						onClick={ ( event ) => handleClick( event, tab ) }
					>
						{ tab.label }
					</TabLink>
				) ) }
			</TabNav>
		</TabbedNavigation>
	);
};

export default SettingsTabBar;
