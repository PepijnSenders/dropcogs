var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Less
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    mix.sass('base.scss')
       .scripts([
        'modernizr/modernizr.js'
       ], 'public/js/header.bundle.js', 'resources/assets/vendor')
       .scripts([
        'jquery/dist/jquery.js',
        'bootstrap-sass-official/assets/javascripts/bootstrap.js'
       ], 'public/js/libs.bundle.js', 'resources/assets/vendor')
       .scriptsIn('resources/assets/js', 'public/js/app.bundle.js')
       .version([
        'css/base.css'
       ]);
});