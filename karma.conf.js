/* eslint-disable no-undef,no-process-env,no-underscore-dangle */
// noinspection NpmUsedModulesInstalled
process = require('process');
process.env.CHROME_BIN = require('puppeteer').executablePath();

const path = require('path');

// don't preprocess files on debug run
// if (!process.env._INTELLIJ_KARMA_INTERNAL_PARAMETER_debug && !process.argv.includes('--debug')) {
//   reporters.push('coverage-istanbul');
// }

module.exports = function(config) {
  config.set({
    basePath: 'js/',
    frameworks: [ 'jasmine' ],
    files: [
      { pattern: 'test/*.test.js', type: 'module' },
      { pattern: 'test/helpers/*.js', type: 'module', included: false },
      { pattern: 'test/fixtures/*.html', included: false },
      { pattern: 'src/**/*.js', type: 'module', included: false }
    ],
    exclude: [],
    preprocessors: {
      'test/*.test.js': [ 'webpack', 'sourcemap' ],
      'test/helpers/*.js': [ 'webpack', 'sourcemap' ],
      'src/**/*.js': [ 'sourcemap' ]
    },
    coverageIstanbulReporter: {
      reports: [ 'html', 'text-summary', 'lcovonly' ],
      dir: path.join(__dirname, 'coverage'),
      fixWebpackSourcePaths: true,
      'report-config': {
        html: { outdir: 'html' }
      }
    },
    // possible values: 'dots', 'progress'
    reporters: [ 'progress', 'coverage-istanbul' ],
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
    concurrency: Infinity,
    // webpack
    webpack: {
      mode: 'development',
      devtool: 'inline-source-map',
      watch: true,
      module: {
        rules: [
          {
            test: /\.js$/,
            include: path.resolve('js/src/'),
            use: {
              loader: 'istanbul-instrumenter-loader',
              options: { esModules: true }
            }
          }
        ]
      }
    },
    webpackMiddleware: {
      noInfo: true
    }
  })
};
