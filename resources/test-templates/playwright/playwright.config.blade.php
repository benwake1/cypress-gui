import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? {{ $pwRetries }} : 0,
  workers: process.env.CI ? {{ $pwWorkers }} : undefined,
  reporter: 'html',
  timeout: {{ $timeoutSeconds * 1000 }},
  use: {
    baseURL: '{{ $baseUrl }}',
    trace: 'on-first-retry',
    headless: {{ $headless ? 'true' : 'false' }},
    viewport: { width: 1280, height: 900 },
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
    {
      name: 'firefox',
      use: { ...devices['Desktop Firefox'] },
    },
  ],
});
