const path = require('path');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

module.exports = {
  entry: './js/src/ulogger.js',
  output: {
    filename: 'bundle.js',
    path: path.resolve(__dirname, 'js/dist'),
    publicPath: 'js/dist/'
  },
  plugins: [ new CleanWebpackPlugin() ],
  optimization: {
    splitChunks: {
      cacheGroups: {
        vendors: {
          test: /[\\/]node_modules[\\/]|[\\/]ol.js/,
          name: 'ol'
        }
      }
    }
  }
};
