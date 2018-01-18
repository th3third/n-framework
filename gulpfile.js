var gulp = require('gulp'); 

// Include Our Plugins
var sass   = require('gulp-sass');
var concat = require('gulp-concat');
var minify = require('gulp-minify');
var cleanCSS = require('gulp-clean-css');
var cssGlobbing = require('gulp-css-globbing');

// Compile Our Sass
gulp.task('sass', function() {
    gulp.src('src/scss/base.scss')
        .pipe(cssGlobbing({extensions: [".css", ".scss", ".sass"]}))
        .pipe(sass().on('error', sass.logError))
        .pipe(cleanCSS())
        .pipe(concat("all.min.css"))
        .pipe(gulp.dest('src/css'));
    return gulp.src('src/scss/custom/*.scss')
        .pipe(cssGlobbing({extensions: [".css", ".scss", ".sass"]}))
        .pipe(sass().on('error', sass.logError))
        .pipe(cleanCSS())
        .pipe(gulp.dest('src/css'));
});

// Concatenate & Minify JS
gulp.task('scripts', function() {
    gulp.src(['src/scripts/lib/*.js', 'src/scripts/custom/*.js'])
        .pipe(concat('nykc.js'))
        .pipe(minify())
        .pipe(gulp.dest('src/js'));
    return gulp.src(['src/scripts/modules/*.js'])
        .pipe(minify({
            ext: {
                min: ".js"
            }
        }))
        .pipe(gulp.dest('src/js'));
});

// Watch Files For Changes
gulp.task('watch', function() {
    gulp.watch('src/scripts/**/*.js', ['scripts']);
    gulp.watch('src/scss/**/*.scss', ['sass']);
});

// Default Task
gulp.task('default', ['sass', 'scripts', 'watch']);
gulp.task('css', ['sass']);