const mix = require('laravel-mix');

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
const glob = require('glob')
const path = require('path')

function mixAssetsDir(query, cb) {
    (glob.sync("resources/" + query) || []).forEach((f) => {
        f = f.replace(/[\\\/]+/g, "/");
        cb(f, f.replace("resources", "public"));
    });
}


/*
 |--------------------------------------------------------------------------
 | Application assets
 |--------------------------------------------------------------------------
 */



mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css').postCss('resources/css/main.css', 'public/css', [
    require('tailwindcss'),
]);

mixAssetsDir('/vendors/**',(src,dest)=>mix.copyDirectory(src,dest));
mixAssetsDir('/css/extensions/**',(src,dest)=>mix.copyDirectory(src,dest))
mixAssetsDir('/js/extensions/**',(src,dest)=>mix.copyDirectory(src,dest))
var LiveReloadPlugin = require("webpack-livereload-plugin");


mix.webpackConfig({
    plugins: [new LiveReloadPlugin()],
});
