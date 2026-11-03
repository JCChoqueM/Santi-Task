<?php

namespace Controllers;

use Model\Tarea;
use Model\Parte;
use Model\Vinculo;
use Model\Historial;

class TareaController
{
    /** Carga una tarea por url y exige que el tutor esté vinculado a su tutelado. */
    private static function tareaDelTutor(string $url): Tarea
    {
        $tarea = Tarea::where('url', $url);
        if (!$tarea || !Vinculo::existe((int) $_SESSION['id'], (int) $tarea->tutelado_id)) {
            responderJson(['tipo' => 'error', 'mensaje' => 'No tienes permiso sobre esta tarea.'], 403);
        }
        return $tarea;
    }

    /** Convierte una tarea en el arreglo que consume el JavaScript. */
    private static function aArreglo(Tarea $t): array
    {
        $partes = $t->partes();
        return [
            'url' => $t->url,
            'titulo' => $t->titulo,
            'descripcion' => $t->descripcion,
            'fecha_limite' => $t->fecha_limite,
            'tutelado_id' => (int) $t->tutelado_id,
            'vencida' => $t->vencida(),
            'progreso' => $t->progreso(),
            'partes' => array_map(fn($p) => [
                'id' => (int) $p->id,
                'titulo' => $p->titulo,
                'estado' => $p->estado,
                'comentario' => $p->comentario,
                'devuelta' => (bool) $p->devuelta,
            ], $partes),
        ];
    }

    // GET /api/tareas  (tutor y tutelado)
    public static function index()
    {
        isAuth();
        $yo = (int) $_SESSION['id'];

        if ($_SESSION['rol'] === 'tutor') {
            $tareas = [];
            foreach (Vinculo::tuteladosDe($yo) as $alumno) {
                if (!empty($_GET['tutelado']) && (int) $_GET['tutelado'] !== (int) $alumno->id) {
                    continue;
                }
                $tareas = array_merge($tareas, Tarea::belongsTo('tutelado_id', $alumno->id));
            }
        } else {
            $tareas = Tarea::belongsTo('tutelado_id', $yo);
        }

        $salida = [];
        foreach ($tareas as $t) {
            $item = self::aArreglo($t);
            if (!empty($_GET['porRevisar'])) {
                $hay = array_filter($item['partes'], fn($p) => $p['estado'] === 'hecha');
                if (!$hay) continue;
            }
            $salida[] = $item;
        }
        responderJson(['tareas' => $salida]);
    }

    // POST /api/tarea
    public static function crear()
    {
        isAuth();
        isTutor(true);
        verificarCsrf(true);

        $tuteladoId = (int) ($_POST['tutelado_id'] ?? 0);
        if (!Vinculo::existe((int) $_SESSION['id'], $tuteladoId)) {
            responderJson(['tipo' => 'error', 'mensaje' => 'No tienes permiso sobre este tutelado.'], 403);
        }

        $tarea = new Tarea($_POST);
        $tarea->id = null;   // el cliente no decide el id: siempre es un INSERT (C-04)
        $tarea->tutelado_id = $tuteladoId;
        $tarea->creada_por = $_SESSION['id'];
        $tarea->url = Tarea::generarUrl();

        $partes = array_values(array_filter(array_map('trim', (array) ($_POST['partes'] ?? []))));
        $alertas = $tarea->validar();
        if (!$partes) {
            $alertas['error'][] = 'Añade al menos una parte';
        }
        if (!empty($alertas['error'])) {
            responderJson(['tipo' => 'error', 'mensaje' => $alertas['error'][0]], 400);
        }

        $r = $tarea->guardar();
        foreach ($partes as $i => $titulo) {
            (new Parte(['tarea_id' => $r['id'], 'titulo' => $titulo, 'orden' => $i]))->guardar();
        }
        responderJson(['tipo' => 'exito', 'url' => $tarea->url, 'mensaje' => 'Tarea creada']);
    }

