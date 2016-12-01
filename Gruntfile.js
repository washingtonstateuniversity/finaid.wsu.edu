var Promise = require( "es6-promise" ).polyfill();

module.exports = function( grunt ) {
	grunt.initConfig( {
		pkg: grunt.file.readJSON( "package.json" ),

		concat: {
			options: {
				sourceMap: true
			},
			dist: {
				src: "css/*.css",
				dest: "tmp-style.css"
			}
		},

		postcss: {
			options: {
				map: true,
				diff: false,
				processors: [
					require( "autoprefixer" )( {
						browsers: [ "> 1%", "ie 8-11", "Firefox ESR" ]
					} )
				]
			},
			dist: {
				src: "tmp-style.css",
				dest: "style.css"
			}
		},

		csslint: {
			main: {
				src: [ "style.css" ],
				options: {
					"fallback-colors": false,              // Unless we want to support IE8
					"box-sizing": false,                   // Unless we want to support IE7
					"compatible-vendor-prefixes": false,   // The library on this is older than autoprefixer.
					"gradients": false,                    // This also applies ^
					"overqualified-elements": false,       // We have weird uses that will always generate warnings.
					"ids": false,
					"regex-selectors": false,              // Audit
					"adjoining-classes": false,
					"box-model": false,                    // Audit
					"universal-selector": false,           // Audit
					"unique-headings": false,              // Audit
					"outline-none": false,                 // Audit
					"floats": false,
					"font-sizes": false,                   // Audit
					"important": false,                    // This should be set to 2 one day.
					"unqualified-attributes": false,       // Should probably be 2 one day.
					"qualified-headings": false,
					"known-properties": 1,              // Okay to ignore in the case of known unknowns.
					"duplicate-background-images": 2,
					"duplicate-properties": 2,
					"star-property-hack": 2,
					"text-indent": 2,
					"display-property-grouping": 2,
					"shorthand": 2,
					"empty-rules": 2,
					"vendor-prefix": 2,
					"zero-units": 2,
					"order-alphabetical": false
				}
			}
		},

		clean: {
			options: {
				force: true
			},
			temp: [ "tmp-style.css", "tmp-style.css.map" ]
		},

		jscs: {
			scripts: {
				src: [ "Gruntfile.js", "src/js/*.js" ],
				options: {
					preset: "jquery",
					requireCamelCaseOrUpperCaseIdentifiers: false, // We rely on name_name too much to change them all.
					maximumLineLength: 250
				}
			}
		},

		jshint: {
			grunt_script: {
				src: [ "Gruntfile.js" ],
				options: {
					curly: true,
					eqeqeq: true,
					noarg: true,
					quotmark: "double",
					undef: true,
					unused: false,
					node: true     // Define globals available when running in Node.
				}
			},
			theme_scripts: {
				src: [ "src/js/*.js" ],
				options: {
					bitwise: true,
					curly: true,
					eqeqeq: true,
					forin: true,
					freeze: true,
					noarg: true,
					nonbsp: true,
					quotmark: "double",
					undef: true,
					unused: true,
					browser: true, // Define globals exposed by modern browsers.
					jquery: true   // Define globals exposed by jQuery.
				}
			}
		},

		uglify: {
			all: {
			    files: [ {
					expand: true,
					cwd: "src/js/",
					src: "*.js",
					dest: "js",
					ext: ".min.js"
				} ]
			}
		},

		phpcs: {
			plugin: {
				src: "./"
			},
			options: {
				bin: "vendor/bin/phpcs --extensions=php --ignore=\"*/vendor/*,*/node_modules/*\"",
				standard: "phpcs.ruleset.xml"
			}
		}

	} );

	grunt.loadNpmTasks( "grunt-postcss" );
	grunt.loadNpmTasks( "grunt-contrib-concat" );
	grunt.loadNpmTasks( "grunt-contrib-csslint" );
	grunt.loadNpmTasks( "grunt-contrib-clean" );
	grunt.loadNpmTasks( "grunt-jscs" );
	grunt.loadNpmTasks( "grunt-contrib-jshint" );
	grunt.loadNpmTasks( "grunt-contrib-uglify" );
	grunt.loadNpmTasks( "grunt-phpcs" );

	// Default task(s).
	grunt.registerTask( "default", [ "concat", "postcss", "csslint", "clean", "jscs", "jshint", "uglify" ] );
};
