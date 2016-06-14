var config      = require('../util/loadConfig').watch;
var gulp        = require('gulp');

// Watch files for changes, recompile/rebuild
gulp.task('watch', function() {
  gulp.watch(config.javascript, ['uglify']);
  gulp.watch(config.sass, ['sass']);
});