# ClaudeTask

Aplicación web para que un **tutor** asigne tareas a sus **tutelados** (alumnos), divididas en partes, y las revise. El alumno marca cada parte como hecha; el tutor la aprueba o la devuelve con un comentario. Está hecha en PHP 8 (MVC propio con ActiveRecord), MySQL 8, Docker y Vite/SCSS, sobre la base de UpTask.

La documentación completa (diseño, pruebas, errores conocidos) está en [`docs_santitask.md`](docs_santitask.md). Este archivo tiene lo necesario para instalar y usar la aplicación.

## 1. Instalación desde cero

### Requisitos

- Docker con Docker Compose v2 (el comando `docker compose`).
- Puertos libres en el equipo: **8082** (aplicación), **5173** (Vite, solo en desarrollo) y **3308** (MySQL, solo accesible desde el propio equipo).
- Una cuenta SMTP para enviar correos. **Es obligatoria**: al registrarse, el tutor recibe un enlace de confirmación, y si el correo no sale la cuenta no se crea. Para probar sirve un buzón de pruebas (Ethereal, Mailtrap).
- Internet: para descargar las imágenes y dependencias la primera vez, y para las fuentes de Google en el navegador.

### Pasos

**1. Crea el archivo `.env`** a partir del ejemplo y genera dos claves aleatorias (la de la aplicación y la de administración de MySQL):

```bash
cp .env.example .env
sed -i "s/^DB_PASS=.*/DB_PASS=$(head -c 16 /dev/urandom | od -An -tx1 | tr -d ' \n')/" .env
sed -i "s/^MYSQL_ROOT_PASSWORD=.*/MYSQL_ROOT_PASSWORD=$(head -c 16 /dev/urandom | od -An -tx1 | tr -d ' \n')/" .env
```

Abre `.env` y completa los datos del correo y la dirección:

| Variable | Qué poner |
| --- | --- |
| `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD` | Los datos de tu servidor SMTP |
| `MAIL_FROM` | Remitente de los correos (opcional) |
| `APP_URL` | Dirección donde se abre la aplicación, sin barra final: `http://localhost:8082`. Los enlaces de los correos se construyen con ella |
| `ENVIRONMENT` | `development` para instalar y probar |
| `DB_USER` | Usuario de la aplicación (`claudetask_app`). No uses `root` |

