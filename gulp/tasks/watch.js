var config		= require( '../util/loadConfig' ).watch;
var gulp		= require( 'gulp' );

// Watch files for changes, recompile/rebuild
gulp.task( 'watch', function() {
    gulp.watch( config.javascript.front, ['uglify:front'] );
    gulp.watch( config.javascript.admin, ['uglify:admin'] );
    gulp.watch( config.javascript.tinymce, ['uglify:tinymce'] );
    gulp.watch( config.javascript.fes, ['uglify:fes'] );
	gulp.watch( config.sass.front.src, ['sass:front'] );
	gulp.watch( config.sass.admin.src, ['sass:admin'] );
} );