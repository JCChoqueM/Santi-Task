<?php

namespace Controllers;

use Model\Usuario;
use Model\Vinculo;
use MVC\Router;

class TutorController
{
    /** Lee un campo de $_POST como texto; si llega un arreglo u otro tipo, devuelve ''. */
    private static function texto(string $clave): string
    {
        $v = $_POST[$clave] ?? '';
        return is_string($v) ? $v : '';
    }

    // GET /dashboard
    public static function index(Router $router)
    {
        isAuth();
        isTutor();
        $router->render('tutor/index', [
            'titulo' => 'Tareas',
            'tutelados' => Vinculo::tuteladosDe((int) $_SESSION['id']),
        ]);
    }

    // GET /crear-tarea
    public static function crear_tarea(Router $router)
    {
        isAuth();
        isTutor();
        $router->render('tutor/nueva-tarea', [
            'titulo' => 'Nueva tarea',
            'tutelados' => Vinculo::tuteladosDe((int) $_SESSION['id']),
        ]);
    }

    // GET /tutelados
    public static function tutelados(Router $router)
    {
        isAuth();
        isTutor();
        $yo = (int) $_SESSION['id'];
        $lista = [];
        foreach (Vinculo::tuteladosDe($yo) as $al) {
            $lista[] = [
                'alumno' => $al,
                'tutores' => Vinculo::tutoresDe((int) $al->id),
                'soyResponsable' => (int) $al->creado_por === $yo,
            ];
        }
        $router->render('tutor/tutelados', [
            'titulo' => 'Tutelados',
            'lista' => $lista,
        ]);
    }

    // POST /api/tutelado
    public static function crear_tutelado()
    {
        isTutor(true);
        verificarCsrf(true);

        $t = new Usuario([
            'rol' => 'tutelado',
            'nombre' => trim(self::texto('nombre')),
            'curso' => trim(self::texto('curso')) ?: null,
            'password' => self::texto('password'),
            'confirmado' => 1,
            'creado_por' => $_SESSION['id'],
        ]);
        $plano = $t->password;                       // solo se muestra esta vez

        $alertas = $t->validarTutelado();
        if (!empty($alertas['error'])) {
            responderJson(['tipo' => 'error', 'mensaje' => $alertas['error'][0]], 400);
        }
        try {
            $t->alias = Usuario::generarAlias($t->nombre);
        } catch (\Exception $e) {
            responderJson(['tipo' => 'error', 'mensaje' => $e->getMessage()], 400);
        }

        $t->hashPassword();
        $r = $t->guardar();
        Vinculo::crearVinculo((int) $_SESSION['id'], (int) $r['id']);

        responderJson([
            'tipo' => 'exito',
            'nombre' => $t->nombre,
            'alias' => $t->alias,
            'password' => $plano,
        ]);
    }

    /** Devuelve el tutelado si el tutor logueado está vinculado; si no, responde 403. */
    private static function alumnoDelTutor(int $id): Usuario
    {
        $al = Usuario::find($id);
        if (!$al || $al->rol !== 'tutelado' || !Vinculo::existe((int) $_SESSION['id'], $id)) {
            responderJson(['tipo' => 'error', 'mensaje' => 'No tienes permiso sobre este tutelado.'], 403);
        }
        return $al;
    }

    // POST /api/tutelado/password
    public static function reset_password()
    {
        isTutor(true);
        verificarCsrf(true);

        $al = self::alumnoDelTutor((int) self::texto('id'));
        $nueva = self::texto('password');
        if (strlen($nueva) < 6) {
            responderJson(['tipo' => 'error', 'mensaje' => 'La contraseña necesita al menos 6 caracteres.'], 400);
        }
        $al->password = $nueva;
        $al->hashPassword();
        $al->guardar();
        responderJson(['tipo' => 'exito', 'mensaje' => 'Contraseña actualizada']);
    }

    // POST /api/vinculo
    public static function vincular()
    {
        isTutor(true);
        verificarCsrf(true);

        $al = self::alumnoDelTutor((int) self::texto('tutelado_id'));
        $correo = strtolower(trim(self::texto('correo')));
        $otro = Usuario::where('email', $correo);

        if (!$otro || $otro->rol !== 'tutor' || !$otro->confirmado) {
            responderJson(['tipo' => 'error', 'mensaje' => 'No existe un tutor con ese correo.'], 404);
        }
        if (Vinculo::existe((int) $otro->id, (int) $al->id)) {
            responderJson(['tipo' => 'error', 'mensaje' => 'Ese tutor ya está vinculado.'], 400);
        }
        Vinculo::crearVinculo((int) $otro->id, (int) $al->id);
        responderJson(['tipo' => 'exito', 'mensaje' => 'Tutor vinculado: ' . $otro->nombre]);
    }

    // POST /api/vinculo/eliminar
    public static function desvincular()
    {
        isTutor(true);
        verificarCsrf(true);

        $yo = (int) $_SESSION['id'];
        $al = self::alumnoDelTutor((int) self::texto('tutelado_id'));
        $tutorId = (int) self::texto('tutor_id');

        if (!Vinculo::existe($tutorId, (int) $al->id)) {
            responderJson(['tipo' => 'error', 'mensaje' => 'Ese tutor no está vinculado.'], 400);
        }
        // Solo el tutor responsable puede quitar a otros
        if ($tutorId !== $yo && (int) $al->creado_por !== $yo) {
            responderJson(['tipo' => 'error', 'mensaje' => 'Solo el tutor responsable puede quitar a otros tutores.'], 403);
        }
        if (count(Vinculo::tutoresDe((int) $al->id)) <= 1) {
            responderJson(['tipo' => 'error', 'mensaje' => 'Es el único tutor. Vincula a otro antes de salir.'], 400);
        }

        Vinculo::quitar($tutorId, (int) $al->id);

        // Si salió el responsable, el siguiente tutor pasa a serlo
        if ((int) $al->creado_por === $tutorId) {
            $sig = Vinculo::tutoresDe((int) $al->id)[0] ?? null;
            if ($sig) {
                $al->creado_por = $sig->id;
                $al->guardar();
            }
        }
        responderJson(['tipo' => 'exito', 'mensaje' => 'Tutor desvinculado']);
    }
}
