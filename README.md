# Libro de Reclamaciones Virtual

Plataforma web (PHP 8 + Supabase + jQuery) basada en la **Hoja de Reclamación Virtual** (INDECOPI, Perú). Incluye formulario público en dos pasos, panel de administración con barra lateral, PDF y envío por e-mail.

## 1. Funcionalidades

| Módulo | Qué hace |
|---|---|
| Formulario público | Paso 1: elegir **Sede** y **Local**. Paso 2: llenar la hoja (4 bloques del formato oficial). Diseño de `index.html` original, sin cambios |
| Número de hoja | `SERIE-0001`: la serie es del local y el correlativo lo asigna la base de datos (atómico) |
| Pantalla final | Descarga el PDF y envía una copia al correo del consumidor (si lo indicó); "Registrar otra" se habilita tras descargar |
| Panel admin | Barra lateral: **Reclamos**, **Sedes**, **Locales** y acceso al formulario |
| Reclamos | Indicadores (total, pendientes, atendidos, plazo vencido), filtros, paginación y detalle |
| Atención | Estado, observaciones y fecha de respuesta (bloque 4 del PDF) |
| PDF | Hoja A4 generada en el servidor (Dompdf) con los colores del sistema |
| E-mail | Al registrar se avisa al local/sede; la copia al consumidor se envía al pulsar el botón. El admin puede reenviar al consumidor y/o proveedor |

## 2. Estructura de archivos

```
├── .env.example · .gitignore · .htaccess · composer.json · README.md
├── database/schema.sql          Tablas, trigger de correlativo y RLS
├── scripts/create_admin.php     Crea el administrador (CLI)
├── config/bootstrap.php         Autoload, .env, sesión, CSRF, helpers
├── src/  Supabase.php · Reclamo.php · PdfService.php · Mailer.php
└── public/                      DocumentRoot
    ├── index.php                Formulario público (tu diseño)
    ├── api.php                  API pública: sedes, locales, previsualizacion, guardar, enviar_correo
    ├── pdf.php                  PDF por token (?t=...&download=1)
    ├── assets/  public.css · admin.css · admin.js
    └── admin/
        ├── _layout.php          Plantilla con barra lateral
        ├── login.php · logout.php
        ├── index.php            Reclamos       ├── sedes.php   Sedes
        ├── locales.php          Locales        ├── api.php     Endpoint AJAX del panel
        └── pdf.php              PDF de cualquier registro (solo admin)
```

## 3. Instalación

1. **Supabase**: crea un proyecto y ejecuta `database/schema.sql` en *SQL Editor*. (Si ya habías ejecutado la versión anterior, usa un proyecto nuevo o borra las tablas: el esquema cambió.)
2. Copia URL y clave `service_role` (*Project Settings > API*).
3. `cp .env.example .env` y completa Supabase y SMTP.
4. `composer install` (PHP ≥ 8.1 con `curl`, `mbstring`, `gd`).
5. `php scripts/create_admin.php correo@x.com "Nombre" "Clave"`.
6. DocumentRoot en `public/` (local: `php -S localhost:8000 -t public`).
7. En `/admin/login.php`: crea una **sede**, luego sus **locales** (cada uno con su *serie*). El formulario solo lista sedes y locales activos.

## 4. Flujo de procesos

**Consumidor**
1. `index.php` carga las sedes (`api.php?action=sedes`); al elegir una, se cargan sus locales y se actualizan la cabecera y los datos del proveedor.
2. *Continuar*: `previsualizacion` muestra el N° que se asignará (referencial).
3. *Enviar*: `guardar` valida en servidor (DNI/CE 6-20 alfanuméricos, correo opcional válido, monto ≥ 0, local activo), inserta el reclamo y calcula el plazo de 15 días hábiles. El trigger fija el correlativo definitivo. Se avisa por e-mail al local/sede.
4. Pantalla final: el botón descarga `pdf.php?t=TOKEN` y llama a `enviar_correo` (una sola vez por reclamo) para enviar la copia al consumidor.

**Administrador**: Reclamos → *Ver* → guardar estado/observaciones → *Enviar* PDF al consumidor y/o proveedor. Sedes y Locales: alta, edición y activar/desactivar (no se borran, para conservar el historial).

## 5. Modelo de datos

`sedes` 1—N `locales` 1—N `reclamos`; `admins` aparte. `sedes` guarda nombre, razón social, RUC y domicilio (puede haber varias sedes con el mismo RUC). `locales.serie` es única y `locales.contador` guarda el último correlativo. `reclamos.token` permite la descarga pública sin exponer el `id`.

## 6. Seguridad

- La clave `service_role` solo está en el servidor; con RLS sin políticas, la clave `anon` no accede a nada.
- Contraseñas con `password_hash`, sesión regenerada y bloqueo de 5 min tras 5 intentos fallidos.
- CSRF en todos los POST (público y admin), filtros validados y salida escapada en el panel y los PDF.
- El PDF adjunto siempre se genera en el servidor: el cliente solo envía el token, así nadie puede usar el formulario para mandar archivos arbitrarios.
- Usa HTTPS en producción y no subas `.env`.

## 7. Notas y limitaciones

- La fecha del reclamo la fija el servidor (el campo se muestra solo de lectura).
- El N° mostrado antes de enviar es referencial: si dos personas envían a la vez, cada una recibe el suyo definitivo.
- El plazo de 15 días hábiles no descuenta feriados.
- Los PDF reflejan los datos actuales de sede y local.
- El nombre del proveedor en la cabecera del formulario cambia según la sede elegida.
- Gmail requiere una *contraseña de aplicación* para SMTP.

## 8. Despliegue en Vercel

Ver guía completa en [`DEPLOY_VERCEL.md`](DEPLOY_VERCEL.md).

### Resumen rápido:
1. Ejecuta `database/schema.sql` en Supabase SQL Editor
2. Configura 10 variables de entorno en Vercel Dashboard
3. Conecta repositorio Git a Vercel
4. Deploy automático vía `vercel.json`
5. Crea admin: `php scripts/create_admin.php email nombre clave`
6. Configura sede y locales en `/admin`

### Archivos de configuración Vercel:
- `vercel.json` — Configuración principal (build, routes, functions)
- `.vercelignore` — Archivos excluidos del deploy
- `php.ini` — Configuración PHP para serverless
- `.env.production.example` — Plantilla de variables de producción
- `.github/workflows/deploy.yml` — CI/CD automático

### ⚠️ Limitación importante: Sesiones en Serverless
Vercel usa funciones stateless. Las sesiones PHP basadas en archivos **no persisten** entre invocaciones.

**Para producción real**, considera:
- **Opción A**: Migra auth a **Supabase Auth** (recomendado)
- **Opción B**: Usa **Vercel KV (Redis)** para sesiones distribuidas
- **Opción C**: Hosting PHP tradicional (Railway, Render, Fly.io, VPS) — $5-7/mes
