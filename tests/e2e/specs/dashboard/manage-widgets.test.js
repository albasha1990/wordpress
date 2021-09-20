import {
    visitAdminPage,
} from '@wordpress/e2e-test-utils';


describe('Manage dashboard widgets', () => {
    it('Hide a dashboard widget using screen options', async() => {
        await visitAdminPage('/');

        const screenOptionsButton = await page.waitForSelector('#show-settings-link');
        await screenOptionsButton.click();

        // Hide the welcome panel and check that it is hidden
        await page.click('#wp_welcome_panel-hide');

        const hiddenWelcomPanel = await page.$eval('#welcome-panel', (elem) => {
            return window.getComputedStyle(elem).getPropertyValue('display') === 'none';
        });
        expect(hiddenWelcomPanel).toBe(true);

        // Show back the welcome panel
        await page.click('#wp_welcome_panel-hide');
    });

    it('Hide the Welcome panel widget using the dismiss button', async() => {
        await visitAdminPage('/');

        const dismissButton = await page.waitForSelector('a.welcome-panel-close');
        await dismissButton.click();

        const hiddenWelcomPanel = await page.$eval('#welcome-panel', (elem) => {
            return window.getComputedStyle(elem).getPropertyValue('display') === 'none';
        });

        expect(hiddenWelcomPanel).toBe(true);

        // Show back the welcome panel
        const screenOptionsButton = await page.waitForSelector('#show-settings-link');
        await screenOptionsButton.click();
        await page.click('#wp_welcome_panel-hide');
    });

    it('Collapse and expand a dashboard widget', async() => {
        await visitAdminPage('/');

        const toggleButton = await page.waitForSelector('#dashboard_right_now button.handlediv');
        await toggleButton.click();

        const hiddenInsideWidget = await page.$eval('#dashboard_right_now div.inside', (elem) => {
            return window.getComputedStyle(elem).getPropertyValue('display') === 'none';
        });
        expect(hiddenInsideWidget).toBe(true);

        // Expand back the widget
        await page.waitForTimeout(100);
        await page.click('#dashboard_right_now button.handlediv');
    });
});