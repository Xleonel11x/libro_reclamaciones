# Guía de Despliegue en Vercel - Libro de Reclamaciones

## Resumen del Proyecto

Aplicación PHP 8.2+ con:
- **Frontend**: Formulario público (2 pasos) + Panel admin (sidebar)
- **Backend**: Supabase (PostgreSQL) via REST API
- **PDF**: Dompdf (generación servidor)
- **Email**: PHPMailer (SMTP)
- **Auth**: Sesiones PHP + CSRF + password_hash

---

## Requisitos Previos

1. Cuenta en [Vercel](https://vercel.com)
2. Cuenta en [Supabase](https://supabase.com) con proyecto creado
3. Repositorio Git (GitHub/GitLab/Bitbucket)
4. SMTP configurado (Gmail con App Password recomendado)

---

## Paso 1: Preparar Base de Datos en Supabase

1. Ve a **Supabase Dashboard > SQL Editor**
2. Ejecuta el contenido de `database/schema.sql`
3. Ve a **Settings > API** y copia:
   - `Project URL` → `SUPABASE_URL`
   - `service_role` key → `SUPABASE_SERVICE_KEY`
   - `anon` key → `SUPABASE_ANON_KEY`

---

## Paso 2: Configurar Variables de Entorno en Vercel

En **Vercel Dashboard > Settings > Environment Variables**, agrega:

| Variable | Valor | Entorno |
|----------|-------|---------|
| `APP_URL` | `https://tu-proyecto.vercel.app` | Production, Preview |
| `SUPABASE_URL` | `https://xxxxx.supabase.co` | All |
| `SUPABASE_SERVICE_KEY` | `eyJ...` (service_role) | All |
| `SUPABASE_ANON_KEY` | `eyJ...` (anon) | All |
| `SMTP_HOST` | `smtp.gmail.com` | All |
| `SMTP_PORT` | `587` | All |
| `SMTP_SECURE` | `tls` | All |
| `SMTP_USER` | `tu-email@gmail.com` | All |
| `SMTP_PASS` | `tu-app-password` | All |
| `MAIL_FROM` | `tu-email@gmail.com` | All |
| `MAIL_FROM_NAME` | `Libro de Reclamaciones` | All |

> **Nota Gmail**: Usa una [App Password](https://support.google.com/accounts/answer/185833) en lugar de tu contraseña normal.

---

## Paso 3: Conectar Repositorio

1. Haz push a GitHub/GitLab/Bitbucket
2. En Vercel: **Add New > Project > Import Git Repository**
3. Selecciona el repositorio
4. Vercel detectará automáticamente `vercel.json`
5. **Deploy**

---

## Paso 4: Crear Administrador Inicial

Después del primer deploy, ejecuta en la consola de Vercel (Functions > View Function Logs) o localmente:

```bash
# Localmente (requiere .env configurado)
php scripts/create_admin.php admin@tudominio.com "Administrador" "ClaveSegura123"
```

O usa la **Vercel CLI**:

```bash
vercel env pull .env.local
php scripts/create_admin.php admin@tudominio.com "Administrador" "ClaveSegura123"
```

---

## Paso 5: Configurar Sede y Locales

1. Accede a `https://tu-proyecto.vercel.app/admin/login.php`
2. Inicia sesión con el admin creado
3. Ve a **Sedes** → Crear sede (nombre, razón social, RUC 11 dígitos, domicilio, email opcional)
4. Ve a **Locales** → Crear local (seleccionar sede, serie única 1-6 chars, nombre, dirección, email opcional)
5. **Importante**: Solo sedes y locales `activos` aparecen en el formulario público

---

## Estructura de Archivos para Vercel

```
├── vercel.json              # Configuración principal de Vercel
├── .vercelignore            # Archivos ignorados en deploy
├── .env.production.example  # Plantilla de variables de producción
├── composer.json            # Dependencias PHP
├── composer.lock            # Lock file (commitear)
├── api/                     # Vercel PHP entry point (requerido por vercel-php)
│   └── index.php           # Router único → enruta a public/
├── public/                  # DocumentRoot (app real)
│   ├── .htaccess           # Routing local (no usado en Vercel)
│   ├── index.php           # Formulario público
│   ├── api.php             # API pública
│   ├── pdf.php             # Descarga PDF
│   ├── assets/             # CSS/JS estáticos
│   └── admin/              # Panel admin
│       ├── index.php       # Reclamos
│       ├── sedes.php       # Sedes
│       ├── locales.php     # Locales
│       ├── api.php         # API admin
│       ├── pdf.php         # PDF admin
│       ├── login.php       # Login
│       ├── logout.php      # Logout
│       └── _layout.php     # Layout compartido
├── config/
│   └── bootstrap.php       # Autoload, env, helpers
├── src/
│   ├── Supabase.php        # Cliente Supabase REST
│   ├── Reclamo.php         # Lógica de reclamos
│   ├── PdfService.php      # Generación PDF
│   └── Mailer.php          # Envío emails
├── database/
│   └── schema.sql          # Esquema BD
└── scripts/
    └── create_admin.php    # CLI crear admin
```

---

## Limitaciones y Consideraciones en Vercel

### ⚠️ Sesiones PHP
Vercel usa **serverless functions stateless**. Las sesiones basadas en archivos (`session_start()`) **no persisten** entre invocaciones.

**Soluciones**:
1. **Supabase Auth** (recomendado): Migra auth a Supabase
2. **Redis/Vercel KV**: Usa `@vercel/kv` para sesiones distribuidas
3. **JWT en cookies**: Implementa auth stateless con JWT firmados

### ⚠️ Archivos Temporales (PDF)
Dompdf genera PDFs en memoria (`$p->output()`), **funciona bien** en serverless.

### ⚠️ Runtime PHP en Vercel
Vercel usa el runtime legacy `now-php@1.0.0`. La versión de PHP se define en `composer.json`:
```json
{ "require": { "php": "^8.2" } }
```

### ⚠️ Timeout
- **Vercel Hobby**: 10s max
- **Vercel Pro**: 60s max
- Configurado `maxDuration: 30` en `vercel.json`

### ⚠️ Cold Starts
Primera petición puede tardar 1-3s (carga composer, conexión Supabase).

---

## Alternativas Recomendadas para Producción

| Opción | Pros | Contras |
|--------|------|---------|
| **Railway/Render/Fly.io** | PHP nativo, sesiones, BD persistente, $5-7/mes | Menos integración Git |
| **VPS (Hetzner/DO)** | Control total, barato | Mantenimiento manual |
| **Laravel Forge + VPS** | Deploy automático, colas, schedule | Requiere Laravel |
| **Vercel + Supabase Auth + KV** | Serverless, escalable | Requiere refactor auth |

---

## Comandos Útiles

```bash
# Desarrollo local
composer install
cp .env.example .env
# Editar .env
php -S localhost:3000 -t public

# Vercel CLI
npm i -g vercel
vercel login
vercel dev                    # Dev local con config Vercel
vercel env pull .env.local    # Bajar variables de Vercel
vercel deploy --prod          # Deploy producción
vercel logs                   # Ver logs

# Base de datos
# Ejecutar database/schema.sql en Supabase SQL Editor
```

---

## Checklist Pre-Deploy

- [ ] `composer.lock` commiteado
- [ ] `database/schema.sql` ejecutado en Supabase
- [ ] Variables de entorno en Vercel (10 variables)
- [ ] SMTP probado (Gmail App Password)
- [ ] Repositorio conectado a Vercel
- [ ] Primer deploy exitoso
- [ ] Admin creado via CLI
- [ ] Sede + Local creados en panel admin
- [ ] Formulario público funciona (prueba completa)
- [ ] PDF se genera y descarga
- [ ] Email se envía (local/sede y consumidor)

---

## Troubleshooting

### Error 500 en API
- Revisa **Function Logs** en Vercel Dashboard
- Verifica variables de entorno (SUPABASE_SERVICE_KEY)
- Comprueba que Supabase permite conexiones desde IPs de Vercel

### PDF no se genera
- Verifica que `dompdf/dompdf` está en `composer.lock`
- Revisa memoria PHP (Dompdf necesita ~64MB)

### Sesión no persiste (admin)
- **Esperado en Vercel serverless**
- Solución: Implementar Supabase Auth o Vercel KV

### CORS / CSRF errores
- Verifica `APP_URL` coincide con dominio Vercel
- Headers CSRF: `X-CSRF` desde meta tag

---

## Variables de Entorno Reference

```env
# App
APP_URL=https://tu-proyecto.vercel.app

# Supabase (Settings > API)
SUPABASE_URL=https://xxxxxx.supabase.co
SUPABASE_SERVICE_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9... (service_role, SECRETO)
SUPABASE_ANON_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9... (anon, público)

# SMTP (Gmail ejemplo)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_SECURE=tls
SMTP_USER=tu-email@gmail.com
SMTP_PASS=abcd-efgh-ijkl-mnop  # App Password 16 chars

# Email
MAIL_FROM=tu-email@gmail.com
MAIL_FROM_NAME="Libro de Reclamaciones"
```

---

## Soporte

- **Vercel Docs**: https://vercel.com/docs
- **Supabase Docs**: https://supabase.com/docs
- **PHP en Vercel**: https://vercel.com/docs/functions/runtimes/php