# 🐳 Guía de Uso de Docker Compose
**Alitas Vega POS** | Entornos Local y Producción separados

---

## 1. Archivos Disponibles

Hemos separado la infraestructura de contenedores en dos archivos para evitar cruce de datos y configuraciones:

1. `docker-compose.local.yml`: **(Solo para tu PC)** Levanta MySQL y Redis locales, mapeando los puertos `3306` y `6379` a tu computadora.
2. `docker-compose.prod.yml`: **(Para la nube)** Levanta MySQL, Redis, Nginx y la Aplicación PHP-FPM con máxima seguridad y cachés activos. No expone los puertos de las bases de datos al exterior.

---

## 2. Cómo trabajar en Desarrollo Local

El objetivo del entorno local es que sigas usando `php artisan serve` y `npm run dev` para ver tus cambios al instante, pero respaldado por la potencia de MySQL y Redis en contenedores.

### Paso A: Configurar tu archivo `.env`
Asegúrate de que tu archivo `.env` (el que está en la raíz de tu proyecto) esté configurado así:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=alitas_pos
DB_USERNAME=alitas
DB_PASSWORD=password
```

### Paso B: Iniciar la base de datos en Docker
Abre una terminal en tu proyecto y ejecuta:

```bash
docker compose -f docker-compose.local.yml up -d
```
*Esto descargará y arrancará MySQL y Redis en segundo plano.*

### Paso C: Arrancar tu aplicación (como siempre)
En tu terminal, ejecuta tus comandos habituales:

```bash
php artisan serve
npm run dev
```

### Comandos útiles locales:
* **Ver si la base de datos está corriendo:** `docker ps` o `docker compose -f docker-compose.local.yml ps`
* **Apagar la base de datos:** `docker compose -f docker-compose.local.yml down`
* **Borrar la base de datos completa (reset):** `docker compose -f docker-compose.local.yml down -v`

---

## 3. Despliegue en Producción (La Nube)

Cuando subas tu código a un servidor (ej. VPS, DigitalOcean), utilizarás el archivo de producción.

1. Configura tus contraseñas seguras en el archivo `.env.docker` (este archivo no se sube a GitHub por seguridad, debes crearlo en el servidor).
2. Ejecuta el entorno completo:

```bash
docker compose -f docker-compose.prod.yml up -d --build
```
*Esto construirá la imagen de Laravel y arrancará todos los servicios.*

---

## 💡 Notas Importantes
- **Compatibilidad SQL:** Se ha arreglado el orden de las migraciones para que sean 100% compatibles con las reglas estrictas de llaves foráneas (Foreign Keys) de MySQL 8.
- **Conexión a BD Local:** Puedes conectarte a la base de datos local usando DBeaver, TablePlus o HeidiSQL utilizando el host `127.0.0.1`, puerto `3306`, usuario `alitas` y contraseña `password`.
