<?php

namespace Controllers;

use Model\Proyecto;
use Model\Usuario;
use MVC\Router;

class DashboardController
{
    public static function index(Router $router)
    {
        session_start();

        isAuth();
        $id = $_SESSION['id'];

        $proyectos = Proyecto::belongsTo('propietarioId', $id);
        // debuguear($proyectos);
        $router->render('dashboard/index', [
            'titulo' => 'Proyectos',
            'proyectos' => $proyectos
        ]);
    }
    public static function crear_proyecto(Router $router)
    {
        session_start();

        isAuth();

        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $proyecto = new Proyecto($_POST);
            // debuguear($proyecto);
            // Validar
            $alertas = $proyecto->validarProyecto();

            if (empty($alertas)) {
                // debuguear($proyecto);

                //Generar una URL unica
                $hash = md5(uniqid());
                $proyecto->url = $hash;

                //Almacenar el creador del proyecto
                $proyecto->propietarioId = $_SESSION['id'];


                // Guardar el proyecto
                $proyecto->guardar();

                // Redireccionar
                header('Location: /proyecto?id=' . $proyecto->url);
            }

        }


        $router->render('dashboard/crear-proyecto', [
            'alertas' => $alertas,
            'titulo' => 'Crear Proyecto',
        ]);
    }

    public static function proyecto(Router $router)
    {
        session_start();

        isAuth();
        $token = $_GET['id'] ?? null;
        // debuguear($token);

        if (!$token)
            header('Location: /dashboard');

        // debuguear($_SESSION);
        //Revisar que la persona que visita el proyecto, es quien lo creo
        $proyecto = Proyecto::where('url', $token);
        // debuguear($proyecto);

        if (!$proyecto || $proyecto->propietarioId !== $_SESSION['id']) {
            header('Location: /dashboard');
        }

        // debuguear($proyecto);


        $router->render('dashboard/proyecto', [
            'titulo' => $proyecto->proyecto
        ]);
    }


    public static function perfil(Router $router)
    {
        session_start();
        isAuth();
        $alertas = [];
        $usuario = Usuario::find($_SESSION['id']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $usuario->sincronizar($_POST);

            $alertas = $usuario->validar_perfil();
            if (empty($alertas)) {

                $existeUsuario = Usuario::where('email', $usuario->email);
                if ($existeUsuario && $existeUsuario->id !== $usuario->id) {

                    //Mensaje de error
                    Usuario::setAlerta('error', 'Email no válido, el email pertenece a otra cuenta');
                    $alertas = Usuario::getAlertas();
                } else {
                    //guardar el usuario
                    $usuario->guardar();
                    Usuario::setAlerta('exito', 'Guardado Correctamente');
                    $alertas = Usuario::getAlertas();
                    //Asigar el nombre de la sesion
                    $_SESSION['nombre'] = $usuario->nombre;
                }

            }


        }

        $router->render('dashboard/perfil', [
            'titulo' => 'Perfil',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function cambiar_password(Router $router)
    {
        session_start();
        isAuth();

        $alertas = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $usuario = Usuario::find($_SESSION['id']);
            //sincronizar con los datos del usuario
            $usuario->sincronizar($_POST);

            $alertas = $usuario->nuevo_password();


            if (empty($alertas)) {
                //guardar el nuevo password
                $resultado = $usuario->comprobar_password();
                if ($resultado) {
                    $usuario->password = $usuario->password_nuevo;
                    //Eliminar propiedades no necesarias
                    unset($usuario->password_actual);
                    unset($usuario->password_nuevo);
                    //hash el nuevo password
                    $usuario->hashPassword();
                    //actualizar
                    $resultado = $usuario->guardar();

                    if ($resultado) {
                        Usuario::setAlerta('exito', 'Password Actualizado Correctamente');
                        $alertas = Usuario::getAlertas();
                    }


                } else {
                    Usuario::setAlerta('error', 'Password Incorrecto');
                    $alertas = Usuario::getAlertas();
                }
            }
        }


        $router->render('dashboard/cambiar-password', [
            'titulo' => 'Cambiar Password',
            'alertas' => $alertas

        ]);
    }


}