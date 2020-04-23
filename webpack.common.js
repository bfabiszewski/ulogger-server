const path = require('path');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const csso = require('csso');
const fs = require('fs');

module.exports = {
  entry: './js/src/ulogger.js',
  output: {
    filename: 'bundle.js',
    path: path.resolve(__dirname, 'js/dist'),
    publicPath: 'js/dist/'
  },
  plugins: [
   new CleanWebpackPlugin({
      cleanOnceBeforeBuildPatterns: [ '**/*', '!.*' ]
    }),
    new CopyWebpackPlugin([
      {
        from: '{css/src/*.css,node_modules/ol/ol.css,node_modules/chartist/dist/chartist.css}',
        to: path.resolve(__dirname, 'css/dist'),
        flatten: true,
        transform: (content, filename) => {
          const basename = path.basename(filename);
          const result = csso.minify(content.toString(), {
            filename: basename,
            sourceMap: true
          });
          fs.writeFile(`css/dist/${basename}.map`, result.map.toString(), (err) => { if (err) { throw err; }});
          return `${result.css}\n/*# sourceMappingURL=${basename}.map */`;
        }
      }
    ])
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
  }
};
