var gulp = require('gulp'),
    concat = require('gulp-concat'),
    minifyJs = require('gulp-uglify'),
    uglifycss = require('gulp-uglifycss'),
    less = require('gulp-less'),
    rename = require("gulp-rename"),
    notify = require("gulp-notify"),
    clean = require('gulp-clean');

gulp.task('vendors-css', function () {
    gulp.src([
        'web_src/frontend-vendors/bootstrap/dist/css/bootstrap.css',
        'web_src/frontend-vendors/bootstrap/dist/css/bootstrap-theme.css',
        'web_src/frontend-vendors/font-awesome/css/font-awesome.css',
        'web_src/frontend-vendors/bootstrap-select/dist/css/bootstrap-select.css',
        'web_src/frontend-vendors/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css',
        'web_src/frontend-vendors/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css',
        'web_src/frontend-vendors/selectize/dist/css/selectize.css',
        'web_src/frontend-vendors/selectize/dist/css/selectize.bootstrap3.css',
        'web_src/frontend-vendors/fullcalendar/dist/fullcalendar.css',
        'web_src/frontend-vendors/jquery-colorbox/example3/colorbox.css'
    ])
        .pipe(concat('vendors.min.css'))
        .pipe(uglifycss())
        .pipe(rename("vendors.min.css"))
        .pipe(gulp.dest('web/css/'));
});

gulp.task('custom-css', function() {
    gulp.src(['web_src/css/main.less'])
        .pipe(less({compress: true}))
        .pipe(uglifycss())
        .pipe(rename("custom.min.css"))
        .pipe(gulp.dest('web/css/'));
        //.pipe(notify("Gulp watch: custom-css task completed."));
});

gulp.task('vendors-js', function() {
    gulp.src([
        'web_src/frontend-vendors/jquery/dist/jquery.js',
        'web_src/frontend-vendors/bootstrap/dist/js/bootstrap.js',
        'web_src/frontend-vendors/moment/moment.js',
        'web_src/frontend-vendors/moment/locale/uk.js',
        'web_src/frontend-vendors/bootstrap-select/dist/js/bootstrap-select.js',
        'web_src/frontend-vendors/bootstrap-datepicker/dist/js/bootstrap-datepicker.js',
        'web_src/frontend-vendors/bootstrap-datepicker/dist/locales/bootstrap-datepicker.uk.min.js',
        'web_src/frontend-vendors/jsonform/deps/underscore.js',
        'web_src/frontend-vendors/jsonform/deps/opt/jsv.js',
        'web_src/frontend-vendors/jsonform/deps/opt/ace/ace.js',
        'web_src/js/jsonForm_bootstrap3.js',
        'web_src/frontend-vendors/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',
        'web_src/frontend-vendors/selectize/dist/js/standalone/selectize.js',
        'web_src/frontend-vendors/fullcalendar/dist/fullcalendar.js',
        'web_src/frontend-vendors/fullcalendar/dist/lang-all.js',
        'web_src/frontend-vendors/jquery-colorbox/jquery.colorbox.js'
    ])
        .pipe(concat('vendors-js.min.js'))
        .pipe(minifyJs())
        .pipe(rename("vendors.min.js"))
        .pipe(gulp.dest('web/js/'));
});

gulp.task('custom-js', function() {
    gulp.src('web/js/**/*.js')
        .pipe(concat('app.min.js'))
        .pipe(minifyJs())
        .pipe(rename("custom.min.js"))
        .pipe(gulp.dest('web/js/'));
        //.pipe(notify("Gulp watch: custom-js task completed."));
});

gulp.task('fonts', function(){
    gulp.src([
        'web_src/frontend-vendors/bootstrap/fonts/*',
        'web_src/frontend-vendors/font-awesome/fonts/*'
    ])
        .pipe(gulp.dest('web/fonts/'));
});

gulp.task('images', function(){
    gulp.src([
        'web_src/img/*'
    ])
        .pipe(gulp.dest('web/img/'));
});

gulp.task('imagesColorBox', function(){
    gulp.src([
            'web_src/frontend-vendors/jquery-colorbox/example3/images/*'
        ])
        .pipe(gulp.dest('web/css/images/'));
});

gulp.task('clean', function () {
    return gulp.src(['web/css/*', 'web/js/*', 'web/fonts/*', 'web/img/*'])
        .pipe(clean());
});

gulp.task('default', ['clean'], function () {
    var tasks = ['vendors-css', 'custom-css', 'vendors-js', 'custom-js', 'fonts', 'images', 'imagesColorBox'];

    tasks.forEach(function (val) {
        gulp.start(val);
    });
});

gulp.task('watch', function () {
    var css = gulp.watch('web_src/css/*.css', ['custom-css']),
        less = gulp.watch('web_src/css/*.less', ['custom-css']),
        js = gulp.watch('web_src/js/**/*.js', ['custom-js']);
});
