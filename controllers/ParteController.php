<?php

namespace Controllers;

use Model\Historial;
use Model\Parte;
use Model\Tarea;
use Model\Vinculo;

class ParteController
{
    /** Lee un campo de $_POST como texto; si llega un arreglo u otro tipo, devuelve ''. */
    private static function texto(string $clave): string
    {
        $v = $_POST[$clave] ?? '';
        return is_string($v) ? $v : '';
    }

    // POST /api/parte/hecha   id  (solo el tutelado dueño de la tarea)
    public static function marcar_hecha()
    {
        isTutelado(true);
        verificarCsrf(true);

        $parte = Parte::find((int) self::texto('id'));
        $tarea = $parte ? Tarea::find((int) $parte->tarea_id) : null;
        if (!$tarea || (int) $tarea->tutelado_id !== (int) $_SESSION['id']) {
            responderJson(['tipo' => 'error', 'mensaje' => 'Esa tarea no es tuya.'], 403);
        }
        if ($parte->estado !== 'pendiente') {
            responderJson(['tipo' => 'error', 'mensaje' => 'Esta parte ya no está pendiente.'], 400);
        }

        $parte->estado = 'hecha';
        $parte->comentario = '';
        $parte->devuelta = 0;
        $parte->guardar();
        Historial::registrar((int) $tarea->id, $parte->titulo, 'hecha', (int) $_SESSION['id']);
        responderJson(['tipo' => 'exito', 'mensaje' => 'Listo, tu tutor lo revisará']);
    }

    // POST /api/parte/revisar   id, accion (aprobar|devolver|reabrir), comentario
    public static function revisar()
    {
        isTutor(true);
        verificarCsrf(true);

        $accion = self::texto('accion');
        $parte = Parte::find((int) self::texto('id'));
        $tarea = $parte ? Tarea::find((int) $parte->tarea_id) : null;
        if (!$tarea || !Vinculo::existe((int) $_SESSION['id'], (int) $tarea->tutelado_id)) {
            responderJson(['tipo' => 'error', 'mensaje' => 'No tienes permiso sobre esta tarea.'], 403);
        }
        try {
            $parte->transicionar($accion, (int) $_SESSION['id'], self::texto('comentario'));
        } catch (\InvalidArgumentException | \DomainException $e) {
            responderJson(['tipo' => 'error', 'mensaje' => $e->getMessage()], 400);
        }
        $parte->guardar();
        Historial::registrar((int) $tarea->id, $parte->titulo, $accion, (int) $_SESSION['id']);
        responderJson(['tipo' => 'exito', 'progreso' => $tarea->progreso()]);
    }

    // POST /api/parte   url, titulo  (añade una parte a una tarea existente)
    public static function crear()
    {
        isTutor(true);
        verificarCsrf(true);

        $tarea = Tarea::where('url', self::texto('url'));
        if (!$tarea || !Vinculo::existe((int) $_SESSION['id'], (int) $tarea->tutelado_id)) {
            responderJson(['tipo' => 'error', 'mensaje' => 'No tienes permiso sobre esta tarea.'], 403);
        }
        $titulo = trim(self::texto('titulo'));
        if ($titulo === '') {
            responderJson(['tipo' => 'error', 'mensaje' => 'Escribe el nombre de la parte.'], 400);
        }
        if (mb_strlen($titulo) > 255) {
            responderJson(['tipo' => 'error', 'mensaje' => 'El nombre de la parte es demasiado largo (máximo 255 caracteres).'], 400);
        }
        $p = new Parte(['tarea_id' => $tarea->id, 'titulo' => $titulo, 'orden' => count($tarea->partes())]);
        $r = $p->guardar();
        responderJson(['tipo' => 'exito', 'id' => $r['id']]);
    }
}
