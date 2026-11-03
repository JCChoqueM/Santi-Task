<?php

namespace Controllers;

use MVC\Router;

class AlumnoController
{
    // GET /mis-tareas
    public static function index(Router $router)
    {
        isAuth();
        isTutelado();
        $router->render('alumno/index', ['titulo' => 'Mis tareas']);
    }
}
