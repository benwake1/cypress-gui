{
  "name": "signaldeck-cypress-tests",
  "version": "1.0.0",
  "private": true,
  "description": "Generated Cypress test suite for {{ $baseUrl }}",
  "engines": {
    "node": ">=18.0.0"
  },
  "scripts": {
    "test": "cypress run",
    "test:open": "cypress open",
    "test:headed": "cypress run --headed",
    "test:chrome": "cypress run --browser chrome",
    "test:firefox": "cypress run --browser firefox",
    "test:ci": "cypress run --reporter mochawesome --reporter-options reportDir=mochawesome-report,overwrite=false,html=false,json=true"
  },
  "devDependencies": {
    "cypress": "^13.0.0",
    "mochawesome": "^7.1.3",
    "mochawesome-merge": "^4.3.0"
  }
}
