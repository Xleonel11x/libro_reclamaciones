# Resumen de Configuración para Vercel - Libro de Reclamaciones

## Archivos Creados

### Configuración Principal
| Archivo | Propósito |
|---------|-----------|
| `vercel.json` | Configuración principal: build, routes, functions (`vercel-php@0.7.4`) |
| `.vercelignore` | Archivos excluidos del deploy (vendor, .env, scripts, etc.) |
| `php.ini` | Configuración PHP optimizada para serverless (memoria, OPcache, sesiones) |
| `.env.production.example` | Plantilla de variables de entorno para producción |
| `composer.json` | Define PHP version (`"php": "^8.2"`) — Vercel la usa para runtime |
| `api/index.php` | **Entry point único** para vercel-php — enruta a public/ |

### CI/CD
| Archivo | Propósito |
|---------|-----------|
| `.github/workflows/deploy.yml` | Pipeline GitHub Actions: lint, test, deploy preview/production |

### Documentación
| Archivo | Propósito |
|---------|-----------|
| `DEPLOY_VERCEL.md` | Guía completa paso a paso para deploy en Vercel |
| `VERCEL_SETUP_SUMMARY.md` | Este archivo - resumen de cambios |
| `README.md` | Actualizado con sección de despliegue Vercel |

### Routing
| Archivo | Propósito |
|---------|-----------|
| `public/.htaccess` | Reescritura de URLs para Vercel (API, PDF, admin, assets) |

## Archivos Modificados

### Configuración PHP
| Archivo | Cambios |
|---------|---------|
| `config/bootstrap.php` | Session config para serverless (`/tmp`), HTTPS detection, strict mode |

### Git
| Archivo | Cambios |
|---------|---------|
| `.gitignore` | Agregados: .env.local, .vercel, IDE files, logs, test files |

## Variables de Entorno Requeridas (10)

```env
APP_URL=https://tu-proyecto.vercel.app
SUPABASE_URL=https://xxxxx.supabase.co
SUPABASE_SERVICE_KEY=eyJ... (service_role)
SUPABASE_ANON_KEY=eyJ... (anon)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_SECURE=tls
SMTP_USER=tu-email@gmail.com
SMTP_PASS=tu-app-password
MAIL_FROM=tu-email@gmail.com
MAIL_FROM_NAME="Libro de Reclamaciones"
```

## Estructura Final del Proyecto

```
libro-reclamaciones/
├── .github/workflows/deploy.yml   # CI/CD
├── .vercelignore                   # Ignore rules
├── .env.example                    # Template local
├── .env.production.example         # Template producción
├── .gitignore                      # Git ignore
├── vercel.json                     # Vercel config
├── php.ini                         # PHP config
├── composer.json                   # Deps
├── composer.lock                   # Lock file (✓ commited)
├── DEPLOY_VERCEL.md                # Guía deploy
├── VERCEL_SETUP_SUMMARY.md         # Este archivo
├── README.md                       # Actualizado
├── database/schema.sql             # Schema BD
├── scripts/create_admin.php        # CLI admin
├── config/bootstrap.php            # Bootstrap (modificado)
├── src/
│   ├── Supabase.php
│   ├── Reclamo.php
│   ├── PdfService.php
│   └── Mailer.php
├── api/                            # Vercel PHP entry point (vercel-php)
│   └── index.php                  # Router único → enruta a public/
└── public/                         # DocumentRoot (app real)
    ├── .htaccess                   # Routing local (no usado en Vercel)
    ├── index.php                   # Formulario público
    ├── api.php                     # API pública
    ├── pdf.php                     # PDF público
    ├── assets/
    │   ├── public.css
    │   ├── admin.css
    │   └── admin.js
    └── admin/
        ├── _layout.php
        ├── login.php
        ├── logout.php
        ├── index.php               # Reclamos
        ├── sedes.php               # Sedes
        ├── locales.php             # Locales
        ├── api.php                 # API admin
        └── pdf.php                 # PDF admin
```

## ⚠️ Limitación Crítica: Sesiones en Serverless

**Las sesiones PHP basadas en archivos NO funcionan en Vercel** (functions son stateless).

### Para Producción Real - Elige Una:

| Opción | Esfuerzo | Costo | Recomendación |
|--------|----------|-------|---------------|
| **Railway/Render/Fly.io** | Bajo | $5-7/mes | ✅ **Mejor opción** - PHP nativo, sesiones, BD |
| **Supabase Auth** | Medio | Gratis | ✅ Buena - migra auth a Supabase |
| **Vercel KV (Redis)** | Medio | $20/mes | ⚠️ Solo Pro plan |
| **JWT Stateless** | Alto | Gratis | ⚠️ Requiere refactor completo |

## Comandos de Deploy

```bash
# 1. Preparar
git add .
git commit -m "feat: configuración Vercel deploy"
git push origin main

# 2. En Vercel Dashboard:
# - Import Project desde GitHub
# - Agregar 10 Environment Variables
# - Deploy

# 3. Post-deploy:
vercel env pull .env.local
php scripts/create_admin.php admin@tudominio.com "Admin" "ClaveSegura123"

# 4. Configurar en /admin:
# - Crear Sede
# - Crear Local(es) con serie única
```

## Verificación Post-Deploy

- [ ] `https://tu-proyecto.vercel.app` carga formulario
- [ ] Seleccionar sede → carga locales
- [ ] Continuar → muestra N° hoja referencial
- [ ] Llenar formulario → enviar → success screen
- [ ] Descargar PDF → se genera correctamente
- [ ] Email consumidor → se envía (si hay email)
- [ ] `/admin/login.php` → login admin
- [ ] Panel admin: ver reclamos, sedes, locales
- [ ] Editar reclamo → guardar estado/observaciones
- [ ] Reenviar PDF desde admin

## Soporte

- **Vercel PHP Runtime**: https://vercel.com/docs/functions/runtimes/php
- **Supabase REST API**: https://supabase.com/docs/reference/javascript/fetch
- **Dompdf**: https://github.com/dompdf/dompdf
- **PHPMailer**: https://github.com/PHPMailer/PHPMailer