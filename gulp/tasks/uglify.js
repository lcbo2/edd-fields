var $             = require('gulp-load-plugins')();
var config        = require('../util/loadConfig').javascript;
var gulp          = require('gulp');
var sequence      = require('run-sequence');
var notify        = require('gulp-notify');
var fs            = require('fs');
var pkg           = JSON.parse(fs.readFileSync('./package.json'));

gulp.task('front-uglify', function() {

    var uglify = $.uglify()
    .on('error', function (e) {
        console.log(e);
    });

    return gulp.src(config.front.src)
        .pipe($.sourcemaps.init())
        .pipe($.babel())
        .pipe($.concat(config.front.filename))
        .pipe(uglify)
        .pipe($.sourcemaps.write('.'))
        .pipe(gulp.dest(config.dest.root))
        .pipe(notify( {
        title: pkg.name,
        message: 'JS Complete'
    }));

});

gulp.task('admin-uglify', function() {
    
    var uglify = $.uglify()
    .on('error', function (e) {
        console.log(e);
    });

    return gulp.src(config.admin.src)
        .pipe($.sourcemaps.init())
        .pipe($.babel())
        .pipe($.concat(config.admin.filename))
        .pipe(uglify)
        .pipe($.sourcemaps.write('.'))
        .pipe(gulp.dest(config.dest.root))
        .pipe(notify( {
        title: pkg.name,
        message: 'Admin JS Complete'
    }));

});

gulp.task('uglify', function(done) {
    sequence('front-uglify', 'admin-uglify', done); 
});