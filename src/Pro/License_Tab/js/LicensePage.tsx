import { Fragment, useEffect, useState, type FormEvent } from 'react';
import { Badge, Button, Link, TextInput, ValidationMessage, type BadgeColor } from '@wpengine/bds-react';
import { Section, SectionRow } from '@wpengine/bds-wordpress-react';
import { ArrowUpRight, RefreshCw } from 'lucide-react';

import SettingsPageLayout from '../../../Settings_Page/js/components/SettingsPageLayout.tsx';
import SettingsTabBar from '../../../Settings_Page/js/components/SettingsTabBar.tsx';
import { toaster } from '../../../Settings_Page/js/components/toaster.ts';
import styles from './LicensePage.module.css';

interface LicenseData {
	activateNonce: string;
	deactivateNonce: string;
	recheckNonce: string;
	key: string;
	isActive: boolean;
	isDefined: boolean;
	canRecheck: boolean;
	isWpe: boolean;
	status: string;
	statusText: string;
	name: string;
	isLifetime: boolean;
	isExpired: boolean;
	isRefunded: boolean;
	expiryDate: string;
	manageLicenseUrl: string;
	pricingUrl: string;
}

interface LicensePageConfig {
	strings: Record< string, string >;
	tabs: AcfSettingsPageTab[];
	tabData: LicenseData;
	ajaxUrl: string;
	action: string;
	nonce: string;
	logoUrl: string;
}

function decodeHtmlEntities( str: string ): string {
	const el = document.createElement( 'textarea' );
	el.innerHTML = str;
	return el.value;
}

interface LinkedError {
	text: string;
	links: { href: string; label: string }[];
}

type MessagePart = { text: string } | { href: string; label: string };

/**
 * Splits a message into its text and its http(s) links, in order, so links can be rendered without injecting HTML.
 * @param message
 */
function parseMessageParts( message: string ): MessagePart[] {
	// DOMParser builds an inert document, so markup in the message (e.g. an <img onerror>) can't run.
	const container = new DOMParser().parseFromString( decodeHtmlEntities( message ), 'text/html' ).body;

	const parts: MessagePart[] = [];

	container.childNodes.forEach( ( node ) => {
		if ( node instanceof HTMLAnchorElement ) {
			const href = node.getAttribute( 'href' ) ?? '';
			let url: URL | null = null;
			try {
				url = new URL( href, window.location.origin );
			} catch {
				// A malformed href is shown as plain text below.
			}
			if ( url && ( url.protocol === 'http:' || url.protocol === 'https:' ) ) {
				parts.push( { href: url.href, label: node.textContent?.trim() ?? '' } );
				return;
			}
		}

		// Keep the surrounding spaces so the text reads as written around any links.
		const text = node.textContent ?? '';
		if ( text.trim() ) {
			parts.push( { text } );
		}
	} );

	return parts;
}

function parseLinkedError( message: string ): LinkedError | null {
	const parts = parseMessageParts( message );
	const textParts = parts.flatMap( ( part ) => ( 'text' in part ? [ part.text.trim() ] : [] ) );
	const links = parts.flatMap( ( part ) => ( 'href' in part ? [ part ] : [] ) );

	// Only treat this as a field-level error if there's a link to show alongside the message.
	return links.length ? { text: textParts.join( ' ' ), links } : null;
}

/**
 * Returns the localStorage key for this site's license notice.
 *
 * Sites on a subdirectory multisite share an origin, and so localStorage, so the key includes the site's
 * admin-ajax.php URL, which is different for each site.
 * @param ajaxUrl The site's admin-ajax.php URL.
 */
export function getLicenseNoticeStorageKey( ajaxUrl: string ): string {
	return `acf-license-notice-message:${ ajaxUrl }`;
}

function getStoredErrorMessage( storageKey: string ): string | null {
	try {
		return window.localStorage.getItem( storageKey );
	} catch {
		return null;
	}
}

function getStatusBadgeColor( status: string ): BadgeColor {
	switch ( status ) {
		case 'active':
			return 'green';
		case 'expired':
		case 'cancelled':
			return 'orange';
		default:
			return 'gray';
	}
}

/**
 * An external link styled as a BDS Link, announcing that it opens in a new tab.
 * @param root0
 * @param root0.href
 * @param root0.children
 * @param root0.newTabLabel
 */
const ExternalLink = ( { href, children, newTabLabel }: { href: string; children: string; newTabLabel: string } ) => (
	<Link href={ href } target="_blank" rel="noopener noreferrer" size="md" iconTrailing={ <ArrowUpRight /> }>
		{ children }
		<span className="screen-reader-text"> ({ newTabLabel })</span>
	</Link>
);

