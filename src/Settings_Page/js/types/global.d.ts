interface AcfSettingsPageTab {
	key: string;
	label: string;
	url: string;
	count?: number;
}

interface AcfSettingsPageField {
	key: string;
	type: string;
	label: string;
	value?: boolean | string | number | null;
	description?: string;
	managed?: boolean;
	pro?: boolean;
	proExpired?: boolean;
	proLocked?: boolean;
	proUrl?: string;
	proTooltip?: string;
	experimental?: boolean;
	learnMoreUrl?: string;
	disabled?: boolean;
	options?: Array< { label: string; value: string; disabled: boolean } >;
}

interface AcfSettingsPageSection {
	title: string;
	fields: AcfSettingsPageField[];
}

interface AcfSettingsPage {
	ajaxUrl: string;
	nonce: string;
	action: string;
	activeTab: string;
	strings: Record< string, string >;
	tabData: { sections: AcfSettingsPageSection[] };
	tabs: AcfSettingsPageTab[];
	logoUrl: string;
}

declare const acfSettingsPage: AcfSettingsPage;
