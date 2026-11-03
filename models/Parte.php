<?php

namespace Model;

class Parte extends ActiveRecord
{
    protected static $tabla = 'partes';
    protected static $columnasDB = [
        'id', 'tarea_id', 'titulo', 'orden', 'estado',
        'comentario', 'devuelta', 'revisada_por'
    ];

    public $id;
    public $tarea_id;
    public $titulo;
    public $orden;
    public $estado;
    public $comentario;
    public $devuelta;
    public $revisada_por;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->tarea_id = $args['tarea_id'] ?? '';
        $this->titulo = trim($args['titulo'] ?? '');
        $this->orden = $args['orden'] ?? 0;
        $this->estado = $args['estado'] ?? 'pendiente';
        $this->comentario = $args['comentario'] ?? '';
        $this->devuelta = $args['devuelta'] ?? 0;
        $this->revisada_por = $args['revisada_por'] ?? null;
    }

    /**
     * Máquina de estados de una parte (equivale a doRevisar de app.js).
     * aprobar: hecha -> aprobada | devolver: hecha -> pendiente | reabrir: aprobada -> pendiente
     */
    public function transicionar(string $accion, int $tutorId, string $comentario = ''): void
    {
        $reglas = [
            'aprobar'  => ['hecha',    'aprobada', 'Solo se aprueba lo que el alumno marcó como hecho.'],
            'devolver' => ['hecha',    'pendiente', 'Solo se devuelve lo que está por revisar.'],
            'reabrir'  => ['aprobada', 'pendiente', 'Solo se reabre una parte aprobada.'],
        ];
        if (!isset($reglas[$accion])) {
            throw new \InvalidArgumentException('Acción no válida.');
        }
        [$desde, $hacia, $mensaje] = $reglas[$accion];
        if ($this->estado !== $desde) {
            throw new \DomainException($mensaje);
        }

        $this->estado = $hacia;
        $this->comentario = $accion === 'reabrir' ? '' : mb_substr(trim($comentario), 0, 500);
        $this->devuelta = $accion === 'aprobar' ? 0 : 1;
        $this->revisada_por = $tutorId;
    }
}
