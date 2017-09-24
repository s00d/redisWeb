let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

function resolve (dir) {
    return path.join(__dirname, '.', dir)
}

mix.js('resources/assets/js/app.js', 'public/js')
    .sass('resources/assets/sass/app.scss', 'public/css')
    .copy('/Users/pavel/Sites/redis-web/node_modules/socket.io-client/dist', 'public/js')
    .extract(['vue', 'jquery', 'socket.io-client'])
    .autoload({
        jquery: ['$', 'jquery', 'window.jquery'],
        'socket.io-client': ['io']
    })
    .webpackConfig({
        resolve: {
            alias: {
                components: resolve('/resources/assets/js/components'),
                pages: resolve('/resources/assets/js/pages'),
                root: resolve('/resources/assets/js/'),
                projectRoot: resolve(''),
                '~store': resolve('/resources/assets/js/store'),
                '~plugins': resolve('/resources/assets/js/plugins'),
                getters: resolve('/resources/assets/js/vuex/getters'),
                actions: resolve('/resources/assets/js/vuex/actions')
            }
        }
    })
    .sourceMaps();

// if (mix.inProduction()) {
mix.version();
// };

// mix.browserSync('redis-web.dev');