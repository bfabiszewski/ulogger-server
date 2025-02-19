const path = require('path');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const HtmlWebpackPlugin = require('html-webpack-plugin')

module.exports = {
  entry: './src/ulogger.js',
  output: {
    filename: 'bundle.[contenthash].js',
    path: path.resolve(__dirname, 'dist'),
    clean: true
  },
  plugins: [
    new CopyWebpackPlugin({
      patterns: [
        { from: 'src/assets/icons/*', to: 'icons/[name][ext]' },
        { from: 'src/assets/images/*', to: 'images/[name][ext]' },
        { from: 'src/assets/browserconfig.xml' },
        { from: 'src/assets/manifest.json' }
      ]
    }),
    new HtmlWebpackPlugin({
      template: 'src/assets/index.html'
    })
  ],
  optimization: {
    splitChunks: {
      cacheGroups: {
        vendors: {
          test: /[\\/]node_modules[\\/]|[\\/]ol.js/,
          name: 'ol'
        }
      }
    }
  },
  module: {
    rules: [
      {
        resourceQuery: /raw/,
        type: 'asset/source'
    },
      {
        test: /\.(png|svg|jpg|jpeg|gif)$/i,
        type: 'asset/resource',
        resourceQuery: { not: [ /raw/ ] },
        generator: {
          filename: 'images/[name][ext][query]'
        }
    },
      {
        test: /\.(woff|woff2|eot|ttf|otf)$/i,
        type: 'asset/resource',
        generator: {
          filename: 'fonts/[hash][ext][query]'
        }
    }
    ]
  }
};
