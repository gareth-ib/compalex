

setup difference models to build SQL commands using eloquent query builder.



https://stackoverflow.com/questions/18236294/how-do-i-get-the-query-builder-to-output-its-raw-sql-query-as-a-string



https://laravel.com/docs/5.6/migrations#creating-columns


Schema::table('users', function (Blueprint $table) {
    $table->string('email');
})
// if this works...
->toSql();


https://laravel.com/docs/5.6/migrations#generating-migrations



https://github.com/Xethron/migrations-generator



https://github.com/DBDiff/DBDiff