interface LicensePageProps {
	config: LicensePageConfig;
	onTabSwitch?: ( tab: string ) => void;
}

const LicensePage = ( { config, onTabSwitch }: LicensePageProps ) => {
	const { strings, tabs, tabData, ajaxUrl, action, nonce } = config;
	const [ license, setLicense ] = useState< LicenseData >( tabData );
	const [ isLoading, setIsLoading ] = useState( false );
	const [ isRechecking, setIsRechecking ] = useState( false );
	const [ isDeactivating, setIsDeactivating ] = useState( false );
	const [ licenseKeyInput, setLicenseKeyInput ] = useState( license.isActive ? license.key : '' );
	const noticeStorageKey = getLicenseNoticeStorageKey( ajaxUrl );
	// Ignore a stored notice if the license is already confirmed active, otherwise it lingers forever.
	const [ linkedErrorMessage, setLinkedErrorMessage ] = useState< string | null >( () =>
		tabData.isActive ? null : getStoredErrorMessage( noticeStorageKey )
	);

	const hasLicenseKey = Boolean( license.key );
	const showKeyInput = ! license.isDefined && ! license.isWpe;
	// An inactive (e.g. expired) stored key can still be removed while a replacement key is entered.
	const showSeparateDeactivate = showKeyInput && hasLicenseKey && ! license.isActive;
	const linkedError = linkedErrorMessage ? parseLinkedError( linkedErrorMessage ) : null;

	// Persist the notice so it survives a page reload, only clearing when the key is edited or replaced.
	useEffect( () => {
		try {
			if ( linkedErrorMessage ) {
				window.localStorage.setItem( noticeStorageKey, linkedErrorMessage );
			} else {
				window.localStorage.removeItem( noticeStorageKey );
			}
		} catch {
			// Ignore storage errors (e.g. private browsing).
		}
	}, [ linkedErrorMessage, noticeStorageKey ] );

	const sendLicenseRequest = ( params: Record< string, string > ) => {
		return fetch( ajaxUrl, {
			method: 'POST',
			body: new URLSearchParams( {
				action,
				nonce,
				tab: 'license',
				...params,
			} ),
		} ).then( ( res ) => res.json() );
	};

	const runLicenseAction = (
		params: Record< string, string >,
		errorTitle: string,
		setPending: ( pending: boolean ) => void
	) => {
		setLinkedErrorMessage( null );
		setPending( true );
		sendLicenseRequest( params )
			.then( ( response ) => {
				if ( response.success && response.data ) {
					if ( response.data.license ) {
						const nextLicense = response.data.license as LicenseData;
						const wasActive = license.isActive;
						setLicense( nextLicense );
						// The returned key is masked, so only show it in the read-only input of an active license. Otherwise
						// keep what the user typed (e.g. after a failed activation), or clear the masked key if it was active.
						setLicenseKeyInput( ( prev ) => {
							if ( nextLicense.isActive ) {
								return nextLicense.key ?? '';
							}
							return wasActive ? '' : prev;
						} );
					}
					const type = response.data.success === false ? 'negative' : 'positive';
					const message = ( response.data.message as string ) ?? '';
					if ( type === 'negative' && parseLinkedError( message ) ) {
						setLinkedErrorMessage( message );
					} else {
						toaster.create( { type, title: decodeHtmlEntities( message ) } );
					}
				} else {
					toaster.create( {
						type: 'negative',
						title: decodeHtmlEntities( ( response.data as unknown as string ) ?? '' ),
					} );
				}
			} )
			.catch( () => toaster.create( { type: 'negative', title: errorTitle } ) )
			.finally( () => setPending( false ) );
	};

	const handleActivate = () => {
		const inputKey = licenseKeyInput.trim();
		if ( ! inputKey ) {
			return;
		}

		runLicenseAction(
			{ license_action: 'activate', license_key: inputKey, _acf_nonce: license.activateNonce },
			strings.activateError,
			setIsLoading
		);
	};

	const handleDeactivate = ( setPending: ( pending: boolean ) => void = setIsLoading ) => {
		runLicenseAction(
			{ license_action: 'deactivate', _acf_nonce: license.deactivateNonce },
			strings.deactivateError,
			setPending
		);
	};

	const handleRecheck = () => {
		runLicenseAction(
			{ license_action: 'recheck', _acf_nonce: license.recheckNonce },
			strings.recheckError,
			setIsRechecking
		);
	};

	const handleSubmit = ( e: FormEvent< HTMLFormElement > ) => {
		e.preventDefault();
		if ( license.isActive ) {
			handleDeactivate();
		} else {
			handleActivate();
		}
	};

	return (
		<SettingsPageLayout
			title={ strings.settings }
			tabs={
				<SettingsTabBar
					activeTab="license"
					tabs={ tabs }
					ariaLabel={ strings.settingsTabs }
					onTabSwitch={ onTabSwitch }
				/>
			}
			strings={ strings }
		>
			<Section padding="none" header={ false }>
				<SectionRow label={ strings.licenseKey } htmlFor={ showKeyInput ? 'acf-license-key' : undefined }>
					{ license.isDefined && (
						<span className={ styles.value }>
							{ parseMessageParts( strings.licenseDefinedInConfig ?? '' ).map( ( part, index ) => (
								<Fragment key={ index }>
									{ 'href' in part ? (
										<ExternalLink href={ part.href } newTabLabel={ strings.opensInNewTab }>
											{ part.label }
										</ExternalLink>
									) : (
										part.text
									) }
								</Fragment>
							) ) }
						</span>
					) }
					{ showKeyInput && (
						<form onSubmit={ handleSubmit } className={ styles.keyForm }>
							<div className={ styles.keyField }>
								<TextInput
									id="acf-license-key"
									name="acf_license_key"
									value={ licenseKeyInput }
									onChange={ ( e ) => {
										setLicenseKeyInput( e.target.value );
										setLinkedErrorMessage( null );
									} }
									placeholder={ strings.enterLicenseKey }
									readOnly={ license.isActive }
									invalid={ Boolean( linkedError ) }
									aria-describedby={ linkedError ? 'acf-license-key-error' : undefined }
								/>
								{ linkedError && (
									<div className={ styles.error } id="acf-license-key-error">
										<ValidationMessage type="negative">{ linkedError.text }</ValidationMessage>
										{ linkedError.links.map( ( link, index ) => (
											<ExternalLink
												key={ index }
												href={ link.href }
												newTabLabel={ strings.opensInNewTab }
											>
												{ link.label }
											</ExternalLink>
										) ) }
									</div>
								) }
							</div>
							<Button
								nativeType="submit"
								variant={ license.isActive ? 'secondary' : 'primary' }
								size="md"
								loading={ isLoading }
								disabled={ isRechecking || isDeactivating }
							>
								{ license.isActive ? strings.deactivateLicense : strings.activateLicense }
							</Button>
							{ showSeparateDeactivate && (
								<Button
									variant="secondary"
									size="md"
									loading={ isDeactivating }
									disabled={ isLoading || isRechecking }
									onClick={ () => handleDeactivate( setIsDeactivating ) }
								>
									{ strings.deactivateLicense }
								</Button>
							) }
						</form>
					) }
				</SectionRow>

				<SectionRow label={ strings.licenseStatus }>
					<div className={ styles.status }>
						<Badge size="md" color={ getStatusBadgeColor( license.status ) } radius="pill">
							{ license.statusText }
						</Badge>
						{ ! hasLicenseKey && ! license.isDefined && (
							<span className={ styles.prompt }>
								<span className={ styles.value }>{ strings.noPricingMessage }</span>
								<ExternalLink
									href={ decodeHtmlEntities( license.pricingUrl ) }
									newTabLabel={ strings.opensInNewTab }
								>
									{ strings.viewPricingPurchase }
								</ExternalLink>
							</span>
						) }
						{ ( license.canRecheck || ( hasLicenseKey && ! license.isLifetime ) ) && (
							<span className={ styles.actions }>
								{ license.canRecheck && (
									<Button
										variant="ghost"
										size="md"
										iconLeading={ <RefreshCw /> }
										loading={ isRechecking }
										disabled={ isLoading || isDeactivating }
										onClick={ handleRecheck }
									>
										{ strings.recheckLicense }
									</Button>
								) }
								{ hasLicenseKey && ! license.isLifetime && (
									<ExternalLink
										href={ decodeHtmlEntities( license.manageLicenseUrl ) }
										newTabLabel={ strings.opensInNewTab }
									>
										{ license.isExpired || license.isRefunded
											? strings.renewSubscription
											: strings.manageLicense }
									</ExternalLink>
								) }
							</span>
						) }
					</div>
				</SectionRow>

				{ hasLicenseKey && license.name && (
					<SectionRow label={ license.isLifetime ? strings.licenseType : strings.subscriptionType }>
						<span className={ styles.value }>
							{ license.isLifetime && ! license.isWpe ? `${ strings.lifetime } - ` : '' }
							{ license.name }
						</span>
					</SectionRow>
				) }

				{ hasLicenseKey && ! license.isLifetime && license.expiryDate && (
					<SectionRow label={ license.isExpired ? strings.subscriptionExpired : strings.subscriptionExpires }>
						<span className={ styles.value }>{ license.expiryDate }</span>
					</SectionRow>
				) }
			</Section>
		</SettingsPageLayout>
	);
};

export default LicensePage;
