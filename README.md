# Alitas Vega — Sistema POS

> Sistema de Punto de Venta (POS) interno para el restaurante **Alitas Vega**,
> con soporte para dos sucursales: **Cochabamba** y **Tarija**.

---

## 📋 Descripción

Aplicación web monolítica para gestión de pedidos, mesas, caja, inventario,
menú y reportes. Diseñada para operar en hosting compartido cPanel (PiensaHost)
con soporte multi-sucursal.

---

## 🛠️ Stack Tecnológico

| Tecnología | Versión | Propósito |
|---|---|---|
| PHP | 8.2+ | Lenguaje backend |
| Laravel | 11.x | Framework principal |
| MySQL | 8.x | Base de datos relacional |
| Livewire | 3.x | Componentes reactivos server-side |
| Alpine.js | 3.x | JavaScript ligero para interactividad |
| DomPDF | 3.x | Generación de tickets en PDF |

---

## ✅ Requisitos Previos

- PHP >= 8.2 con extensiones: `fileinfo`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `ctype`, `curl`, `zip`
- Composer >= 2.x
- MySQL >= 8.0
- Node.js >= 18.x (para compilar assets)

---

## 🚀 Instalación Local

```bash
# 1. Clonar el repositorio
git clone https://github.com/AlvaroThg/alitasvega.git
cd alitasvega

# 2. Instalar dependencias PHP
composer install

# 3. Instalar dependencias JS
npm install && npm run dev

# 4. Configurar el entorno
cp .env.example .env
php artisan key:generate

# 5. Configurar la base de datos en .env
# DB_HOST=127.0.0.1
# DB_DATABASE=alitasvega
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Ejecutar migraciones
php artisan migrate

# 7. Levantar el servidor local
php artisan serve
```

---

## 🏗️ Arquitectura de Módulos

La aplicación sigue una arquitectura de **monolito modular** bajo `app/Modules/`.
Cada módulo es autónomo y agrupa su propia lógica de negocio, controladores,
componentes Livewire y vistas.

```
app/Modules/
├── Auth/               # Autenticación manual (login, logout, roles)
├── Branch/             # Gestión de sucursales (Cochabamba, Tarija)
├── Menu/               # Categorías, platillos y precios
├── Orders/             # Pedidos por mesa y para llevar
├── Tables/             # Mesas y su estado (libre, ocupada, reservada)
├── Cash/               # Apertura/cierre de caja y movimientos
├── Inventory/          # Control de insumos y stock
├── Promotions/         # Promociones y descuentos
├── Tickets/            # Generación de tickets/facturas en PDF
└── Reports/            # Reportes de ventas, inventario y caja
```

### Estructura interna de cada módulo

```
app/Modules/{Modulo}/
├── Models/             # Modelos Eloquent del módulo
├── Http/
│   ├── Controllers/    # Controladores HTTP tradicionales
│   ├── Livewire/       # Componentes Livewire 3
│   └── Middleware/     # Middleware específico del módulo
└── Views/              # Vistas Blade del módulo
```

---

## 🔐 Middleware Registrado

| Alias | Clase | Descripción |
|---|---|---|
| `role` | `CheckRole` | Restringe acceso según rol del usuario |
| `branch` | `EnsureActiveBranch` | Requiere sucursal activa en sesión |

**Uso en rutas:**
```php
Route::middleware(['role:admin', 'branch'])->group(function () {
    // rutas protegidas
});
```

---

## 🌎 Configuración Regional

- **Timezone:** `America/La_Paz` (UTC-4, Bolivia)
- **Locale:** `es` (español)
- **Faker Locale:** `es_BO`

---

## 📂 Convención de Namespaces

```
App\Modules\{Modulo}\Models\{Modelo}
App\Modules\{Modulo}\Http\Controllers\{Controlador}
App\Modules\{Modulo}\Http\Livewire\{Componente}
App\Modules\{Modulo}\Http\Middleware\{Middleware}
```

---

## 📦 Despliegue en cPanel (PiensaHost)

1. Subir archivos vía FTP (excluir `node_modules/`, `vendor/`).
2. Ejecutar `composer install --no-dev --optimize-autoloader` desde SSH.
3. Configurar el `.env` de producción.
4. Apuntar el `DocumentRoot` a la carpeta `public/`.
5. Ejecutar `php artisan migrate --force`.

---

## 📄 Licencia

Uso interno — Alitas Vega © 2025