    // POST /api/tarea/actualizar
    // partes = JSON: [{"id": 12, "titulo": "..."}, {"id": null, "titulo": "nueva"}]
    public static function actualizar()
    {
        isAuth();
        isTutor(true);
        verificarCsrf(true);

        $tarea = self::tareaDelTutor($_POST['url'] ?? '');

        // Solo estos campos se aceptan del cliente (C-04)
        $tarea->titulo = trim($_POST['titulo'] ?? '');
        $tarea->descripcion = trim($_POST['descripcion'] ?? '');
        $tarea->fecha_limite = ($_POST['fecha_limite'] ?? '') ?: null;

        $alertas = $tarea->validar();
        $enviadas = json_decode($_POST['partes'] ?? '[]', true);
        $enviadas = array_values(array_filter(
            is_array($enviadas) ? $enviadas : [],
            fn($x) => trim((string) ($x['titulo'] ?? '')) !== ''
        ));
        if (!$enviadas) {
            $alertas['error'][] = 'La tarea necesita al menos una parte';
        }
        if (!empty($alertas['error'])) {
            responderJson(['tipo' => 'error', 'mensaje' => $alertas['error'][0]], 400);
        }

        $tarea->guardar();

        // Partes actuales de esta tarea, por id
        $actuales = [];
        foreach ($tarea->partes() as $p) {
            $actuales[(int) $p->id] = $p;
        }

        $conservar = [];
        foreach ($enviadas as $orden => $x) {
            $id = (int) ($x['id'] ?? 0);
            if ($id && isset($actuales[$id])) {          // parte existente de ESTA tarea
                $p = $actuales[$id];
                $p->titulo = trim($x['titulo']);
                $p->orden = $orden;
                $p->guardar();
                $conservar[$id] = true;
            } else {                                     // parte nueva
                (new Parte([
                    'tarea_id' => $tarea->id,
                    'titulo' => trim($x['titulo']),
                    'orden' => $orden,
                ]))->guardar();
            }
        }
        foreach ($actuales as $id => $p) {               // las que ya no vienen se borran
            if (!isset($conservar[$id])) {
                $p->eliminar();
            }
        }
        responderJson(['tipo' => 'exito', 'mensaje' => 'Tarea actualizada']);
    }

    // POST /api/tarea/eliminar
    public static function eliminar()
    {
        isAuth();
        isTutor(true);
        verificarCsrf(true);

        $tarea = self::tareaDelTutor($_POST['url'] ?? '');
        $tarea->eliminar();   // partes e historial caen por ON DELETE CASCADE
        responderJson(['tipo' => 'exito', 'mensaje' => 'Tarea eliminada']);
    }

    // POST /api/tarea/duplicar   destinos[] = ids de tutelados
    public static function duplicar()
    {
        isAuth();
        isTutor(true);
        verificarCsrf(true);

        $original = self::tareaDelTutor($_POST['url'] ?? '');
        $destinos = array_unique(array_map('intval', (array) ($_POST['destinos'] ?? [])));
        if (!$destinos) {
            responderJson(['tipo' => 'error', 'mensaje' => 'Elige al menos un tutelado.'], 400);
        }
        foreach ($destinos as $d) {
            if (!Vinculo::existe((int) $_SESSION['id'], $d)) {
                responderJson(['tipo' => 'error', 'mensaje' => 'No estás vinculado a uno de los tutelados elegidos.'], 403);
            }
        }

        $partes = $original->partes();
        foreach ($destinos as $d) {
            $copia = new Tarea([
                'url' => Tarea::generarUrl(),
                'tutelado_id' => $d,
                'creada_por' => $_SESSION['id'],
                'titulo' => $original->titulo,
                'descripcion' => $original->descripcion,
                'fecha_limite' => $original->fecha_limite,
            ]);
            $r = $copia->guardar();
            foreach ($partes as $i => $p) {
                (new Parte(['tarea_id' => $r['id'], 'titulo' => $p->titulo, 'orden' => $i]))->guardar();
            }
        }
        responderJson(['tipo' => 'exito', 'copiadas' => count($destinos)]);
    }

    // GET /api/historial?url=...  (tutelado dueño o tutor vinculado)
    public static function historial()
    {
        isAuth();
        $tarea = Tarea::where('url', $_GET['url'] ?? '');
        $permitido = $tarea && (
            ($_SESSION['rol'] === 'tutelado' && (int) $tarea->tutelado_id === (int) $_SESSION['id'])
            || ($_SESSION['rol'] === 'tutor' && Vinculo::existe((int) $_SESSION['id'], (int) $tarea->tutelado_id))
        );
        if (!$permitido) {
            responderJson(['tipo' => 'error', 'mensaje' => 'No tienes permiso sobre esta tarea.'], 403);
        }

        $filas = Historial::SQL(
            "SELECT h.*, u.nombre AS nombre_usuario
             FROM historial h JOIN usuarios u ON u.id = h.por
             WHERE h.tarea_id = " . (int) $tarea->id . " ORDER BY h.id DESC"
        );
        responderJson(['historial' => array_map(fn($h) => [
            'parte' => $h->parte,
            'accion' => $h->accion,
            'por' => $h->nombre_usuario ?? '',
            'cuando' => $h->creado_en ?? '',
        ], $filas)]);
    }
}
