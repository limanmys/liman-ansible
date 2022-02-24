const webpack = require('webpack');
const isProd = process.env.NODE_ENV === "production";

module.exports = {
  productionSourceMap: false,
  outputDir: isProd ? '../public' : '../public/js',
  filenameHashing: false,
  css: {
    extract: false,
  },
  chainWebpack: config => {
    config.plugins.delete('html');
    config.plugins.delete('preload');
    config.plugins.delete('prefetch');
    config.plugins.delete('hmr');
  },
  configureWebpack: {
    optimization: {
      splitChunks: false
    },
    plugins: [
      new webpack.optimize.LimitChunkCountPlugin({
          maxChunks: 1
      })
    ]
  },
};