<?php
require 'flight/Flight.php';
require 'config/config.php';

Flight::route('/', function(){
    Flight::redirect('/login');
});

Flight::route('/login', function(){
    require 'login.php';
});

Flight::route('/logout', function(){
    require 'logout.php';
});

Flight::route('/users', function(){
    require 'users.php';
});

Flight::route('/user/create', function(){
    require 'user_create.php';
});

Flight::route('/user/edit/@id', function($id){
    $_GET['id'] = $id;
    require 'user_edit.php';
});

Flight::route('/user/delete/@id', function($id){
    $_GET['id'] = $id;
    require 'user_delete.php';
});

Flight::start();