Si cambias las claves a mano, usa solo letras y números (no `'`, `\`, `#`, `$` ni espacios).

**2. Levanta los contenedores:**

```bash
docker compose up -d --build
```

La primera vez, MySQL crea la base, importa `claudetask.sql` y crea el usuario de la aplicación con permisos solo de datos (`SELECT`, `INSERT`, `UPDATE`, `DELETE`) sobre `claudetask`. El contenedor PHP, por su parte, instala las dependencias de Composer y npm. Todo tarda unos minutos. Para saber cuándo termina:

```bash
docker logs claudetask_php 2>&1 | tail -3        # debe decir ">>> Todo listo. Iniciando Apache..."
docker logs claudetask_mysql 2>&1 | grep 'init process done'
```

Si Compose se detiene con `Falta MYSQL_ROOT_PASSWORD en .env` (o `DB_USER`, `DB_PASS`), esa variable no está en el `.env`.

**3. Inicia Vite** (compila los estilos y el JavaScript en desarrollo). Déjalo abierto en una segunda terminal:

```bash
docker exec -it claudetask_php npm run dev
```

**4. Abre** <http://localhost:8082>.

> El script que crea el usuario de la aplicación y el que importa la base solo se ejecutan cuando el volumen de MySQL está vacío. Si cambias después `DB_USER` o `DB_PASS` en el `.env`, MySQL no se entera. Para una base que ya existía con `root`, usa `bash docker/crear-usuario-bd.sh`.

## 2. Primer uso

1. En la pantalla de acceso pulsa **«Aun no tienes una cuenta? Obtener una»**.
2. Escribe nombre, email, contraseña (6 a 72 caracteres) y repítela. Las cuentas que se registran son de **tutor**.
3. Abre el enlace del correo que recibes: verás «Cuenta confirmada correctamente».
4. Inicia sesión con tu correo y tu contraseña. Entras al panel de tareas.

Los tutelados no se registran solos: los crea un tutor (sección siguiente).

## 3. Manual del tutor

El menú tiene **Tareas**, **Nueva tarea**, **Tutelados** y **Perfil**; arriba a la derecha, **Cerrar Sesión**.

**Crear un tutelado** (menú *Tutelados*)

1. Escribe el nombre, el curso (opcional) y una contraseña de al menos 6 caracteres. Pulsa **Crear tutelado**.
2. La aplicación genera el **alias** del alumno (su primer nombre y un número, por ejemplo `Lucia47`) y lo muestra junto con la contraseña. **Solo se muestran una vez**: anótalos y entrégaselos al alumno. Recarga la página para verlo en la lista.
3. En cada tutelado puedes **Vincular tutor** (otro tutor ya registrado y confirmado, por su correo), **Quitar** a un tutor y **Restablecer contraseña**. Quien creó al tutelado aparece como «responsable» y es el único que puede quitar a otros tutores; cualquier tutor puede quitarse a sí mismo.

**Asignar una tarea** (menú *Nueva tarea*; primero hace falta tener un tutelado)

1. Elige a quién va dirigida (**Para**), escribe el **título** (obligatorio), la descripción y la fecha límite (opcionales).
2. Con **+ Añadir parte** divide la tarea en pasos; cada parte es lo que el alumno marca como hecho. Máximo 50 partes por tarea.
3. Pulsa **Crear tarea**.

**Revisar y gestionar** (menú *Tareas*)

- Filtra por **Tutelado** o marca **Solo por revisar** para ver únicamente lo que espera tu revisión.
- Cada tarjeta muestra el título, la fecha, el avance («N de M partes» aprobadas, o *Completada*) y *Vencida* si pasó la fecha sin completarse.
- Cada parte tiene un estado: **Pendiente** (el alumno aún no la marca), **Por revisar** (la marcó como hecha) y **Aprobada**.
  - En una parte *Por revisar* puedes escribir un comentario (opcional, hasta 500 caracteres) y pulsar **Aprobar** o **Devolver**. Devuelta, la parte vuelve a pendiente y el alumno ve tu comentario.
  - En una parte *Aprobada* puedes pulsar **Reabrir**.
- Al pie de cada tarjeta: **Editar** (título, descripción, fecha y partes), **Duplicar** (copia la tarea, con sus partes en pendiente, a los tutelados que elijas), **Historial** (quién hizo qué y cuándo) y **Eliminar** (borra la tarea con sus partes y su historial; pide confirmación).

## 4. Manual del tutelado

Inicia sesión escribiendo tu **alias** (por ejemplo `Lucia47`) y la contraseña que te dio tu tutor, en el campo «Correo o alias». Entras a **Mis tareas**.

- Cada tarea muestra su descripción, la fecha límite y sus partes.
- Una parte **Por hacer** tiene el botón **Ya lo hice**. Al pulsarlo pasa a **Esperando al tutor**.
- Si tu tutor la **devuelve**, verás «Tu tutor pidió correcciones» con su comentario y la parte vuelve a **Por hacer**.
- Cuando todas las partes están **Aprobadas**, la tarea aparece como *Completada*.
- **Historial** muestra lo ocurrido en esa tarea.
- En **Perfil** puedes cambiar tu nombre (el alias no se cambia) y, en **Cambiar Password**, tu contraseña.
- Si olvidas tu contraseña, pídele a tu tutor que use **Restablecer contraseña**. Los tutelados no tienen recuperación por correo.

## 5. Contraseña olvidada y bloqueo

- **Tutor:** en la pantalla de acceso, **Olvidaste tu password**, escribe tu correo y abre el enlace que llega. El enlace **caduca a la hora**; si caduca, pide uno nuevo.
- **Bloqueo:** tras 5 intentos fallidos con el mismo correo o alias, el acceso se bloquea 30 segundos.

## 6. Mantenimiento

| Tarea | Cómo |
| --- | --- |
| Respaldo | `bash docker/respaldo.sh` guarda `respaldos/claudetask-<fecha>.sql` (permisos 600). Lleva datos personales y hashes de contraseñas: guárdalo fuera del equipo. No incluye los usuarios de MySQL |
| Restaurar | `bash docker/restaurar.sh respaldos/claudetask-<fecha>.sql` (pide escribir `si`; **reemplaza** los datos actuales) |
| Ver errores | `docker logs claudetask_php` (PHP y Apache) y `docker logs claudetask_mysql` |
| Reiniciar la aplicación | `docker restart claudetask_php` |
| Reiniciar tras editar `.env` o `docker/apache.conf` | **Obligatorio**: el contenedor puede seguir viendo una copia antigua del archivo. Reiniciar detiene Vite; vuelve a lanzarlo con `docker exec -it claudetask_php npm run dev` |
| Detener / volver a arrancar | `docker compose stop` / `docker compose start` |
| Borrar todo, incluida la base | `docker compose down -v` (**destruye los datos**; haz un respaldo antes) |

Problemas frecuentes:

- **La página se ve sin estilos en desarrollo:** Vite no está corriendo. Lánzalo (paso 3).
- **Sin estilos en producción:** falta compilar. Ejecuta `docker exec claudetask_php npm run prod`. Si falla, el motivo aparece en el log con el prefijo `Vite:`.
- **«No pudimos enviar el correo de confirmación»:** revisa los datos `MAIL_*`. Mientras no salga el correo no se puede registrar ningún tutor.
- **Cambié las claves de la base y la aplicación no conecta:** ver la nota del paso 4 de la instalación.

## 7. Paso a producción

1. En `.env`: `ENVIRONMENT=production`, `APP_URL` con `https://` y tu dominio, y claves propias (no las de ejemplo). Con `APP_URL` en https, la cookie de sesión solo viaja por HTTPS.
2. Compila los recursos y reinicia: `docker exec claudetask_php npm run prod && docker restart claudetask_php` (`npm run prod` también pone `ENVIRONMENT=production` en el `.env`).
3. Pon un servidor o proxy con HTTPS delante del puerto 8082. La aplicación no termina TLS.
4. En `docker-compose.yml`, quita la línea del puerto `5173`: solo hace falta con Vite en desarrollo.
5. En producción los errores no se muestran en la página: van al registro (`docker logs claudetask_php`). La política de contenido (CSP) es estricta: solo recursos propios, más las fuentes de Google.
6. Programa respaldos con `docker/respaldo.sh`.

Para volver a desarrollo: `ENVIRONMENT=development` en `.env`, `docker restart claudetask_php` y `docker exec -it claudetask_php npm run dev`.

### 7.1 Sin Docker: DOM Cloud

ClaudeTask también se desplegó en DOM Cloud (plan Lite), que no usa Docker: NGINX con php-fpm y MariaDB.

1. Sube el proyecto a un repositorio público de GitHub y crea el sitio en DOM Cloud.
2. En el portal, en el despliegue del sitio, pega la receta de `docs_santitask.md`, sección 8.2, con la dirección de tu repositorio en `source`. Instala PHP 8.4, compila los recursos con Vite, crea el `.env` y carga `claudetask.sql` quitando sus líneas `CREATE DATABASE` y `USE`.
3. Para actualizar el código, usa la misma receta sin las tres últimas líneas, que cargan la base solo la primera vez (no se probó).
4. El correo queda sin configurar: completa `MAIL_HOST`, `MAIL_USERNAME` y `MAIL_PASSWORD` en el `.env` del servidor. Sin ellos no se puede registrar un tutor por correo.
5. En ese entorno la aplicación usa el usuario de la cuenta y no `claudetask_app`, y `docker/respaldo.sh` no sirve: usa respaldos propios del servidor.

## 8. Estructura

```
public/index.php     rutas y punto de entrada      controllers/   un controlador por área
models/              ActiveRecord y modelos        views/         plantillas PHP
includes/            entorno, sesión, CSRF, CSP    src/           JavaScript y SCSS (Vite)
docker/              Apache, respaldos, init       claudetask.sql esquema de la base
```
