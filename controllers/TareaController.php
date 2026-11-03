<?php

namespace Controllers;

use Model\Tarea;
use Model\Parte;
use Model\Vinculo;
use Model\Historial;

class TareaController
{
    // Límites acordes con las columnas: titulo VARCHAR(255), descripcion TEXT
    private const MAX_TITULO = 255;
    private const MAX_DESCRIPCION = 5000;
    private const MAX_PARTES = 50;

    /** Lee un campo de $_POST o $_GET como texto; si llega un arreglo u otro tipo, devuelve ''. */
    private static function texto(string $clave, array $fuente): string
    {
        $v = $fuente[$clave] ?? '';
        return is_string($v) ? $v : '';
    }

    /** Sin sesión, la API responde JSON 403 (no redirige). */
    private static function sesion(): void
    {
        if (!isset($_SESSION['login'])) {
            responderJson(['tipo' => 'error', 'mensaje' => 'Acceso denegado.'], 403);
        }
    }

    /** Devuelve el mensaje del primer error de los campos, o null si todo es válido. */
    private static function errorDeCampos(string $titulo, string $descripcion, string $fecha, array $partes): ?string
    {
        if ($titulo === '') {
            return 'La tarea necesita un título';
        }
        if (mb_strlen($titulo) > self::MAX_TITULO) {
            return 'El título no puede pasar de ' . self::MAX_TITULO . ' caracteres.';
        }
        if (mb_strlen($descripcion) > self::MAX_DESCRIPCION) {
            return 'La descripción no puede pasar de ' . self::MAX_DESCRIPCION . ' caracteres.';
        }
        if ($fecha !== '') {
            $d = \DateTime::createFromFormat('!Y-m-d', $fecha);
            if (!$d || $d->format('Y-m-d') !== $fecha) {
                return 'La fecha límite no es válida.';
            }
        }
        if (!$partes) {
            return 'Añade al menos una parte';
        }
        if (count($partes) > self::MAX_PARTES) {
            return 'Una tarea admite como máximo ' . self::MAX_PARTES . ' partes.';
        }
        foreach ($partes as $p) {
            if (mb_strlen($p) > self::MAX_TITULO) {
                return 'Cada parte puede tener hasta ' . self::MAX_TITULO . ' caracteres.';
            }
        }
        return null;
    }

    /** De una lista enviada por el cliente, conserva solo los textos no vacíos (ya recortados). */
    private static function partesDeLista(mixed $lista): array
    {
        $salida = [];
        foreach ((is_array($lista) ? $lista : []) as $p) {
            if (!is_string($p)) {
                continue;
            }
            $p = trim($p);
            if ($p !== '') {
                $salida[] = $p;
            }
        }
        return $salida;
    }

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
        self::sesion();
        $yo = (int) $_SESSION['id'];
        $filtro = (int) self::texto('tutelado', $_GET);

        if ($_SESSION['rol'] === 'tutor') {
            $tareas = [];
            foreach (Vinculo::tuteladosDe($yo) as $alumno) {
                if ($filtro && $filtro !== (int) $alumno->id) {
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
        isTutor(true);
        verificarCsrf(true);

        $tuteladoId = (int) self::texto('tutelado_id', $_POST);
        if (!Vinculo::existe((int) $_SESSION['id'], $tuteladoId)) {
            responderJson(['tipo' => 'error', 'mensaje' => 'No tienes permiso sobre este tutelado.'], 403);
        }

        $titulo = trim(self::texto('titulo', $_POST));
        $descripcion = trim(self::texto('descripcion', $_POST));
        $fecha = trim(self::texto('fecha_limite', $_POST));
        $partes = self::partesDeLista($_POST['partes'] ?? []);

        $error = self::errorDeCampos($titulo, $descripcion, $fecha, $partes);
        if ($error !== null) {
            responderJson(['tipo' => 'error', 'mensaje' => $error], 400);
        }

        // Solo estos campos salen del cliente; el id, el autor y la url los fija el servidor (C-04)
        $tarea = new Tarea([
            'url' => Tarea::generarUrl(),
            'tutelado_id' => $tuteladoId,
            'creada_por' => $_SESSION['id'],
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'fecha_limite' => $fecha,
        ]);

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
        isTutor(true);
        verificarCsrf(true);

        $tarea = self::tareaDelTutor(self::texto('url', $_POST));

        // Solo estos campos se aceptan del cliente (C-04)
        $titulo = trim(self::texto('titulo', $_POST));
        $descripcion = trim(self::texto('descripcion', $_POST));
        $fecha = trim(self::texto('fecha_limite', $_POST));

        // partes llega como JSON; se descartan los elementos que no sean {id, titulo} con titulo de texto
        $crudo = self::texto('partes', $_POST);
        $decodificado = json_decode($crudo === '' ? '[]' : $crudo, true);
        $enviadas = [];
        foreach ((is_array($decodificado) ? $decodificado : []) as $x) {
            if (!is_array($x) || !is_string($x['titulo'] ?? null)) {
                continue;
            }
            $t = trim($x['titulo']);
            if ($t === '') {
                continue;
            }
            $id = $x['id'] ?? null;
            $enviadas[] = ['id' => is_numeric($id) ? (int) $id : 0, 'titulo' => $t];
        }

        $error = self::errorDeCampos($titulo, $descripcion, $fecha, array_column($enviadas, 'titulo'));
        if ($error !== null) {
            responderJson(['tipo' => 'error', 'mensaje' => $error], 400);
        }

        $tarea->titulo = $titulo;
        $tarea->descripcion = $descripcion;
        $tarea->fecha_limite = $fecha !== '' ? $fecha : null;
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
        isTutor(true);
        verificarCsrf(true);

        $tarea = self::tareaDelTutor(self::texto('url', $_POST));
        $tarea->eliminar();   // partes e historial caen por ON DELETE CASCADE
        responderJson(['tipo' => 'exito', 'mensaje' => 'Tarea eliminada']);
    }

    // POST /api/tarea/duplicar   destinos[] = ids de tutelados
    public static function duplicar()
    {
        isTutor(true);
        verificarCsrf(true);

        $original = self::tareaDelTutor(self::texto('url', $_POST));
        // Solo ids enteros positivos, sin repetidos; cualquier otro valor se ignora
        $crudo = $_POST['destinos'] ?? [];
        $unicos = [];
        foreach ((is_array($crudo) ? $crudo : []) as $d) {
            if (is_string($d) && ctype_digit($d) && (int) $d > 0) {
                $unicos[(int) $d] = true;
            }
        }
        $destinos = array_keys($unicos);
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
        self::sesion();
        $tarea = Tarea::where('url', self::texto('url', $_GET));
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
