import { useState } from 'react';
import { Alert, Button, Link, LoadingIndicator } from '@wpengine/bds-react';
import { Section, SectionRow } from '@wpengine/bds-wordpress-react';
import { ArrowUpRight } from 'lucide-react';

import SettingsPageLayout from '../../../Settings_Page/js/components/SettingsPageLayout.tsx';
import SettingsTabBar from '../../../Settings_Page/js/components/SettingsTabBar.tsx';
import { toaster } from '../../../Settings_Page/js/components/toaster.ts';
import styles from './UpdatesPage.module.css';

declare global {
	interface Window {
		tb_show?: ( caption: string, url: string, imageGroup?: boolean ) => void;
	}
}

interface UpdatesData {
	currentVersion: string;
	remoteVersion: string;
	updateAvailable: boolean;
	isChecking: boolean;
	licenseError: boolean;
	serverError: boolean;
	serverErrorMessage: string;
	wpCompatible: boolean;
	wpIncompatibleMessage: string;
	changelog: string;
	viewVersionDetailsUrl?: string;
	updateUrl?: string;
	updateBlockedMessage?: string;
	seeAllUpdatesUrl?: string;
	count?: number;
}

interface UpdatesPageConfig {
	strings: Record< string, string >;
	tabs: AcfSettingsPageTab[];
	tabData: UpdatesData;
	ajaxUrl: string;
	action: string;
	nonce: string;
	logoUrl: string;
}

interface UpdatesPageProps {
	config: UpdatesPageConfig;
}

const Value = ( { children }: { children: string } ) => <span className={ styles.value }>{ children }</span>;

function openVersionDetails( url: string ) {
	if ( typeof window.tb_show === 'function' ) {
		window.tb_show( '', url );
		return;
	}
	window.location.href = url;
}

const VersionDetailsButton = ( { label, url }: { label: string; url?: string } ) => (
	<Button
		variant="secondary"
		size="md"
		{ ...( url ? { onClick: () => openVersionDetails( url ) } : { disabled: true } ) }
	>
		{ label }
	</Button>
);

const UpdateNowButton = ( { label, url }: { label: string; url?: string } ) => (
	<Button
		variant="primary"
		size="md"
		{ ...( url
			? {
					onClick: () => {
						window.location.href = url;
					},
			  }
			: { disabled: true } ) }
	>
		{ label }
	</Button>
);

const UpdateStatusContent = ( {
	strings,
	updateAvailable,
	isChecking,
	licenseError,
	remoteVersion,
	viewVersionDetailsUrl,
	updateUrl,
	updateBlockedMessage,
	onCheckForUpdates,
}: {
	strings: Record< string, string >;
	updateAvailable: boolean;
	isChecking: boolean;
	licenseError: boolean;
	remoteVersion: string;
	viewVersionDetailsUrl?: string;
	updateUrl?: string;
	updateBlockedMessage?: string;
	onCheckForUpdates: () => void;
} ) => {
	const versionDetailsLabel = strings.viewVersionDetails.replace( '%s', remoteVersion );

	if ( licenseError ) {
		return (
			<div className={ styles.status }>
				<span className={ styles.error }>
					{ strings.licenseErrorMessage }
					{ strings.licenseErrorSubtext && (
						<>
							<br />
							{ strings.licenseErrorSubtext }
						</>
					) }
				</span>
				<div className={ styles.actions }>
					<VersionDetailsButton label={ versionDetailsLabel } url={ viewVersionDetailsUrl } />
					<UpdateNowButton label={ strings.updateNow } />
				</div>
			</div>
		);
	}

	if ( updateAvailable ) {
		return (
			<div className={ styles.status }>
				<span className={ styles.available }>
					{ strings.newVersionAvailable }
					{ updateBlockedMessage && (
						<>
							<br />
							<Value>{ updateBlockedMessage }</Value>
						</>
					) }
				</span>
				<div className={ styles.actions }>
					<VersionDetailsButton label={ versionDetailsLabel } url={ viewVersionDetailsUrl } />
					<UpdateNowButton label={ strings.updateNow } url={ updateUrl } />
				</div>
			</div>
		);
	}

	return (
		<div className={ styles.status }>
			{ isChecking ? (
				<span className={ styles.checking }>
					<LoadingIndicator size="sm" appearance="default" aria-hidden="true" />
					<Value>{ strings.checkingForUpdates }</Value>
				</span>
			) : (
				<Value>{ strings.noUpdateAvailable }</Value>
			) }
			<div className={ styles.actions }>
				<Button variant="secondary" size="md" disabled={ isChecking } onClick={ onCheckForUpdates }>
					{ strings.checkForUpdates }
				</Button>
			</div>
		</div>
	);
};

