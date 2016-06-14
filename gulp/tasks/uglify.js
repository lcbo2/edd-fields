var $             = require('gulp-load-plugins')();
var config        = require('../util/loadConfig').javascript;
var gulp          = require('gulp');
var notify        = require('gulp-notify');
var fs            = require('fs');
var pkg           = JSON.parse(fs.readFileSync('./package.json'));

gulp.task('uglify', function() {
  var uglify = $.uglify()
    .on('error', function (e) {
      console.log(e);
    });

  return gulp.src(config.src)
    .pipe($.sourcemaps.init())
    .pipe($.babel())
    .pipe($.concat(config.filename))
    .pipe(uglify)
    .pipe($.sourcemaps.write('.'))
    .pipe(gulp.dest(config.dest.root))
    .pipe(notify( {
      title: pkg.name,
      message: 'JS Complete'
    }));
});