<?php include_once __DIR__ . '/header-dashboard.php' ?>

<div class="contenedor-sm">
    <div class="contenedor-nueva-tarea">
        <button
            type="button"
            class="agregar-tarea"
            id="agregar-tarea"
        >&#43; Nueva Tarea</button>
    </div>

    <!--section  filtros[inicio] -->
    <div
        class="filtros"
        id="filtros"
    >
        <div class="filtros-inputs">

            <h2>Filtros</h2>
            <!--section1 todas[inicio] -->
            <div class="campo">
                <label for="todas">Todas</label>
                <input
                    type="radio"
                    id="todas"
                    name="filtro"
                    value=""
                    checked
                />
            </div>
            <!--!section1 fin - todas[fin] -->
            <!--section2 completadas[inicio] -->
            <div class="campo">
                <label for="completadas">Completadas</label>
                <input
                    type="radio"
                    id="completadas"
                    name="filtro"
                    value="1"
                />
            </div>
            <!--!section2 fin - completadas[fin] -->
            <!--section3 pendientes[inicio] -->
            <div class="campo">
                <label for="pendientes">Pendientes</label>
                <input
                    type="radio"
                    id="pendientes"
                    name="filtro"
                    value="0"
                />
            </div>
            <!--!section3 fin - pendientes[fin] -->
        </div>
    </div>
    <!--!section  fin - filtros[fin] -->

    <!-- section espacio para mostrar las tareas[inicio]  -->
    <ul
        class="listado-tareas"
        id="listado-tareas"
    ></ul>
    <!-- !section fin - espacio para mostrar las tareas[fin]  -->


</div>


<?php
$script = '';
$script .= vite_script('src/js/tareas.js');
$script .= vite_script('https://cdn.jsdelivr.net/npm/sweetalert2@11');
include_once __DIR__ . '/footer-dashboard.php';
?>
