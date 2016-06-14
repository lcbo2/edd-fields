var $             = require('gulp-load-plugins')();
var autoprefixer  = require('gulp-autoprefixer');
var config        = require('../util/loadConfig').sass;
var gulp          = require('gulp');
var sass          = require('gulp-sass');
var concat        = require('gulp-concat');
var notify        = require('gulp-notify');
var fs            = require('fs');
var pkg           = JSON.parse(fs.readFileSync('./package.json'));

gulp.task('sass', function() {

  return gulp.src(config.src)
    .pipe($.sourcemaps.init())
    .pipe($.sass()
      .on('error', $.sass.logError))
    .pipe(concat('style.css'))
    .pipe(autoprefixer(config.compatibility))
    .pipe($.cssnano())
    .pipe($.sourcemaps.write('.'))
    .pipe(gulp.dest(config.dest.root))
    .pipe(notify( {
      title: pkg.name,
      message: 'SASS Complete'
    }));
});