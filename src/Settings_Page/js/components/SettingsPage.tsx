import { useState, useEffect, useRef, type FormEvent } from 'react';
import { Section } from '@wpengine/bds-wordpress-react';

import SettingsPageLayout from './SettingsPageLayout.tsx';
import SettingsTabBar from './SettingsTabBar.tsx';
import SettingsField from './SettingsField.tsx';
import { toaster } from './toaster.ts';
import styles from './SettingsPage.module.css';

interface SectionConfig {
	title: string;
	fields: AcfSettingsPageField[];
}

interface SettingsPageConfig {
	strings: Record< string, string >;
	tabData: { sections: SectionConfig[] };
	activeTab: string;
	tabs: AcfSettingsPageTab[];
	ajaxUrl: string;
	action: string;
	nonce: string;
	logoUrl: string;
}

function getInitialSettings( sections: SectionConfig[] ): Record< string, unknown > {
	const settings: Record< string, unknown > = {};
	for ( const section of sections ) {
		for ( const field of section.fields ) {
			settings[ field.key ] = field.value ?? null;
		}
	}
	return settings;
}

/**
 * Reads a toggle value the way wp_validate_boolean() does, which is how the page reads toggle values on load.
 * A sanitize callback other than wp_validate_boolean() can return e.g. "0", which Boolean() would treat as true.
 * @param value The value from the server.
 */
function toBoolean( value: unknown ): boolean {
	if ( typeof value === 'string' ) {
		return value !== '' && value !== '0' && value.toLowerCase() !== 'false';
	}
	return Boolean( value );
}

