/**
 * PRO settings page entry: the shared settings app plus the License and Updates tabs, which the free bundle leaves out.
 * Settings_Page.php loads this instead of _acf-settings.tsx when ACF PRO is active.
 */
import { mountSettingsApp } from '../../../Settings_Page/js/SettingsApp.tsx';
import LicensePage from '../../License_Tab/js/LicensePage.tsx';
import UpdatesPage from '../../Updates_Tab/js/UpdatesPage.tsx';

mountSettingsApp( { license: LicensePage, updates: UpdatesPage } );
