let mix = require('laravel-mix');
let path = require('path');

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

mix.setPublicPath('public/assets')
    .js('src/assets/js/app.js', 'public/assets/js')
    .sass('src/assets/sass/app.scss', 'public/assets/css')
    .extract(['vue', 'jquery', 'socket.io-client'])
    .autoload({
        jquery: ['$', 'jquery', 'window.jquery'],
        'socket.io-client': ['io']
    })
    .webpackConfig({
        node: {
            // __dirname: process.env.NODE_ENV !== 'production',
            // __filename: process.env.NODE_ENV !== 'production',
            fs: "empty",
            net: "empty",
            tls: "empty"
        },
        resolve: {
            alias: {
                components: resolve('/src/assets/js/components'),
                pages: resolve('/src/assets/js/pages'),
                root: resolve('/src/assets/js/'),
                projectRoot: resolve(''),
                '~store': resolve('/src/assets/js/store'),
                '~plugins': resolve('/src/assets/js/plugins'),
                getters: resolve('/src/assets/js/vuex/getters'),
                actions: resolve('/src/assets/js/vuex/actions')
            }
        }
    })
    .sourceMaps();

// if (mix.inProduction()) {
mix.version();
// };

// mix.browserSync('redis-web.dev');