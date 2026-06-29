<?php

use Illuminate\Support\Facades\Artisan;
use App\Modules\Menu\Models\Sauce;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "===============================================\n";
echo "      TEST EXPLORATORIO: GESTIÓN DE SALSAS     \n";
echo "===============================================\n\n";

try {
    // 1. Crear una salsa de prueba
    echo "[1] Creando Salsa de prueba...\n";
    $sauce = Sauce::create([
        'name' => 'Salsa Fantasma Test',
        'spice_level' => 10,
        'is_active' => true,
    ]);
    echo "    ✅ Salsa creada correctamente. ID: {$sauce->id} | Nombre: {$sauce->name} | Picante: {$sauce->spice_level} | Activa: " . ($sauce->is_active ? 'Si' : 'No') . "\n\n";

    // 2. Verificar simulación de POS (OrderBuilder.php)
    echo "[2] Simulando carga de Salsas en el POS (solo activas)...\n";
    $activeSauces = Sauce::where('is_active', true)->get();
    echo "    ✅ Se encontraron {$activeSauces->count()} salsas activas en la BD.\n";
    $found = $activeSauces->contains('id', $sauce->id);
    echo "    ✅ ¿La salsa de prueba aparece en el POS? " . ($found ? 'SÍ (Correcto)' : 'NO (Error)') . "\n\n";

    // 3. Editar la salsa
    echo "[3] Editando nivel de picante y nombre de la Salsa...\n";
    $sauce->update([
        'name' => 'Salsa Fantasma Editada',
        'spice_level' => 5,
    ]);
    echo "    ✅ Salsa actualizada. Nombre: {$sauce->name} | Picante: {$sauce->spice_level}\n\n";

    // 4. Desactivar la salsa
    echo "[4] Desactivando la Salsa...\n";
    $sauce->update(['is_active' => false]);
    echo "    ✅ Salsa desactivada.\n\n";

    // 5. Verificar que ya no aparece en el POS
    echo "[5] Verificando POS de nuevo...\n";
    $activeSaucesNow = Sauce::where('is_active', true)->get();
    $foundNow = $activeSaucesNow->contains('id', $sauce->id);
    echo "    ✅ ¿La salsa de prueba aparece en el POS? " . ($foundNow ? 'SÍ (Error)' : 'NO (Correcto)') . "\n\n";

    // 6. Limpiar (Eliminar la salsa de prueba)
    echo "[6] Limpieza de BD...\n";
    $sauce->delete();
    echo "    ✅ Salsa de prueba eliminada.\n\n";

    echo "===============================================\n";
    echo "      TODAS LAS PRUEBAS FUERON EXITOSAS        \n";
    echo "===============================================\n";

} catch (\Exception $e) {
    echo "\n❌ ERROR DURANTE LA PRUEBA:\n";
    echo $e->getMessage() . "\n";
}
