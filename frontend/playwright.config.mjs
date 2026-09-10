import { defineConfig } from '@playwright/test';
export default defineConfig({
  testDir: './tests/browser', timeout: 30000, workers: 1,
  use: { headless: true, channel: 'chrome', baseURL: 'http://127.0.0.1:5189', trace: 'retain-on-failure' },
  webServer: [
    { command: 'npm.cmd run dev -- --host 127.0.0.1 --port 5189', url: 'http://127.0.0.1:5189', reuseExistingServer: false },
    { command: 'npm.cmd --prefix ../admin run dev -- --host 127.0.0.1 --port 5190', url: 'http://127.0.0.1:5190', reuseExistingServer: false },
  ],
});
