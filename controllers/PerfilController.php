<?php

namespace Controllers;

use Model\Usuario;
use MVC\Router;

class PerfilController
{
    /** Lee un campo de $_POST como texto; si llega un arreglo u otro tipo, devuelve ''. */
    private static function texto(string $clave): string
    {
        $v = $_POST[$clave] ?? '';
        return is_string($v) ? $v : '';
    }

    public static function perfil(Router $router)
    {
        isAuth();
        $alertas = [];
        $usuario = Usuario::find((int) $_SESSION['id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificarCsrf();
            // El tutelado solo puede cambiar su nombre; el alias no se edita
            $usuario->nombre = trim(self::texto('nombre'));
            if ($usuario->rol === 'tutor') {
                $usuario->email = trim(self::texto('email'));
            }

            $alertas = $usuario->rol === 'tutor'
                ? $usuario->validar_perfil()
                : (function () use ($usuario) {
                    if (!$usuario->nombre) {
                        Usuario::setAlerta('error', 'El Nombre es Obligatorio');
                    } elseif (mb_strlen($usuario->nombre) > 100) {
                        Usuario::setAlerta('error', 'El nombre es demasiado largo (máximo 100 caracteres)');
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
            $usuario->password_actual = self::texto('password_actual');
            $usuario->password_nuevo = self::texto('password_nuevo');
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
