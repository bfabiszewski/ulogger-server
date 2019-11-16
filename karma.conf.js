/* eslint-disable no-undef,no-process-env,no-underscore-dangle */
// noinspection NpmUsedModulesInstalled
process = require('process');
process.env.CHROME_BIN = require('puppeteer').executablePath();

const preprocessors = {};
const reporters = [ 'progress' ];
// don't preprocess files on debug run
if (!process.env._INTELLIJ_KARMA_INTERNAL_PARAMETER_debug && !process.argv.includes('--debug')) {
  preprocessors['src/*.js'] = 'karma-coverage-istanbul-instrumenter';
  reporters.push('coverage-istanbul');
}

module.exports = function(config) {
  config.set({
    basePath: 'js/',
    // available frameworks: https://npmjs.org/browse/keyword/karma-adapter
    frameworks: [ 'jasmine' ],
    // list of files / patterns to load in the browser
    files: [
      { pattern: 'test/*.test.js', type: 'module' },
      { pattern: 'src/*.js', type: 'module', included: false }
    ],
    // list of files / patterns to exclude
    exclude: [],
    // available preprocessors: https://npmjs.org/browse/keyword/karma-preprocessor
    preprocessors: preprocessors,
    coverageIstanbulInstrumenter: {
      esModules: true
    },
    // possible values: 'dots', 'progress'
    // available reporters: https://npmjs.org/browse/keyword/karma-reporter
    reporters: reporters,
    // web server port
    port: 9876,
    // enable / disable colors in the output (reporters and logs)
    colors: true,
    // possible values: config.LOG_DISABLE || config.LOG_ERROR || config.LOG_WARN || config.LOG_INFO || config.LOG_DEBUG
    logLevel: config.LOG_INFO,
    // enable / disable watching file and executing tests whenever any file changes
    autoWatch: false,
    // start these browsers
    // available browser launchers: https://npmjs.org/browse/keyword/karma-launcher
    browsers: [ 'ChromeHeadless' ],
    // Continuous Integration mode
    // if true, Karma captures browsers, runs the tests and exits
    singleRun: true,
    // how many browser should be started simultaneous
    concurrency: Infinity
  })
};
