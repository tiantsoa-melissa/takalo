<?php
require 'flight/Flight.php';
require 'config/config.php';

Flight::route('/', function(){
    Flight::redirect('/home');
});

Flight::route('/home', function(){
    require 'home.php';
});

Flight::route('/objet/@id', function($id){
    $_GET['id'] = $id;
    require 'objet_detail.php';
});

Flight::route('/recherche', function(){
    require 'recherche.php';
});

Flight::route('/echange/demande', function(){
    require 'demande_echange.php';
});

Flight::route('/echange/gestion', function(){
    require 'gestion_echanges.php';
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