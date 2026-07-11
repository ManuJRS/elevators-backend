# Backend WordPress — Citización Ecommerce

Backend headless de WordPress con WooCommerce y API GraphQL, pensado para consumirse desde el frontend Vue del proyecto **citizacion-ecommerce**.

## Stack

- **WordPress** (imagen oficial Docker)
- **MySQL 8.0**
- **WooCommerce** 10.9
- **WPGraphQL** 2.17
- **WPGraphQL for WooCommerce** 1.0
- **Advanced Custom Fields** 6.8
- **WPGraphQL for ACF** 2.6
- **Custom Post Type UI** 1.19

## Requisitos

- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/)

## Inicio rápido

1. Clona el repositorio:

   ```bash
   git clone <url-del-repositorio>
   cd backend-wp
   ```

2. Crea el archivo de entorno local:

   ```bash
   cp .env.example .env
   ```

3. Levanta los servicios:

   ```bash
   docker compose up -d
   ```

4. Abre WordPress en el navegador:

   - **Sitio:** http://localhost:8080
   - **GraphQL:** http://localhost:8080/graphql
   - **GraphiQL IDE:** http://localhost:8080/wp-admin/admin.php?page=graphql-ide

5. Completa el asistente de instalación de WordPress la primera vez que accedas.

## Configuración del frontend

El frontend Vue se conecta al endpoint GraphQL. En su `.env` debe apuntar a:

```env
VITE_GRAPHQL_URL=http://localhost:8080/graphql
```

## Puente híbrido Vue ↔ WooCommerce

El mu-plugin `wp-content/mu-plugins/citizacion-vue-bridge.php` sincroniza el checkout:

1. **Autenticación:** Lee el parámetro `vue_jwt` enviado desde Vue, valida el token con **Simple JWT Login** e inicia sesión en WordPress/WooCommerce.
2. **Carrito:** Lee `cargar-carrito` con formato `id:cantidad,id:cantidad` y reemplaza el carrito de WooCommerce.
3. **Seguridad:** Redirige al checkout limpio sin exponer el JWT en la URL.

Flujo esperado desde Vue:

```text
/finalizar-compra/?cargar-carrito=123:2,456:1&vue_jwt=<token>
```

Requisitos:

- Plugin **WPGraphQL JWT Authentication** activo.
- Clave secreta definida en `GRAPHQL_JWT_AUTH_SECRET_KEY` (ver `.env` y mu-plugin `citizacion-jwt-config.php`).
- WooCommerce con página de checkout en `/finalizar-compra/`.

## Estructura del proyecto

```
backend-wp/
├── docker-compose.yml   # Orquestación de WordPress + MySQL
├── .env.example         # Plantilla de variables de entorno
└── wp-content/          # Plugins, temas y traducciones versionados
    ├── plugins/
    ├── themes/
    └── languages/
```

> **Nota:** El core de WordPress no se versiona; se obtiene de la imagen Docker. Solo se versiona `wp-content`.

## Comandos útiles

```bash
# Ver logs
docker compose logs -f

# Detener servicios
docker compose down

# Detener y eliminar volúmenes (borra la base de datos)
docker compose down -v
```

## Qué no se sube a Git

- Archivo `.env` con credenciales locales
- Carpeta `wp-content/uploads/` (medios subidos)
- Volúmenes de Docker (`db_data`)

## Licencia

Los plugins de terceros incluidos en `wp-content/plugins/` mantienen sus propias licencias (GPL y similares).
