const { defineConfig } = require('cypress');

module.exports = defineConfig({
  e2e: {
    baseUrl: '{{ $baseUrl }}',
    specPattern: 'cypress/e2e/**/*.cy.{js,jsx,ts,tsx}',
    supportFile: 'cypress/support/e2e.js',
    fixturesFolder: 'cypress/fixtures',
    screenshotsFolder: 'cypress/screenshots',
    videosFolder: 'cypress/videos',
    defaultCommandTimeout: {{ $timeoutSeconds * 1000 }},
    pageLoadTimeout: 60000,
    viewportWidth: 1280,
    viewportHeight: 900,
  },
});
