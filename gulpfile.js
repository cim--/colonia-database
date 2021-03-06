const elixir = require('laravel-elixir');

require('laravel-elixir-vue-2');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(mix => {

	mix.scripts([
		'../../../node_modules/jquery/dist/jquery.js',
		'../../../node_modules/jquery-ui/ui/jquery-1-7.js',
		'../../../node_modules/moment/moment.js',
		'../../../node_modules/bootstrap-sass/assets/javascripts/bootstrap.js',
		'../../../node_modules/datatables.net/js/jquery.dataTables.js',
		'../../../node_modules/datatables.net-bs/js/dataTables.bootstrap.js',
		'../../../node_modules/chart.js/dist/Chart.js',
		'../../../node_modules/fabric/dist/fabric.js',
		'../../../node_modules/d3/build/d3.js',
		'../../../node_modules/d3-cloud/build/d3.layout.cloud.js', 
		'*.js'
	], 'public/js/cdb.js')
		.sass('resources/assets/sass/app.scss', 'public/css/cdb.css')
		.copy([
            './node_modules/bootstrap-sass/assets/fonts/bootstrap',
            './node_modules/font-awesome/fonts'
        ], 'public/fonts');
});
