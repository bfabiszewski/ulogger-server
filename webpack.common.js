const path = require('path');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const csso = require('csso');
const fs = require('fs');
const cssDist = path.resolve(__dirname, 'css/dist');
const cssTransform = (content, filename) => {
  const basename = path.basename(filename);
  const result = csso.minify(content.toString(), {
    filename: basename,
    sourceMap: true
  });
  const output = basename === 'index.css' ? 'chartist.css' : basename;
  fs.writeFile(`css/dist/${output}.map`, result.map.toString(), (err) => { if (err) { throw err; }});
  return `${result.css}\n/*# sourceMappingURL=${output}.map */`;
}

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
    new CopyWebpackPlugin({
      patterns: [
        { from: 'css/src/*.css', to: `${cssDist}/[name][ext]`, transform: cssTransform },
        { from: 'node_modules/ol/ol.css', to: `${cssDist}/[name][ext]`, transform: cssTransform },
        { from: 'node_modules/chartist/dist/index.css', to: `${cssDist}/chartist[ext]`, transform: cssTransform }
      ]
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
      }
    ]
  }
};
