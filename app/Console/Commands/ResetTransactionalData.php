<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Deja la base lista para arrancar en limpio: borra la operación (ventas, caja,
 * movimientos) pero conserva la configuración (usuarios, sucursales, menú,
 * salsas, mesas, promociones y los registros de inventario).
 *
 *   php artisan pos:reset-operacion               (pide confirmación)
 *   php artisan pos:reset-operacion --force       (sin preguntar)
 *   php artisan pos:reset-operacion --stock=0     (además pone el stock en 0)
 */
class ResetTransactionalData extends Command
{
    protected $signature = 'pos:reset-operacion
                            {--force : Ejecutar sin pedir confirmación}
                            {--stock=keep : "keep" mantiene las cantidades de stock, "0" las pone en cero}';

    protected $description = 'Borra ventas, caja y movimientos, conservando usuarios, menú e inventario';

    /**
     * Se borran de la más dependiente a la menos, para no romper las llaves foráneas.
     */
    private const TABLAS = [
        'order_item_sauces',
        'order_promotions',
        'order_payments',
        'tickets',
        'order_items',
        'orders',
        'cash_movements',
        'cash_sessions',
        'inventory_movements',
    ];

    public function handle(): int
    {
        $this->warn('Se BORRARÁN de forma permanente:');
        foreach (self::TABLAS as $tabla) {
            if (Schema::hasTable($tabla)) {
                $this->line(sprintf('   %-22s %s filas', $tabla, DB::table($tabla)->count()));
            }
        }

        $this->newLine();
        $this->info('Se CONSERVAN: usuarios, sucursales, categorías, productos, precios, salsas, mesas, promociones y los registros de inventario.');
        $this->newLine();

        if (!$this->option('force') && !$this->confirm('¿Confirmas borrar la operación? Esto no se puede deshacer.')) {
            $this->comment('Cancelado. No se borró nada.');
            return self::SUCCESS;
        }

        $driver = DB::getDriverName();
        $borradas = [];

        // OJO: TRUNCATE hace commit implícito en MySQL, así que el borrado NO puede
        // ir dentro de DB::transaction() (fallaría al cerrarla: "no active transaction").
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        try {
            foreach (self::TABLAS as $tabla) {
                if (!Schema::hasTable($tabla)) {
                    continue;
                }

                $borradas[$tabla] = DB::table($tabla)->count();

                // truncate reinicia los IDs; así los pedidos vuelven a numerar desde 1.
                if ($driver === 'mysql') {
                    DB::statement("TRUNCATE TABLE `{$tabla}`");
                } else {
                    DB::table($tabla)->delete();
                    if (Schema::hasTable('sqlite_sequence')) {
                        DB::table('sqlite_sequence')->where('name', $tabla)->delete();
                    }
                }
            }
        } finally {
            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        }

        // Los ajustes posteriores sí son transaccionales (no llevan DDL).
        DB::transaction(function () {
            // Las mesas quedaban "ocupadas" apuntando a pedidos que ya no existen.
            if (Schema::hasTable('tables')) {
                DB::table('tables')->update(['status' => 'available']);
            }

            // La Caja Chica arranca en cero: su saldo venía de los movimientos borrados.
            if (Schema::hasColumn('branches', 'petty_cash_balance')) {
                DB::table('branches')->update(['petty_cash_balance' => 0]);
            }

            if ($this->option('stock') === '0' && Schema::hasTable('inventory')) {
                DB::table('inventory')->update(['stock_quantity' => 0]);
            }
        });

        $this->newLine();
        foreach ($borradas as $tabla => $filas) {
            $this->line(sprintf('   borradas %-22s %s filas', $tabla, $filas));
        }

        $this->newLine();
        $this->info('Listo. Mesas liberadas y Caja Chica en 0.');
        $this->info($this->option('stock') === '0'
            ? 'El stock del inventario quedó en 0.'
            : 'Se mantuvieron las cantidades de stock.');

        return self::SUCCESS;
    }
}
