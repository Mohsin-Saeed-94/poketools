const Encore = require('@symfony/webpack-encore');

Encore
// directory where compiled assets will be stored
    .setOutputPath('src/Resources/public')
    // public path used by the web server to access the output path
    .setPublicPath('/')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if you JavaScript imports CSS.
     */
    .addEntry('json-schema-bundle', './assets/js/json-schema-bundle.js')
    //.addEntry('page1', './assets/js/page1.js')
    //.addEntry('page2', './assets/js/page2.js')

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    // .enableSingleRuntimeChunk()
    .disableSingleRuntimeChunk()

    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you use Sass/SCSS files
    .enableSassLoader()

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()

    .configureBabel(function (babelConfig) {
        babelConfig.plugins.push(['prismjs', {
            'languages': ['json', 'markdown', 'regex'],
            'plugins': ['line-numbers', 'show-language'],
            'theme': 'default',
            'css': true
        }])
    })
;

module.exports = Encore.getWebpackConfig();
