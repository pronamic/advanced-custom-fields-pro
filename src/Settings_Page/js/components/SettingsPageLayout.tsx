import type { ReactNode } from 'react';
import { Button, Toaster } from '@wpengine/bds-react';
import { Page, PageContent, PageHeader } from '@wpengine/bds-wordpress-react';

import { toaster } from './toaster.ts';
import styles from './SettingsPageLayout.module.css';

interface SettingsPageLayoutProps {
	title: string;
	tabs: ReactNode;
	formId?: string;
	isSaving?: boolean;
	hasUnsavedChanges?: boolean;
	strings: Record< string, string >;
	children: ReactNode;
}

const SettingsPageLayout = ( {
	title,
	tabs,
	formId,
	isSaving,
	hasUnsavedChanges,
	strings,
	children,
}: SettingsPageLayoutProps ) => {
	return (
		<Page
			className={ styles.page }
			headerComponent={
				<div className={ styles.stickyHeader }>
					<PageHeader
						title={ title }
						actions={
							formId ? (
								<Button
									form={ formId }
									nativeType="submit"
									variant="primary"
									size="md"
									loading={ isSaving }
									disabled={ ! hasUnsavedChanges }
									loadingAriaLabel={ strings.saving }
								>
									{ strings.save }
								</Button>
							) : undefined
						}
						navigation={ tabs }
					/>
				</div>
			}
		>
			<PageContent>
				<PageContent.Main>
					{ /* WP core admin notices are moved in here by SettingsApp.tsx. */ }
					<div className={ styles.notices } data-acf-settings-notices="" />
					{ children }
				</PageContent.Main>
			</PageContent>
			<Toaster toaster={ toaster } />
		</Page>
	);
};

export default SettingsPageLayout;
