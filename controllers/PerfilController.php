<?php

namespace Controllers;

use Model\Usuario;
use MVC\Router;

class PerfilController
{
    public static function perfil(Router $router)
    {
        isAuth();
        $alertas = [];
        $usuario = Usuario::find((int) $_SESSION['id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificarCsrf();
            // El tutelado solo puede cambiar su nombre; el alias no se edita
            $usuario->nombre = trim($_POST['nombre'] ?? '');
            if ($usuario->rol === 'tutor') {
                $usuario->email = trim($_POST['email'] ?? '');
            }

            $alertas = $usuario->rol === 'tutor'
                ? $usuario->validar_perfil()
                : (function () use ($usuario) {
                    if (!$usuario->nombre) {
                        Usuario::setAlerta('error', 'El Nombre es Obligatorio');
                    }
                    return Usuario::getAlertas();
                })();

            if (empty($alertas)) {
                $existe = $usuario->rol === 'tutor' ? Usuario::where('email', $usuario->email) : false;
                if ($existe && (int) $existe->id !== (int) $usuario->id) {
                    Usuario::setAlerta('error', 'Email no válido, el email pertenece a otra cuenta');
                } else {
                    $usuario->guardar();
                    $_SESSION['nombre'] = $usuario->nombre;
                    Usuario::setAlerta('exito', 'Guardado Correctamente');
                }
                $alertas = Usuario::getAlertas();
            }
        }

        $router->render('perfil/perfil', [
            'titulo' => 'Perfil',
            'usuario' => $usuario,
            'alertas' => $alertas,
        ]);
    }

    public static function cambiar_password(Router $router)
    {
        isAuth();
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificarCsrf();
            $usuario = Usuario::find((int) $_SESSION['id']);
            // Solo estas dos entradas; no se usa sincronizar($_POST) para no aceptar otros campos
            $usuario->password_actual = $_POST['password_actual'] ?? '';
            $usuario->password_nuevo = $_POST['password_nuevo'] ?? '';
            $alertas = $usuario->nuevo_password();

            if (empty($alertas)) {
                if ($usuario->comprobar_password()) {
                    $usuario->password = $usuario->password_nuevo;
                    $usuario->hashPassword();
                    if ($usuario->guardar()) {
                        Usuario::setAlerta('exito', 'Password Actualizado Correctamente');
                    }
                } else {
                    Usuario::setAlerta('error', 'Password Incorrecto');
                }
                $alertas = Usuario::getAlertas();
            }
        }

        $router->render('perfil/cambiar-password', [
            'titulo' => 'Cambiar Password',
            'alertas' => $alertas,
        ]);
    }
}
