'use strict';

var gulp = require('gulp');
var sass = require('gulp-sass');

gulp.task('sass', function () {
 return gulp.src('./web/scss/*.scss')
   .pipe(sass().on('error', sass.logError))
   .pipe(gulp.dest('./web/css'));
});

gulp.task('sass:watch', function () {
 gulp.watch('./web/scss/*.scss', ['sass']);
});