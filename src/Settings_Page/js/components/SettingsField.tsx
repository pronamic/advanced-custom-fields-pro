import type { ReactElement } from 'react';
import { Badge, Link, SelectInput, Switch, TextInput, Tooltip } from '@wpengine/bds-react';
import { SectionRow } from '@wpengine/bds-wordpress-react';
import { ArrowUpRight, CodeXml, FlaskConical, Lock } from 'lucide-react';

import ProBadge from './ProBadge.tsx';
import styles from './SettingsField.module.css';

export type SettingValue = boolean | string | number | null | undefined;

export interface SettingsFieldProps {
	field: AcfSettingsPageField;
	value: SettingValue;
	strings: Record< string, string >;
	onToggle: ( key: string, checked: boolean ) => void;
	onChange: ( key: string, value: string ) => void;
}

/**
 * Wraps a badge in a tooltip. The focusable span is the trigger, so keyboard users can reach the tooltip too.
 * @param root0
 * @param root0.text
 * @param root0.children
 */
const WithTooltip = ( { text, children }: { text?: string; children: ReactElement } ) => {
	if ( ! text ) {
		return children;
	}

	return (
		<Tooltip content={ text } arrow="bottom-center">
			<span className={ styles.tooltipTrigger } tabIndex={ 0 }>
				{ children }
			</span>
		</Tooltip>
	);
};

const ManagedBadge = ( { strings }: { strings: Record< string, string > } ) => (
	<WithTooltip text={ strings.managedViaCodeTooltip }>
		<Badge size="md" color="gray" radius="pill" iconLeading={ <CodeXml /> }>
			{ strings.managedViaCode }
		</Badge>
	</WithTooltip>
);

const ProFieldBadge = ( { field, strings }: { field: AcfSettingsPageField; strings: Record< string, string > } ) => {
	const badge = field.proExpired ? (
		<Badge size="md" color="orange" radius="pill" iconLeading={ <Lock /> }>
			{ strings.licenseExpired }
		</Badge>
	) : (
		<ProBadge label={ strings.pro } />
	);
	const label = field.proExpired ? strings.licenseExpired : strings.pro;

	const content = field.proUrl ? (
		<a
			className={ styles.proLink }
			href={ field.proUrl }
			target="_blank"
			rel="noopener noreferrer"
			aria-label={ `${ label } (${ strings.opensInNewTab })` }
		>
			{ badge }
		</a>
	) : (
		<span className={ styles.badgeWrap }>{ badge }</span>
	);

	return <WithTooltip text={ field.proTooltip }>{ content }</WithTooltip>;
};

const ExperimentalBadge = ( { strings }: { strings: Record< string, string > } ) => (
	<Badge size="md" color="blue" radius="pill" iconLeading={ <FlaskConical /> }>
		{ strings.experimental }
	</Badge>
);

const Badges = ( { field, strings }: { field: AcfSettingsPageField; strings: Record< string, string > } ) => {
	if ( ! field.pro && ! field.managed ) {
		return null;
	}

	return (
		<span className={ styles.badges }>
			{ field.pro && <ProFieldBadge field={ field } strings={ strings } /> }
			{ field.managed && <ManagedBadge strings={ strings } /> }
		</span>
	);
};

// The Experimental pill sits next to the label in the row's label column.
const getLabel = ( field: AcfSettingsPageField, strings: Record< string, string > ) => {
	if ( ! field.experimental ) {
		return field.label;
	}

	return (
		<span className={ styles.label }>
			{ field.label }
			<ExperimentalBadge strings={ strings } />
		</span>
	);
};

const LearnMoreLink = ( {
	url,
	label,
	strings,
}: {
	url: string;
	label: string;
	strings: Record< string, string >;
} ) => (
	<Link
		className={ styles.learnMore }
		href={ url }
		target="_blank"
		rel="noopener noreferrer"
		aria-label={ `${ strings.learnMoreAbout.replace( '%s', label ) } (${ strings.opensInNewTab })` }
		iconTrailing={ <ArrowUpRight /> }
	>
		{ strings.learnMore }
	</Link>
);

// Select and text rows show the description and link under the control.
const getHint = ( field: AcfSettingsPageField, strings: Record< string, string > ) => {
	if ( ! field.description && ! field.learnMoreUrl ) {
		return undefined;
	}

	return (
		<>
			{ field.description && <span className={ styles.hintText }>{ field.description }</span> }
			{ field.learnMoreUrl && (
				<LearnMoreLink url={ field.learnMoreUrl } label={ field.label } strings={ strings } />
			) }
		</>
	);
};

/**
 * Renders one settings field from the `acf/settings_page/general/{section}/fields` filter as a Section row.
 * Field types map to BDS controls here, so extensions keep using the same PHP field shape.
 * @param root0
 * @param root0.field
 * @param root0.value
 * @param root0.strings
 * @param root0.onToggle
 * @param root0.onChange
 */
const SettingsField = ( { field, value, strings, onToggle, onChange }: SettingsFieldProps ) => {
	const inputId = `acf-setting-${ field.key }`;
	const labelId = `${ inputId }-label`;
	const disabled = Boolean( field.managed || field.proLocked || field.disabled );
	const label = getLabel( field, strings );

	switch ( field.type ) {
		case 'toggle':
			return (
				<SectionRow label={ label }>
					<div className={ styles.setting }>
						<div className={ styles.control }>
							<Switch
								id={ inputId }
								name={ field.key }
								size="md"
								className={ styles.switch }
								label={
									<span className={ styles.switchLabel }>{ field.description || field.label }</span>
								}
								checked={ Boolean( value ) }
								disabled={ disabled }
								onChange={ ( event ) => onToggle( field.key, event.target.checked ) }
							/>
							{ field.learnMoreUrl && (
								<LearnMoreLink url={ field.learnMoreUrl } label={ field.label } strings={ strings } />
							) }
						</div>
						<Badges field={ field } strings={ strings } />
					</div>
				</SectionRow>
			);
		case 'select':
			return (
				<SectionRow label={ label } labelId={ labelId }>
					<div className={ styles.setting }>
						<div className={ styles.select }>
							<SelectInput
								id={ inputId }
								name={ field.key }
								aria-labelledby={ labelId }
								items={ ( field.options ?? [] ).map( ( option ) => ( {
									value: option.value,
									label: option.label,
									disabled: option.disabled,
								} ) ) }
								value={ value === null || value === undefined ? null : String( value ) }
								onValueChange={ ( next ) => {
									if ( next !== null ) {
										onChange( field.key, next );
									}
								} }
								disabled={ disabled }
								hint={ getHint( field, strings ) }
							/>
						</div>
						<Badges field={ field } strings={ strings } />
					</div>
				</SectionRow>
			);
		case 'text':
			return (
				<SectionRow label={ label } htmlFor={ inputId }>
					<div className={ styles.setting }>
						<div className={ styles.text }>
							<TextInput
								id={ inputId }
								name={ field.key }
								value={ value === null || value === undefined ? '' : String( value ) }
								onChange={ ( event ) => onChange( field.key, event.target.value ) }
								disabled={ disabled }
								hint={ getHint( field, strings ) }
							/>
						</div>
						<Badges field={ field } strings={ strings } />
					</div>
				</SectionRow>
			);
		default:
			return null;
	}
};

export default SettingsField;
