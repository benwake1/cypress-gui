{
  "name": "signaldeck-playwright-tests",
  "version": "1.0.0",
  "private": true,
  "description": "Generated Playwright test suite for {{ $baseUrl }}",
  "engines": {
    "node": ">=18.0.0"
  },
  "scripts": {
    "install:browsers": "playwright install --with-deps",
    "test": "playwright test",
    "test:headed": "playwright test --headed",
    "test:ui": "playwright test --ui",
    "test:debug": "playwright test --debug",
    "test:chrome": "playwright test --project=chromium",
    "test:firefox": "playwright test --project=firefox",
    "test:webkit": "playwright test --project=webkit",
    "test:ci": "playwright test --reporter=line,json",
    "report": "playwright show-report"
  },
  "devDependencies": {
    "@playwright/test": "^1.44.0"
  }
}
