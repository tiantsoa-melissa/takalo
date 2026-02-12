<?php
session_start();
session_destroy();
Flight::redirect('/login');
