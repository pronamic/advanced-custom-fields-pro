import { createBdsToaster } from '@wpengine/bds-react';

// One toaster for the whole settings app, rendered once by SettingsPageLayout.
export const toaster = createBdsToaster( { placement: 'bottom-end' } );
