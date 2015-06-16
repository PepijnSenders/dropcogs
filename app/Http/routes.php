<?php

Route::group(['prefix' => 'pages'], function () {

    Route::get('login', ['as' => 'pages.users.login', 'uses' => 'User\PagesController@login']);

    Route::group(['middleware' => 'dropbox.auth'], function () {

        Route::get('home', ['as' => 'pages.home', 'uses' => 'PagesController@home']);

        Route::group(['prefix' => 'users', 'namespace' => 'User'], function () {
            Route::get('configure/{path?}', ['as' => 'pages.users.configure', 'uses' => 'PagesController@configure'])
                ->where('path', '.*');
        });

    });

});

Route::group(['prefix' => 'dropbox', 'namespace' => 'User'], function () {

    Route::get('auth', ['as' => 'dropbox.auth', 'uses' => 'DropboxController@auth']);
    Route::get('analyze', ['middleware' => 'dropbox.auth', 'as' => 'dropbox.analyze', 'uses' => 'DropboxController@analyze']);

});

Route::group(['prefix' => 'folders', 'namespace' => 'User', 'middleware' => 'dropbox.auth'], function () {

    Route::post('add', ['as' => 'folders.add', 'uses' => 'FoldersController@add']);
    Route::post('remove', ['as' => 'folders.remove', 'uses' => 'FoldersController@remove']);

});