const UpdatesPage = ( {
	config,
	onTabSwitch,
	onTabCountChange,
}: UpdatesPageProps & {
	onTabSwitch?: ( tab: string ) => void;
	onTabCountChange?: ( tab: string, count: number ) => void;
} ) => {
	const { strings, tabData, ajaxUrl, nonce, tabs } = config;

	const [ updateState, setUpdateState ] = useState( {
		remoteVersion: tabData.remoteVersion,
		updateAvailable: tabData.updateAvailable,
		isChecking: tabData.isChecking,
		licenseError: tabData.licenseError,
		serverError: tabData.serverError,
		serverErrorMessage: tabData.serverErrorMessage,
		wpCompatible: tabData.wpCompatible,
		wpIncompatibleMessage: tabData.wpIncompatibleMessage,
		viewVersionDetailsUrl: tabData.viewVersionDetailsUrl,
		updateUrl: tabData.updateUrl,
		updateBlockedMessage: tabData.updateBlockedMessage,
	} );

	// The request itself failed (e.g. an expired nonce or a network error), so keep the last result and say why.
	const showCheckError = ( message?: unknown ) => {
		setUpdateState( ( s ) => ( { ...s, isChecking: false } ) );
		toaster.create( {
			type: 'negative',
			title: typeof message === 'string' && message ? message : strings.checkForUpdatesError,
		} );
	};

	const handleCheckForUpdates = async () => {
		setUpdateState( ( s ) => ( { ...s, isChecking: true } ) );

		try {
			const body = new FormData();
			body.append( 'action', 'acf_check_for_updates' );
			body.append( 'nonce', nonce );

			const response = await fetch( ajaxUrl, { method: 'POST', body } );
			// Error responses (e.g. a 403 for an expired nonce) carry their message as JSON.
			const json = await response.json().catch( () => null );

			if ( response.ok && json?.success && json.data ) {
				setUpdateState( {
					remoteVersion: json.data.remoteVersion,
					updateAvailable: json.data.updateAvailable,
					isChecking: false,
					licenseError: json.data.licenseError,
					serverError: json.data.serverError,
					serverErrorMessage: json.data.serverErrorMessage,
					wpCompatible: json.data.wpCompatible,
					wpIncompatibleMessage: json.data.wpIncompatibleMessage,
					viewVersionDetailsUrl: json.data.viewVersionDetailsUrl,
					updateUrl: json.data.updateUrl,
					updateBlockedMessage: json.data.updateBlockedMessage,
				} );
				// Keep the tab's badge in sync with the result of the check.
				if ( typeof json.data.count === 'number' ) {
					onTabCountChange?.( 'updates', json.data.count );
				}
			} else {
				showCheckError( json?.data );
			}
		} catch {
			showCheckError();
		}
	};

	const { currentVersion, changelog, seeAllUpdatesUrl } = tabData;

	const {
		remoteVersion,
		updateAvailable,
		isChecking,
		licenseError,
		serverError,
		serverErrorMessage,
		wpCompatible,
		wpIncompatibleMessage,
		viewVersionDetailsUrl,
		updateUrl,
		updateBlockedMessage,
	} = updateState;

	return (
		<SettingsPageLayout
			title={ strings.settings }
			tabs={
				<SettingsTabBar
					activeTab="updates"
					tabs={ tabs }
					ariaLabel={ strings.settingsTabs }
					onTabSwitch={ onTabSwitch }
				/>
			}
			strings={ strings }
		>
			{ ! wpCompatible && (
				<Alert type="warning" title={ strings.wpIncompatibleTitle }>
					{ wpIncompatibleMessage }
				</Alert>
			) }
			{ serverError && (
				<Alert type="negative" title={ strings.serverErrorTitle }>
					{ strings.serverErrorMessage }
					{ serverErrorMessage && <> ({ serverErrorMessage }).</> }
				</Alert>
			) }
			<Section padding="none" header={ false }>
				<SectionRow label={ strings.installedVersion }>
					<Value>{ currentVersion }</Value>
				</SectionRow>

				<SectionRow label={ strings.latestVersion }>
					<Value>{ remoteVersion || strings.notAvailable }</Value>
				</SectionRow>

				<SectionRow label={ strings.updateStatus }>
					<UpdateStatusContent
						strings={ strings }
						updateAvailable={ updateAvailable }
						isChecking={ isChecking }
						licenseError={ licenseError }
						remoteVersion={ remoteVersion }
						viewVersionDetailsUrl={ viewVersionDetailsUrl }
						updateUrl={ updateUrl }
						updateBlockedMessage={ updateBlockedMessage }
						onCheckForUpdates={ handleCheckForUpdates }
					/>
				</SectionRow>
			</Section>

			<Section
				padding="none"
				title={ strings.currentVersionChangelog }
				iconLeading={ null }
				action={
					seeAllUpdatesUrl ? (
						<Link
							href={ seeAllUpdatesUrl }
							target="_blank"
							rel="noopener noreferrer"
							size="md"
							iconTrailing={ <ArrowUpRight /> }
						>
							{ strings.seeAllUpdates }
							<span className="screen-reader-text"> ({ strings.opensInNewTab })</span>
						</Link>
					) : undefined
				}
			>
				{ changelog ? (
					// Escaped server-side in acf_updates_tab_get_changelog_changes() / acf_updates_tab_parse_readme_changelog().
					<div className={ styles.changelog } dangerouslySetInnerHTML={ { __html: changelog } } />
				) : (
					<div className={ styles.changelog }>{ strings.noChangelogAvailable }</div>
				) }
			</Section>
		</SettingsPageLayout>
	);
};

export default UpdatesPage;
