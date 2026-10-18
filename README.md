# TNL Computer — sitio nuevo (Laravel 12)

Reemplaza al sitio estático + PHP suelto de la carpeta de arriba. Incluye tienda con carrito (el pedido llega por mail y WhatsApp, sin pago online), hosting, diseño web, service, contacto, portfolio con certificaciones y un panel `/admin`.

El sitio está en español (`/...`) e inglés (`/en/...`). Los textos fijos se traducen en `lang/en.json`; los proyectos, rubros y zonas de envío tienen su campo "en inglés" en el panel.

| Qué | Dónde se edita |
|---|---|
| Pedidos y pagos (enviar, entregar, cancelar, código de seguimiento) | `/admin/pedidos` |
| Zonas y costos de envío por código postal | `/admin/zonas-envio` |
| Lista de precios del proveedor (CSV) | `/admin/productos/importar` o `php artisan tnl:importar-csv archivo.csv` |
| Productos, ofertas, rubros | `/admin/productos`, `/admin/rubros` |
| Planes y precios de hosting | `/admin/hosting` |
| Proyectos, skills y certificaciones del portfolio | `/admin/proyectos`, `/admin/skills`, `/admin/certificaciones` |
| Mensajes de contacto, service y pedidos | `/admin/mensajes` (además llegan por mail) |
| Textos fijos: WhatsApp, mails, redes, perfil, experiencia, servicios | `config/tnl.php` |

## Desarrollo local

```powershell
composer install; npm install
copy .env.example .env   # y cambiar: APP_ENV=local, APP_DEBUG=true, APP_URL=http://127.0.0.1:8000, DB_CONNECTION=sqlite, MAIL_MAILER=log
php artisan key:generate
php -d extension=pdo_sqlite artisan migrate --seed
php -d extension=pdo_sqlite artisan tnl:admin tu@mail.com              # usuario del panel
npm run build
cd public; php -d extension=pdo_sqlite -S 127.0.0.1:8000 ..\vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php
```

Tests: `php -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit` (SQLite en memoria, nunca toca una base real).

## Publicar en el hosting (DirectAdmin, con SSH)

Rutas de ejemplo: el dominio vive en `~/domains/tnlcomputer.com.ar/`. La app va en `laravel/` y `public_html` pasa a ser un enlace a `laravel/public`, así el código, el `.env` y `vendor` nunca quedan accesibles desde la web.

1. **Empaquetar** (en tu PC, PowerShell en la raíz del proyecto): `.\empaquetar.ps1` → genera `dist\tnlcomputer-app.zip` con `vendor` y los assets compilados.
2. **Base de datos**: en DirectAdmin → MySQL Management, usar la base actual (`tnlcom55_tnl`) o crear una nueva. Las tablas nuevas (`products`, `categories`, …) no pisan las viejas (`productos`, `rubros`, `ofertas`).
3. **Subir y descomprimir**: subir el zip a `~/domains/tnlcomputer.com.ar/` y por SSH:
   ```bash
   cd ~/domains/tnlcomputer.com.ar
   mkdir laravel && cd laravel && unzip ../tnlcomputer-app.zip && cd ..
   cp laravel/.env.example laravel/.env && nano laravel/.env       # completar DB_* y MAIL_*
   ```
4. **Rescatar archivos del sitio viejo** (están en el hosting y no en el repo):
   ```bash
   mkdir -p laravel/public/assets
   cp -r public_html/assets/certs laravel/public/assets/            # PDFs de las certificaciones
   cp -n public_html/assets/img/webp/productos/* laravel/public/img/productos/   # fotos de productos más nuevas
   ```
5. **Instalar**:
   ```bash
   cd laravel
   php artisan key:generate --force
   php artisan migrate --seed --force
   # el seed ya carga el catálogo de database/data/catalogo.csv (lista de precios + imágenes)
   php artisan tnl:admin jorgemartinez@tnlcomputer.com.ar
   php artisan config:cache && php artisan route:cache && php artisan view:cache
   chmod -R 775 storage bootstrap/cache public/uploads 2>/dev/null; mkdir -p public/uploads
   ```
6. **Cambiar el sitio** (es el único paso visible; se revierte en segundos):
   ```bash
   cd ~/domains/tnlcomputer.com.ar
   mv public_html public_html_viejo && ln -s laravel/public public_html
   ```
   Para volver atrás: `rm public_html && mv public_html_viejo public_html`.
7. Revisar: inicio, tienda, un formulario de contacto (tiene que llegar el mail) y `/admin`.

Las URLs viejas (`hosting.html`, `portfolio.html`, etc.) redirigen con 301 a las nuevas.

### Actualizar después
Empaquetar de nuevo, subir, y en el servidor descomprimir encima de `laravel/` (sin pisar `.env`, `public/uploads`, `public/assets`, `storage`), luego `php artisan migrate --force && php artisan optimize`.

### Tienda y pagos (Mercado Pago Checkout Pro)

Flujo: carrito → datos y envío (el costo se cotiza por CP) → Mercado Pago → página de seguimiento `/pedido/{token}`.

- **Medios de pago:** crédito en cuotas, débito, dinero en cuenta y pago con dos tarjetas (opción nativa de Mercado Pago). El efectivo está excluido por defecto (`MP_EXCLUDED_PAYMENT_TYPES`).
- **Seguridad:**
  - Los datos de tarjeta se cargan en Mercado Pago y nunca pasan por este servidor.
  - Precios y envío se recalculan del lado del servidor.
  - El estado de cada pago se consulta a la API de Mercado Pago, nunca se toma de la URL de retorno.
  - El webhook valida la firma HMAC (`MP_WEBHOOK_SECRET`) y rechaza avisos de más de una hora.
  - Se controla que el pago sea del pedido (`external_reference`) y en ARS.
  - Si un mismo aviso llega dos veces, no se duplican pagos ni mails.
  - Los links de pedido usan un token de 40 caracteres.
  - Los formularios tienen CSRF, límite de envíos y honeypot.
- **Configurar:**
  1. Cargar `MP_ACCESS_TOKEN` en `.env`.
  2. En el panel de Mercado Pago, en Webhooks, cargar la URL `https://tnlcomputer.com.ar/webhooks/mercadopago`, marcar el evento **Pagos** y copiar la clave secreta en `MP_WEBHOOK_SECRET`.
  3. Correr `php artisan config:cache`.
- **Probar antes de abrir:** usar las credenciales de prueba (`TEST-...`) y las [tarjetas de prueba](https://www.mercadopago.com.ar/developers/es/docs/checkout-pro/additional-content/your-integrations/test/cards).
- **Mails:** cuando un pedido se paga, el cliente recibe la confirmación y TNL recibe el aviso "Venta pagada".
- **Reintegros:** se hacen desde Mercado Pago, y el pedido pasa solo a "Reintegrado".

### Actualizar precios
Para actualizar precios, subí la lista nueva del proveedor en `/admin/productos/importar` (aplica el recargo de `TNL_MARKUP`, hoy 20%). Por SSH: `php artisan tnl:importar-csv lista.csv --desactivar`.

### Si el hosting fuera IIS (Windows)
`public/web.config` ya tiene las reglas equivalentes al `.htaccess`. El sitio de IIS tiene que apuntar a la carpeta `public`.
