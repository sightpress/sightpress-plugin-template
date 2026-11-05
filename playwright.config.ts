import { defineConfig, devices } from '@playwright/test';

// The specs need a running site with this plugin enabled (the skeleton's
// integration set-up). Point SIGHTPRESS_BASE_URL at it; without it they skip
// with a reason.
export default defineConfig({
  testDir: './e2e',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  reporter: process.env.CI ? [['github'], ['html', { open: 'never' }]] : 'list',
  use: {
    baseURL: process.env.SIGHTPRESS_BASE_URL,
    trace: 'retain-on-failure',
  },
  projects: [
    { name: 'desktop', use: { ...devices['Desktop Chrome'] } },
    { name: 'mobile-320', use: { ...devices['Pixel 5'], viewport: { width: 320, height: 640 } } },
  ],
});
