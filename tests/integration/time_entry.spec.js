import { test, expect } from '@playwright/test';

test.describe('ksf_FA_HRM_TimeTracking Time Tracking Module', () => {
  test('should load time entry list page', async ({ page }) => {
    await page.goto('/modules/ksf_FA_HRM_TimeTracking/pages/time_entries.php');
    await expect(page.locator('h1, h2')).toContainText(/Time Entry/i);
  });
});
