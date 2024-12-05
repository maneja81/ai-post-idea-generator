let mix = require( "laravel-mix" );

mix
	.css( "src/styles.css", "assets" )
	.js( "src/scripts.js", "assets" )
	.options(
		{
			terser: {
				extractComments: false,
			},
		}
	);