const SettingsPage = ( {
	config,
	onTabSwitch,
	onUnsavedChangesChange,
}: {
	config: SettingsPageConfig;
	onTabSwitch?: ( tab: string ) => void;
	onUnsavedChangesChange?: ( hasUnsavedChanges: boolean ) => void;
} ) => {
	const { strings, tabData, activeTab, tabs, ajaxUrl, action, nonce } = config ?? {
		strings: {} as Record< string, string >,
		tabData: { sections: [] as SectionConfig[] },
		activeTab: '',
		tabs: [] as AcfSettingsPageTab[],
		ajaxUrl: '',
		action: '',
		nonce: '',
	};
	const sections = Array.isArray( tabData?.sections ) ? tabData.sections : [];
	const [ acfSettings, setAcfSettings ] = useState( () => getInitialSettings( sections ) );
	const [ isSaving, setIsSaving ] = useState( false );
	const savedSettings = useRef( getInitialSettings( sections ) );
	// Toggles auto-save, so only non-toggle fields count as unsaved changes for the Save button.
	const hasUnsavedChanges = sections.some( ( section ) =>
		section.fields
			.filter( ( field ) => field.type !== 'toggle' )
			.some(
				( field ) =>
					JSON.stringify( acfSettings[ field.key ] ) !== JSON.stringify( savedSettings.current[ field.key ] )
			)
	);
	const hasSections = sections.length > 0;
	const formId = hasSections ? 'acf-settings-form' : undefined;

	// The app owns navigation, so report unsaved changes up for it to guard tab switches and page unloads.
	useEffect( () => {
		onUnsavedChangesChange?.( hasUnsavedChanges );
	}, [ hasUnsavedChanges, onUnsavedChangesChange ] );

	useEffect( () => () => onUnsavedChangesChange?.( false ), [ onUnsavedChangesChange ] );

	const saveToggle = async ( key: string, checked: boolean ): Promise< void > => {
		const response = await fetch( ajaxUrl, {
			method: 'POST',
			body: new URLSearchParams( {
				action,
				nonce,
				tab: activeTab,
				acfSettings: JSON.stringify( { [ key ]: checked } ),
			} ),
		} ).catch( () => {
			toaster.create( { type: 'negative', title: strings.saveErrorMessage } );
			throw new Error( strings.saveErrorMessage );
		} );

		const payload: { success?: boolean; data?: unknown } | null = await response.json().catch( () => null );

		if ( ! response.ok || ! payload?.success ) {
			const errorMessage = typeof payload?.data === 'string' ? payload.data : strings.saveErrorMessage;
			toaster.create( { type: 'negative', title: errorMessage } );
			throw new Error( errorMessage );
		}

		// ACF's save handler sends back the value it reads after saving. Show that rather than the click: a sanitize
		// callback, or a filter on the underlying option, can leave the setting different from what was asked for.
		// A tab's own 'ajax_callback' may not send it back, so then keep the click, as the save succeeded.
		const data = payload.data as { settings?: Record< string, unknown > } | undefined;
		const confirmed = Boolean( data?.settings ) && Object.prototype.hasOwnProperty.call( data?.settings, key );
		const saved = confirmed ? toBoolean( data?.settings?.[ key ] ) : checked;
		savedSettings.current = { ...savedSettings.current, [ key ]: saved };
		setAcfSettings( ( prev ) => ( { ...prev, [ key ]: saved } ) );

		if ( saved !== checked ) {
			toaster.create( {
				type: 'negative',
				title: strings.settingsNotSaved
					? strings.settingsNotSaved.replace( '%s', key )
					: strings.saveErrorMessage,
			} );
			return;
		}

		toaster.create( { type: 'positive', title: strings.saveSuccessMessage } );
	};

	const handleSubmit = ( event: FormEvent< HTMLFormElement > ) => {
		event.preventDefault();

		// Only send fields the user has changed and can edit. Sending disabled or managed fields could overwrite their
		// stored values, and toggles are saved as soon as they're switched.
		const changedSettings: Record< string, unknown > = {};
		for ( const section of sections ) {
			for ( const field of section.fields ) {
				if ( field.type === 'toggle' || field.disabled || field.managed || field.proLocked ) {
					continue;
				}
				if (
					JSON.stringify( acfSettings[ field.key ] ) !== JSON.stringify( savedSettings.current[ field.key ] )
				) {
					changedSettings[ field.key ] = acfSettings[ field.key ];
				}
			}
		}

		if ( Object.keys( changedSettings ).length === 0 ) {
			return;
		}

		setIsSaving( true );

		fetch( ajaxUrl, {
			method: 'POST',
			body: new URLSearchParams( {
				action,
				nonce,
				tab: activeTab,
				acfSettings: JSON.stringify( changedSettings ),
			} ),
		} )
			.then( async ( response ) => {
				const payload: { success?: boolean; data?: unknown } | null = await response.json();

				if ( response.ok && payload?.success ) {
					const data = payload.data as
						| {
								message?: string;
								settings?: Record< string, unknown >;
								skipped?: string[];
								failed?: string[];
						  }
						| undefined;

					// Only settings the server confirmed are saved. Anything skipped, or edited while the request was
					// pending, stays unsaved, and a newer edit isn't overwritten by the older saved value.
					const confirmed = data?.settings ?? {};
					savedSettings.current = { ...savedSettings.current, ...confirmed };
					setAcfSettings( ( prev ) => {
						const next = { ...prev };
						for ( const [ key, value ] of Object.entries( confirmed ) ) {
							if ( JSON.stringify( prev[ key ] ) === JSON.stringify( changedSettings[ key ] ) ) {
								next[ key ] = value;
							}
						}
						return next;
					} );

					// Some settings may have been skipped or failed to save while others saved. They stay unsaved, so say which.
					const notSaved = [ ...( data?.skipped ?? [] ), ...( data?.failed ?? [] ) ];
					if ( notSaved.length > 0 ) {
						toaster.create( {
							type: 'negative',
							title: strings.settingsNotSaved
								? strings.settingsNotSaved.replace( '%s', notSaved.join( ', ' ) )
								: strings.saveErrorMessage,
						} );
						return;
					}

					toaster.create( { type: 'positive', title: strings.saveSuccessMessage } );
					return;
				}

				const errorMessage = typeof payload?.data === 'string' ? payload.data : strings.saveErrorMessage;
				toaster.create( { type: 'negative', title: errorMessage } );
			} )
			.catch( () => {
				toaster.create( { type: 'negative', title: strings.saveErrorMessage } );
			} )
			.finally( () => {
				setIsSaving( false );
			} );
	};

	return (
		<SettingsPageLayout
			title={ strings.settings }
			tabs={
				<SettingsTabBar
					activeTab={ activeTab }
					tabs={ tabs }
					ariaLabel={ strings.settingsTabs }
					onTabSwitch={ onTabSwitch }
				/>
			}
			formId={ formId }
			isSaving={ isSaving }
			hasUnsavedChanges={ hasUnsavedChanges }
			strings={ strings }
		>
			{ hasSections && (
				<form id={ formId } className={ styles.form } onSubmit={ handleSubmit }>
					{ sections.map( ( section ) => (
						<Section key={ section.title } title={ section.title } iconLeading={ null } padding="none">
							{ section.fields.map( ( field ) => (
								<SettingsField
									key={ field.key }
									field={ field }
									value={ acfSettings[ field.key ] as AcfSettingsPageField[ 'value' ] }
									strings={ strings }
									onToggle={ ( key, checked ) => {
										// saveToggle shows its own error toast; the switch stays in its saved state.
										saveToggle( key, checked ).catch( () => {} );
									} }
									onChange={ ( key, value ) =>
										setAcfSettings( ( prev ) => ( { ...prev, [ key ]: value } ) )
									}
								/>
							) ) }
						</Section>
					) ) }
				</form>
			) }
		</SettingsPageLayout>
	);
};

export default SettingsPage;
