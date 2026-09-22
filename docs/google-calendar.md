# Integración Google Calendar (multi-tenant)

PodoSoft permite que **cada clínica (tenant) conecte su propia cuenta de Google Calendar** mediante OAuth 2.0. Existe un único OAuth Client de la aplicación en Google Cloud; cada tenant solo autoriza el acceso con su cuenta de Google.

## Variables `.env` requeridas

```env
GOOGLE_CLIENT_ID=          # Client ID del OAuth Client de PodoSoft (Google Cloud)
GOOGLE_CLIENT_SECRET=      # Client Secret (¡solo en .env, nunca en código!)
GOOGLE_REDIRECT_URI=https://podosof.devsoftai.com/google/calendar/callback
```

## Configuración en Google Cloud Console

1. Proyecto de PodoSoft → **APIs y servicios → Pantalla de consentimiento OAuth** (tipo Externo, scopes: `calendar.events`, `userinfo.email`).
2. Habilitar **Google Calendar API** y **Google People/userinfo** (userinfo.email).
3. **Credenciales → OAuth 2.0 Client ID** (tipo Web):
   - Authorized redirect URI (exacta): `https://podosof.devsoftai.com/google/calendar/callback`
4. Si la app está en modo "Testing", agregar los correos de las clínicas como **usuarios de prueba**, o publicar la app para producción.

## Rutas

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/config/google-calendar` | Pantalla de configuración (Livewire `GoogleCalendarConfig`) |
| GET | `/google/calendar/redirect` | Inicia OAuth (genera `state` en sesión) |
| GET | `/google/calendar/callback` | Callback de Google (valida state, guarda conexión) |
| POST | `/google/calendar/disconnect` | Revoca y elimina la conexión del tenant |
| GET | `/google/calendar/status` | JSON de estado (sin tokens) |

Todas bajo middleware `auth`. El `tenant_id` proviene **siempre** del usuario autenticado, nunca del request.

## Flujo

1. Usuario: Configuración → Google Calendar → "Conectar".
2. `redirect()` genera `state` aleatorio en sesión y redirige a Google (`access_type=offline`, `prompt=consent` para garantizar `refresh_token`).
3. `callback()` valida `state` (anti-CSRF), intercambia el `code`, obtiene email/ID de la cuenta y el calendario principal.
4. Guarda en `google_calendar_connections` (uno por tenant, índice único `tenant_id`).
5. La vista muestra cuenta, calendario, botón **Probar conexión** (consulta real a Google) y **Desconectar**.

## Almacenamiento y seguridad

- `access_token` y `refresh_token` se guardan **cifrados** (cast `encrypted` del modelo) y están en `$hidden` (nunca salen en JSON).
- Los logs nunca incluyen tokens.
- Aislamiento: todas las consultas usan `forTenant($tenantId)`; un tenant no puede ver la conexión de otro.
- Revocar tokens rotos: si Google devuelve `invalid_grant` al refrescar, se solicita reconexión.

## Desconexión

`disconnect()` revoca el token en Google (best-effort) y elimina la fila. **Las citas de PodoSoft no se tocan** y `citas.google_event_id` se conserva (decisión: conservarlos; los Jobs de sync reconectarán eventos nuevos).

## Cómo probar con una cuenta real

1. Configurar las 3 variables `.env` con las credenciales del OAuth Client.
2. `php artisan config:clear`
3. Entrar a `/config/google-calendar` → Conectar → autorizar con una cuenta Google de prueba (registrada como test user si la app está en Testing).
4. Ver estado "Conectado", cuenta y calendario. Pulsar **Probar conexión** (debe decir "Conexión exitosa con el calendario ...").

## Pendiente para sincronización automática de citas

La estructura ya está lista:

- Columna `citas.google_event_id`.
- Jobs preparados (cola dedicada `google-calendar`, aún **no despachados** desde ningún lado):
  - `App\Jobs\CreateGoogleCalendarEvent`
  - `App\Jobs\UpdateGoogleCalendarEvent`
  - `App\Jobs\DeleteGoogleCalendarEvent`

Worker para esta cola:

```bash
php artisan queue:work database --queue=google-calendar --sleep=3 --tries=3 --timeout=60
```
- Servicio `GoogleCalendarService` con `createEvent/updateEvent/deleteEvent`.

Para activar la sincronización: despachar `CreateGoogleCalendarEvent` tras crear una cita (`AppointmentManager` / wizard público), `Update*` al editar y `Delete*` al cancelar/eliminar. Todos respetan `tenant_id` de la cita.
