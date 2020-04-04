const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
// directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    // copy static files
    .copyFiles({
        from: './assets/static',
        to: 'static/[path][name].[ext]'
    })

    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if you JavaScript imports CSS.
     */
    // .createSharedEntry('app', './assets/js/app.js')
    .addEntry('other', './assets/js/other.js')
    .addEntry('ability_index', './assets/js/ability_index.js')
    .addEntry('ability_view', './assets/js/ability_view.js')
    .addEntry('item_index', './assets/js/item_index.js')
    .addEntry('item_view', './assets/js/item_view.js')
    .addEntry('location_index', './assets/js/location_index.js')
    .addEntry('location_view', './assets/js/location_view.js')
    .addEntry('move_index', './assets/js/move_index.js')
    .addEntry('move_view', './assets/js/move_view.js')
    .addEntry('nature_index', './assets/js/nature_index.js')
    .addEntry('nature_view', './assets/js/nature_view.js')
    .addEntry('pokemon_index', './assets/js/pokemon_index.js')
    .addEntry('pokemon_view', './assets/js/pokemon_view.js')
    .addEntry('type_index', './assets/js/type_index.js')
    .addEntry('type_view', './assets/js/type_view.js')
    .addEntry('contest_type_view', './assets/js/contest_type_view.js')
    .addEntry('search_results', './assets/js/search_results.js')

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications(!Encore.isProduction())
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // enables Sass/SCSS support
    .enableSassLoader()

    // inline files where sensible
    .configureUrlLoader({
        fonts: {limit: 4096},
        images: {limit: 8192}
    })

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you're having problems with a jQuery plugin
    .autoProvidejQuery()

    // uncomment if you use API Platform Admin (composer req api-admin)
    //.enableReactPreset()
    //.addEntry('admin', './assets/js/admin.js')

    // split chunks
    .splitEntryChunks()

    .enablePostCssLoader()
;

module.exports = Encore.getWebpackConfig();
