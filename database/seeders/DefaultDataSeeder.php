<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Outlet;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ModifierGroup;
use App\Models\Modifier;
use App\Models\Topping;
use App\Models\User;
use App\Models\Unit;
use App\Models\Ingredient;
use Illuminate\Support\Str;

class DefaultDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create default outlet
        $outlet = Outlet::firstOrCreate(
            ['code' => 'WPK01'],
            [
                'name' => 'Warkop Kenangan Pusat',
                'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
                'phone' => '021-555-1234',
                'email' => 'pusat@warkopkenangan.com',
                'npwp' => '12.345.678.9-012.000',
                'operational_start' => '06:00:00',
                'operational_end' => '22:00:00',
                'receipt_footer' => 'Terima kasih telah berkunjung!\nFollow IG: @warkopkenangan\nWiFi: WarkopGuest / password123',
                'is_active' => true,
                'settings' => [
                    'tax_rate' => 0.11,
                    'service_charge' => 0.05,
                    'rounding' => 100,
                    'currency' => 'IDR',
                ],
            ]
        );

        // Create default units
        $units = [
            ['name' => 'Kilogram', 'symbol' => 'kg', 'type' => 'weight', 'base_conversion' => 1, 'is_base' => true],
            ['name' => 'Gram', 'symbol' => 'g', 'type' => 'weight', 'base_conversion' => 0.001, 'is_base' => false],
            ['name' => 'Liter', 'symbol' => 'L', 'type' => 'volume', 'base_conversion' => 1, 'is_base' => true],
            ['name' => 'Mililiter', 'symbol' => 'ml', 'type' => 'volume', 'base_conversion' => 0.001, 'is_base' => false],
            ['name' => 'Piece', 'symbol' => 'pcs', 'type' => 'count', 'base_conversion' => 1, 'is_base' => true],
            ['name' => 'Pack', 'symbol' => 'pack', 'type' => 'count', 'base_conversion' => 1, 'is_base' => false],
        ];

        foreach ($units as $unitData) {
            Unit::firstOrCreate(
                ['outlet_id' => $outlet->id, 'name' => $unitData['name']],
                array_merge($unitData, ['outlet_id' => $outlet->id])
            );
        }

        $unitKg = Unit::where('outlet_id', $outlet->id)->where('symbol', 'kg')->first();
        $unitG = Unit::where('outlet_id', $outlet->id)->where('symbol', 'g')->first();
        $unitL = Unit::where('outlet_id', $outlet->id)->where('symbol', 'L')->first();
        $unitMl = Unit::where('outlet_id', $outlet->id)->where('symbol', 'ml')->first();
        $unitPcs = Unit::where('outlet_id', $outlet->id)->where('symbol', 'pcs')->first();

        // Create default categories
        $categories = [
            ['name' => 'Kopi', 'icon' => 'coffee', 'color' => '#6F4E37', 'sort_order' => 1, 'is_kds_station' => true, 'kds_station_name' => 'Barista'],
            ['name' => 'Non-Kopi', 'icon' => 'cup-soda', 'color' => '#2E86C1', 'sort_order' => 2, 'is_kds_station' => true, 'kds_station_name' => 'Barista'],
            ['name' => 'Makanan Berat', 'icon' => 'utensils', 'color' => '#E67E22', 'sort_order' => 3, 'is_kds_station' => true, 'kds_station_name' => 'Kitchen'],
            ['name' => 'Camilan', 'icon' => 'cookie', 'color' => '#F39C12', 'sort_order' => 4],
            ['name' => 'Indomie', 'icon' => 'bowl-rice', 'color' => '#E74C3C', 'sort_order' => 5, 'is_kds_station' => true, 'kds_station_name' => 'Kitchen'],
            ['name' => 'Rokok', 'icon' => 'cigarette', 'color' => '#7F8C8D', 'sort_order' => 6],
        ];

        $categoryModels = [];
        foreach ($categories as $index => $catData) {
            $category = Category::firstOrCreate(
                ['outlet_id' => $outlet->id, 'slug' => Str::slug($catData['name'])],
                array_merge($catData, [
                    'outlet_id' => $outlet->id,
                    'is_active' => true,
                ])
            );
            $categoryModels[$catData['name']] = $category;
        }

        // Create default modifier groups
        $modifierGroups = [
            [
                'name' => 'Level Gula',
                'slug' => 'level-gula',
                'selection_type' => 'single',
                'is_required' => true,
                'min_selection' => 1,
                'max_selection' => 1,
                'modifiers' => [
                    ['name' => 'Less Sugar', 'slug' => 'less-sugar', 'price_adjustment' => 0, 'is_default' => false],
                    ['name' => 'Normal', 'slug' => 'normal', 'price_adjustment' => 0, 'is_default' => true],
                    ['name' => 'Extra Sugar', 'slug' => 'extra-sugar', 'price_adjustment' => 0, 'is_default' => false],
                ],
            ],
            [
                'name' => 'Level Es',
                'slug' => 'level-es',
                'selection_type' => 'single',
                'is_required' => true,
                'min_selection' => 1,
                'max_selection' => 1,
                'modifiers' => [
                    ['name' => 'Less Ice', 'slug' => 'less-ice', 'price_adjustment' => 0, 'is_default' => false],
                    ['name' => 'Normal Ice', 'slug' => 'normal-ice', 'price_adjustment' => 0, 'is_default' => true],
                    ['name' => 'Extra Ice', 'slug' => 'extra-ice', 'price_adjustment' => 0, 'is_default' => false],
                    ['name' => 'No Ice (Dingin)', 'slug' => 'no-ice', 'price_adjustment' => 0, 'is_default' => false],
                ],
            ],
            [
                'name' => 'Level Pedas',
                'slug' => 'level-pedas',
                'selection_type' => 'single',
                'is_required' => true,
                'min_selection' => 1,
                'max_selection' => 1,
                'modifiers' => [
                    ['name' => 'Tidak Pedas', 'slug' => 'tidak-pedas', 'price_adjustment' => 0, 'is_default' => false],
                    ['name' => 'Pedas Level 1', 'slug' => 'pedas-1', 'price_adjustment' => 0, 'is_default' => true],
                    ['name' => 'Pedas Level 2', 'slug' => 'pedas-2', 'price_adjustment' => 0, 'is_default' => false],
                    ['name' => 'Pedas Level 3', 'slug' => 'pedas-3', 'price_adjustment' => 0, 'is_default' => false],
                    ['name' => 'Pedas Level 4', 'slug' => 'pedas-4', 'price_adjustment' => 0, 'is_default' => false],
                    ['name' => 'Pedas Level 5', 'slug' => 'pedas-5', 'price_adjustment' => 0, 'is_default' => false],
                ],
            ],
            [
                'name' => 'Varian Indomie',
                'slug' => 'varian-indomie',
                'selection_type' => 'single',
                'is_required' => true,
                'min_selection' => 1,
                'max_selection' => 1,
                'modifiers' => [
                    ['name' => 'Goreng', 'slug' => 'goreng', 'price_adjustment' => 0, 'is_default' => true],
                    ['name' => 'Rebus', 'slug' => 'rebus', 'price_adjustment' => 0, 'is_default' => false],
                ],
            ],
            [
                'name' => 'Telur Indomie',
                'slug' => 'telur-indomie',
                'selection_type' => 'single',
                'is_required' => false,
                'min_selection' => 0,
                'max_selection' => 1,
                'modifiers' => [
                    ['name' => 'Mata Sapi', 'slug' => 'mata-sapi', 'price_adjustment' => 3000, 'is_default' => false],
                    ['name' => 'Dadar', 'slug' => 'dadar', 'price_adjustment' => 3000, 'is_default' => false],
                    ['name' => 'Setengah Matang', 'slug' => 'setengah-matang', 'price_adjustment' => 3000, 'is_default' => false],
                ],
            ],
        ];

        $modifierGroupModels = [];
        foreach ($modifierGroups as $mgData) {
            $modifiersData = $mgData['modifiers'];
            unset($mgData['modifiers']);
            
            $mg = ModifierGroup::firstOrCreate(
                ['outlet_id' => $outlet->id, 'slug' => $mgData['slug']],
                array_merge($mgData, ['outlet_id' => $outlet->id, 'is_active' => true])
            );
            $modifierGroupModels[$mgData['slug']] = $mg;

            foreach ($modifiersData as $modData) {
                Modifier::firstOrCreate(
                    ['modifier_group_id' => $mg->id, 'slug' => $modData['slug']],
                    array_merge($modData, ['modifier_group_id' => $mg->id, 'is_active' => true])
                );
            }
        }

        // Create default toppings
        $toppings = [
            ['name' => 'Boba', 'slug' => 'boba', 'price' => 3000, 'unit' => 'pcs', 'stock_qty' => 100, 'sort_order' => 1],
            ['name' => 'Jelly', 'slug' => 'jelly', 'price' => 2000, 'unit' => 'pcs', 'stock_qty' => 100, 'sort_order' => 2],
            ['name' => 'Grass Jelly (Cincau)', 'slug' => 'cincau', 'price' => 2000, 'unit' => 'pcs', 'stock_qty' => 100, 'sort_order' => 3],
            ['name' => 'Extra Shot Espresso', 'slug' => 'extra-shot', 'price' => 4000, 'unit' => 'shot', 'stock_qty' => 50, 'sort_order' => 4],
            ['name' => 'Whipped Cream', 'slug' => 'whipped-cream', 'price' => 2000, 'unit' => 'pcs', 'stock_qty' => 50, 'sort_order' => 5],
            ['name' => 'Keju Mozzarella', 'slug' => 'keju-mozzarella', 'price' => 3000, 'unit' => 'pcs', 'stock_qty' => 50, 'sort_order' => 6],
            ['name' => 'Sosis', 'slug' => 'sosis', 'price' => 4000, 'unit' => 'pcs', 'stock_qty' => 50, 'sort_order' => 7],
        ];

        $toppingModels = [];
        foreach ($toppings as $topData) {
            $topping = Topping::firstOrCreate(
                ['outlet_id' => $outlet->id, 'slug' => $topData['slug']],
                array_merge($topData, ['outlet_id' => $outlet->id, 'is_active' => true, 'track_stock' => true])
            );
            $toppingModels[$topData['slug']] = $topping;
        }

        // Create default ingredients
        $ingredients = [
            ['name' => 'Biji Kopi Arabika', 'slug' => 'biji-kopi-arabika', 'unit_id' => $unitKg?->id, 'current_stock' => 10, 'min_stock' => 2, 'avg_cost' => 180000, 'sku' => 'BKA-001'],
            ['name' => 'Biji Kopi Robusta', 'slug' => 'biji-kopi-robusta', 'unit_id' => $unitKg?->id, 'current_stock' => 10, 'min_stock' => 2, 'avg_cost' => 120000, 'sku' => 'BKR-001'],
            ['name' => 'Susu UHT Full Cream', 'slug' => 'susu-uht-full-cream', 'unit_id' => $unitL?->id, 'current_stock' => 50, 'min_stock' => 10, 'avg_cost' => 18000, 'sku' => 'SUFC-001'],
            ['name' => 'Susu Kental Manis', 'slug' => 'susu-kental-manis', 'unit_id' => $unitPcs?->id, 'current_stock' => 100, 'min_stock' => 20, 'avg_cost' => 8000, 'sku' => 'SKM-001'],
            ['name' => 'Gula Pasir', 'slug' => 'gula-pasir', 'unit_id' => $unitKg?->id, 'current_stock' => 20, 'min_stock' => 5, 'avg_cost' => 14000, 'sku' => 'GP-001'],
            ['name' => 'Gula Aren', 'slug' => 'gula-aren', 'unit_id' => $unitKg?->id, 'current_stock' => 5, 'min_stock' => 1, 'avg_cost' => 45000, 'sku' => 'GA-001'],
            ['name' => 'Es Batu', 'slug' => 'es-batu', 'unit_id' => $unitKg?->id, 'current_stock' => 50, 'min_stock' => 10, 'avg_cost' => 5000, 'sku' => 'EB-001'],
            ['name' => 'Indomie Goreng', 'slug' => 'indomie-goreng', 'unit_id' => $unitPcs?->id, 'current_stock' => 100, 'min_stock' => 20, 'avg_cost' => 3000, 'sku' => 'IG-001'],
            ['name' => 'Indomie Rebus', 'slug' => 'indomie-rebus', 'unit_id' => $unitPcs?->id, 'current_stock' => 100, 'min_stock' => 20, 'avg_cost' => 3000, 'sku' => 'IR-001'],
            ['name' => 'Telur Ayam', 'slug' => 'telur-ayam', 'unit_id' => $unitPcs?->id, 'current_stock' => 200, 'min_stock' => 30, 'avg_cost' => 2500, 'sku' => 'TA-001'],
            ['name' => 'Bawang Goreng', 'slug' => 'bawang-goreng', 'unit_id' => $unitG?->id, 'current_stock' => 2000, 'min_stock' => 500, 'avg_cost' => 80, 'sku' => 'BG-001'],
            ['name' => 'Daun Bawang', 'slug' => 'daun-bawang', 'unit_id' => $unitG?->id, 'current_stock' => 500, 'min_stock' => 100, 'avg_cost' => 100, 'sku' => 'DB-001'],
            ['name' => 'Cabai Rawit', 'slug' => 'cabai-rawit', 'unit_id' => $unitG?->id, 'current_stock' => 1000, 'min_stock' => 200, 'avg_cost' => 150, 'sku' => 'CR-001'],
            ['name' => 'Sosis Sapi', 'slug' => 'sosis-sapi', 'unit_id' => $unitPcs?->id, 'current_stock' => 50, 'min_stock' => 10, 'avg_cost' => 5000, 'sku' => 'SS-001'],
            ['name' => 'Keju Mozzarella', 'slug' => 'keju-mozzarella', 'unit_id' => $unitG?->id, 'current_stock' => 2000, 'min_stock' => 500, 'avg_cost' => 120, 'sku' => 'KM-001'],
            ['name' => 'Boba Pearls', 'slug' => 'boba-pearls', 'unit_id' => $unitKg?->id, 'current_stock' => 5, 'min_stock' => 1, 'avg_cost' => 80000, 'sku' => 'BP-001'],
            ['name' => 'Cincau', 'slug' => 'cincau', 'unit_id' => $unitKg?->id, 'current_stock' => 5, 'min_stock' => 1, 'avg_cost' => 60000, 'sku' => 'CC-001'],
        ];

        $ingredientModels = [];
        foreach ($ingredients as $ingData) {
            $ingredient = Ingredient::firstOrCreate(
                ['outlet_id' => $outlet->id, 'slug' => $ingData['slug']],
                array_merge($ingData, ['outlet_id' => $outlet->id, 'is_active' => true, 'track_stock' => true])
            );
            $ingredientModels[$ingData['slug']] = $ingredient;
        }

        // Create default products
        $products = [
            // Kopi
            [
                'name' => 'Kopi Susu',
                'slug' => 'kopi-susu',
                'category' => 'Kopi',
                'base_price' => 18000,
                'cost_price' => 5500,
                'description' => 'Kopi susu signature dengan gula aren',
                'is_bestseller' => true,
                'prep_time_minutes' => 3,
                'kds_station' => 'barista',
                'variants' => [],
                'modifier_groups' => ['level-gula', 'level-es'],
                'toppings' => ['boba', 'jelly', 'cincau', 'extra-shot', 'whipped-cream'],
                'recipe' => [
                    'biji-kopi-arabika' => 18, // gram
                    'susu-uht-full-cream' => 150, // ml
                    'gula-aren' => 15, // gram
                    'es-batu' => 100, // gram
                ],
            ],
            [
                'name' => 'Americano',
                'slug' => 'americano',
                'category' => 'Kopi',
                'base_price' => 15000,
                'cost_price' => 4000,
                'description' => 'Espresso dengan air panas/dingin',
                'is_bestseller' => false,
                'prep_time_minutes' => 2,
                'kds_station' => 'barista',
                'variants' => [
                    ['name' => 'Hot', 'slug' => 'hot', 'price_adjustment' => 0, 'is_default' => true],
                    ['name' => 'Ice', 'slug' => 'ice', 'price_adjustment' => 2000, 'is_default' => false],
                ],
                'modifier_groups' => ['level-gula', 'level-es'],
                'toppings' => ['extra-shot'],
                'recipe' => [
                    'biji-kopi-arabika' => 18,
                    'es-batu' => 100,
                ],
            ],
            [
                'name' => 'Cappuccino',
                'slug' => 'cappuccino',
                'category' => 'Kopi',
                'base_price' => 20000,
                'cost_price' => 6000,
                'description' => 'Espresso dengan susu steamed dan foam',
                'is_bestseller' => false,
                'prep_time_minutes' => 3,
                'kds_station' => 'barista',
                'variants' => [
                    ['name' => 'Hot', 'slug' => 'hot', 'price_adjustment' => 0, 'is_default' => true],
                    ['name' => 'Ice', 'slug' => 'ice', 'price_adjustment' => 2000, 'is_default' => false],
                ],
                'modifier_groups' => ['level-gula'],
                'toppings' => ['extra-shot', 'whipped-cream'],
                'recipe' => [
                    'biji-kopi-arabika' => 18,
                    'susu-uht-full-cream' => 120,
                    'es-batu' => 80,
                ],
            ],
            [
                'name' => 'Vietnam Drip',
                'slug' => 'vietnam-drip',
                'category' => 'Kopi',
                'base_price' => 18000,
                'cost_price' => 5000,
                'description' => 'Kopi drip Vietnam dengan susu kental manis',
                'is_bestseller' => true,
                'prep_time_minutes' => 5,
                'kds_station' => 'barista',
                'variants' => [
                    ['name' => 'Hitam', 'slug' => 'hitam', 'price_adjustment' => 0, 'is_default' => false],
                    ['name' => 'Susu', 'slug' => 'susu', 'price_adjustment' => 2000, 'is_default' => true],
                ],
                'modifier_groups' => ['level-gula', 'level-es'],
                'toppings' => [],
                'recipe' => [
                    'biji-kopi-robusta' => 20,
                    'susu-kental-manis' => 20,
                    'es-batu' => 100,
                ],
            ],

            // Non-Kopi
            [
                'name' => 'Es Teh Manis',
                'slug' => 'es-teh-manis',
                'category' => 'Non-Kopi',
                'base_price' => 8000,
                'cost_price' => 1500,
                'description' => 'Teh manis segar',
                'is_bestseller' => true,
                'prep_time_minutes' => 1,
                'kds_station' => 'barista',
                'variants' => [],
                'modifier_groups' => ['level-gula', 'level-es'],
                'toppings' => [],
                'recipe' => [
                    'gula-pasir' => 15,
                    'es-batu' => 150,
                ],
            ],
            [
                'name' => 'Es Jeruk Peras',
                'slug' => 'es-jeruk-peras',
                'category' => 'Non-Kopi',
                'base_price' => 12000,
                'cost_price' => 4000,
                'description' => 'Jeruk peras segar',
                'is_bestseller' => false,
                'prep_time_minutes' => 2,
                'kds_station' => 'barista',
                'variants' => [],
                'modifier_groups' => ['level-gula', 'level-es'],
                'toppings' => [],
                'recipe' => [
                    'gula-pasir' => 10,
                    'es-batu' => 150,
                ],
            ],
            [
                'name' => 'Matcha Latte',
                'slug' => 'matcha-latte',
                'category' => 'Non-Kopi',
                'base_price' => 22000,
                'cost_price' => 8000,
                'description' => 'Matcha premium dengan susu',
                'is_bestseller' => false,
                'prep_time_minutes' => 3,
                'kds_station' => 'barista',
                'variants' => [],
                'modifier_groups' => ['level-gula', 'level-es'],
                'toppings' => ['boba', 'whipped-cream'],
                'recipe' => [
                    'susu-uht-full-cream' => 180,
                    'gula-pasir' => 10,
                    'es-batu' => 100,
                ],
            ],
            [
                'name' => 'Chocolate',
                'slug' => 'chocolate',
                'category' => 'Non-Kopi',
                'base_price' => 18000,
                'cost_price' => 6000,
                'description' => 'Cokelat keju susu',
                'is_bestseller' => false,
                'prep_time_minutes' => 3,
                'kds_station' => 'barista',
                'variants' => [
                    ['name' => 'Hot', 'slug' => 'hot', 'price_adjustment' => 0, 'is_default' => true],
                    ['name' => 'Ice', 'slug' => 'ice', 'price_adjustment' => 2000, 'is_default' => false],
                ],
                'modifier_groups' => ['level-gula', 'level-es'],
                'toppings' => ['whipped-cream', 'boba'],
                'recipe' => [
                    'susu-uht-full-cream' => 180,
                    'gula-pasir' => 15,
                    'es-batu' => 80,
                ],
            ],

            // Makanan Berat
            [
                'name' => 'Nasi Goreng Spesial',
                'slug' => 'nasi-goreng-spesial',
                'category' => 'Makanan Berat',
                'base_price' => 25000,
                'cost_price' => 10000,
                'description' => 'Nasi goreng dengan telur, ayam, sosis, sayur',
                'is_bestseller' => true,
                'prep_time_minutes' => 8,
                'kds_station' => 'kitchen',
                'variants' => [],
                'modifier_groups' => ['level-pedas'],
                'toppings' => ['keju-mozzarella', 'sosis', 'telur-ayam'],
                'recipe' => [
                    'biji-kopi-arabika' => 0, // placeholder, actual ingredients would be rice, chicken, etc.
                ],
            ],
            [
                'name' => 'Mie Goreng',
                'slug' => 'mie-goreng',
                'category' => 'Makanan Berat',
                'base_price' => 20000,
                'cost_price' => 8000,
                'description' => 'Mie goreng dengan telur dan sayuran',
                'is_bestseller' => false,
                'prep_time_minutes' => 7,
                'kds_station' => 'kitchen',
                'variants' => [],
                'modifier_groups' => ['level-pedas'],
                'toppings' => ['keju-mozzarella', 'sosis'],
                'recipe' => [],
            ],
            [
                'name' => 'Ayam Geprek',
                'slug' => 'ayam-geprek',
                'category' => 'Makanan Berat',
                'base_price' => 22000,
                'cost_price' => 9000,
                'description' => 'Ayam goreng geprek dengan sambal bawang',
                'is_bestseller' => true,
                'prep_time_minutes' => 10,
                'kds_station' => 'kitchen',
                'variants' => [],
                'modifier_groups' => ['level-pedas'],
                'toppings' => ['keju-mozzarella'],
                'recipe' => [],
            ],

            // Camilan
            [
                'name' => 'Kentang Goreng',
                'slug' => 'kentang-goreng',
                'category' => 'Camilan',
                'base_price' => 15000,
                'cost_price' => 5000,
                'description' => 'Kentang goreng crispy dengan saus',
                'is_bestseller' => false,
                'prep_time_minutes' => 5,
                'kds_station' => 'kitchen',
                'variants' => [],
                'modifier_groups' => [],
                'toppings' => ['keju-mozzarella'],
                'recipe' => [],
            ],
            [
                'name' => 'Roti Bakar Keju',
                'slug' => 'roti-bakar-keju',
                'category' => 'Camilan',
                'base_price' => 18000,
                'cost_price' => 7000,
                'description' => 'Roti bakar dengan keju leleh',
                'is_bestseller' => false,
                'prep_time_minutes' => 5,
                'kds_station' => 'kitchen',
                'variants' => [],
                'modifier_groups' => [],
                'toppings' => [],
                'recipe' => [],
            ],

            // Indomie
            [
                'name' => 'Indomie Goreng',
                'slug' => 'indomie-goreng-menu',
                'category' => 'Indomie',
                'base_price' => 12000,
                'cost_price' => 5000,
                'description' => 'Indomie goreng dengan telur pilihan',
                'is_bestseller' => true,
                'prep_time_minutes' => 5,
                'kds_station' => 'kitchen',
                'variants' => [],
                'modifier_groups' => ['varian-indomie', 'telur-indomie', 'level-pedas'],
                'toppings' => ['keju-mozzarella', 'sosis', 'bawang-goreng'],
                'recipe' => [
                    'indomie-goreng' => 1,
                    'telur-ayam' => 1,
                    'bawang-goreng' => 10,
                    'daun-bawang' => 5,
                    'cabai-rawit' => 5,
                ],
            ],
            [
                'name' => 'Indomie Rebus',
                'slug' => 'indomie-rebus-menu',
                'category' => 'Indomie',
                'base_price' => 12000,
                'cost_price' => 5000,
                'description' => 'Indomie rebus dengan telur pilihan',
                'is_bestseller' => false,
                'prep_time_minutes' => 5,
                'kds_station' => 'kitchen',
                'variants' => [],
                'modifier_groups' => ['varian-indomie', 'telur-indomie', 'level-pedas'],
                'toppings' => ['keju-mozzarella', 'sosis', 'bawang-goreng'],
                'recipe' => [
                    'indomie-rebus' => 1,
                    'telur-ayam' => 1,
                    'bawang-goreng' => 10,
                    'daun-bawang' => 5,
                    'cabai-rawit' => 5,
                ],
            ],

            // Rokok
            [
                'name' => 'Mild (Filter)',
                'slug' => 'mild-filter',
                'category' => 'Rokok',
                'base_price' => 25000,
                'cost_price' => 22000,
                'description' => 'Rokok filter mild',
                'is_bestseller' => false,
                'prep_time_minutes' => 0,
                'kds_station' => null,
                'variants' => [],
                'modifier_groups' => [],
                'toppings' => [],
                'recipe' => [],
            ],
            [
                'name' => 'Mild (Kretek)',
                'slug' => 'mild-kretek',
                'category' => 'Rokok',
                'base_price' => 25000,
                'cost_price' => 22000,
                'description' => 'Rokok kretek mild',
                'is_bestseller' => false,
                'prep_time_minutes' => 0,
                'kds_station' => null,
                'variants' => [],
                'modifier_groups' => [],
                'toppings' => [],
                'recipe' => [],
            ],
        ];

        foreach ($products as $prodData) {
            $category = $categoryModels[$prodData['category']] ?? null;
            if (!$category) continue;

            $variants = $prodData['variants'];
            $modifierGroupSlugs = $prodData['modifier_groups'];
            $toppingSlugs = $prodData['toppings'];
            $recipe = $prodData['recipe'];
            
            unset($prodData['variants'], $prodData['modifier_groups'], $prodData['toppings'], $prodData['recipe'], $prodData['category']);

            $product = Product::firstOrCreate(
                ['outlet_id' => $outlet->id, 'slug' => $prodData['slug']],
                array_merge($prodData, [
                    'outlet_id' => $outlet->id,
                    'category_id' => $category->id,
                    'type' => 'finished_good',
                    'is_active' => true,
                    'track_stock' => false,
                    'stock_qty' => 0,
                ])
            );

            // Create variants
            foreach ($variants as $index => $varData) {
                ProductVariant::firstOrCreate(
                    ['product_id' => $product->id, 'slug' => $varData['slug']],
                    array_merge($varData, ['product_id' => $product->id, 'sort_order' => $index])
                );
            }

            // Attach modifier groups
            foreach ($modifierGroupSlugs as $index => $mgSlug) {
                if (isset($modifierGroupModels[$mgSlug])) {
                    $product->modifierGroups()->syncWithoutDetaching([
                        $modifierGroupModels[$mgSlug]->id => ['sort_order' => $index]
                    ]);
                }
            }

            // Attach toppings
            foreach ($toppingSlugs as $index => $toppingSlug) {
                if (isset($toppingModels[$toppingSlug])) {
                    $product->toppings()->syncWithoutDetaching([
                        $toppingModels[$toppingSlug]->id => ['default_qty' => 1, 'sort_order' => $index]
                    ]);
                }
            }

            // Create recipe
            foreach ($recipe as $ingSlug => $qty) {
                if (isset($ingredientModels[$ingSlug])) {
                    $ingredient = $ingredientModels[$ingSlug];
                    \App\Models\Recipe::firstOrCreate(
                        ['product_id' => $product->id, 'ingredient_id' => $ingredient->id],
                        [
                            'product_id' => $product->id,
                            'ingredient_id' => $ingredient->id,
                            'quantity' => $qty,
                            'unit_id' => $ingredient->unit_id,
                            'notes' => 'Auto-generated from seeder',
                        ]
                    );
                }
            }
        }

        // Create default users
        $users = [
            [
                'employee_id' => 'ADM001',
                'name' => 'Super Admin',
                'email' => 'superadmin@warkop.com',
                'phone' => '081234567890',
                'password' => bcrypt('password123'),
                'pin' => bcrypt('123456'),
                'role' => 'super_admin',
                'is_active' => true,
                'outlet_id' => $outlet->id,
            ],
            [
                'employee_id' => 'MGR001',
                'name' => 'Manager Pusat',
                'email' => 'manager@warkop.com',
                'phone' => '081234567891',
                'password' => bcrypt('password123'),
                'pin' => bcrypt('123456'),
                'role' => 'manager',
                'is_active' => true,
                'outlet_id' => $outlet->id,
            ],
            [
                'employee_id' => 'CSH001',
                'name' => 'Kasir 1',
                'email' => null,
                'phone' => '081234567892',
                'password' => bcrypt('password123'),
                'pin' => bcrypt('123456'),
                'role' => 'kasir',
                'is_active' => true,
                'outlet_id' => $outlet->id,
            ],
            [
                'employee_id' => 'CSH002',
                'name' => 'Kasir 2',
                'email' => null,
                'phone' => '081234567893',
                'password' => bcrypt('password123'),
                'pin' => bcrypt('123456'),
                'role' => 'kasir',
                'is_active' => true,
                'outlet_id' => $outlet->id,
            ],
            [
                'employee_id' => 'BAR001',
                'name' => 'Barista 1',
                'email' => null,
                'phone' => '081234567894',
                'password' => bcrypt('password123'),
                'pin' => bcrypt('123456'),
                'role' => 'barista',
                'is_active' => true,
                'outlet_id' => $outlet->id,
            ],
            [
                'employee_id' => 'BAR002',
                'name' => 'Barista 2',
                'email' => null,
                'phone' => '081234567895',
                'password' => bcrypt('password123'),
                'pin' => bcrypt('123456'),
                'role' => 'barista',
                'is_active' => true,
                'outlet_id' => $outlet->id,
            ],
            [
                'employee_id' => 'COOK001',
                'name' => 'Koki 1',
                'email' => null,
                'phone' => '081234567896',
                'password' => bcrypt('password123'),
                'pin' => bcrypt('123456'),
                'role' => 'koki',
                'is_active' => true,
                'outlet_id' => $outlet->id,
            ],
            [
                'employee_id' => 'WH001',
                'name' => 'Gudang 1',
                'email' => 'gudang@warkop.com',
                'phone' => '081234567897',
                'password' => bcrypt('password123'),
                'pin' => bcrypt('123456'),
                'role' => 'gudang',
                'is_active' => true,
                'outlet_id' => $outlet->id,
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['employee_id' => $userData['employee_id']],
                $userData
            );

            // Assign role using Spatie
            $user->assignRole($userData['role']);
        }

        // Create tables
        for ($i = 1; $i <= 20; $i++) {
            \App\Models\Table::firstOrCreate(
                ['outlet_id' => $outlet->id, 'number' => (string)$i],
                [
                    'outlet_id' => $outlet->id,
                    'number' => (string)$i,
                    'label' => $i <= 10 ? "Meja $i" : "Teras " . ($i - 10),
                    'capacity' => $i <= 10 ? 4 : 2,
                    'status' => 'available',
                    'is_active' => true,
                    'sort_order' => $i,
                ]
            );
        }

        // Create default KDS stations
        \App\Models\KdsStation::firstOrCreate(
            ['outlet_id' => $outlet->id, 'name' => 'Barista'],
            [
                'outlet_id' => $outlet->id,
                'name' => 'Barista',
                'slug' => 'barista',
                'description' => 'Station untuk minuman (kopi, teh, dll)',
                'icon' => 'coffee',
                'color' => '#6F4E37',
                'sound_enabled' => true,
                'sound_url' => '/sounds/barista-notif.mp3',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        \App\Models\KdsStation::firstOrCreate(
            ['outlet_id' => $outlet->id, 'name' => 'Kitchen'],
            [
                'outlet_id' => $outlet->id,
                'name' => 'Kitchen',
                'slug' => 'kitchen',
                'description' => 'Station untuk makanan (nasi, mie, camilan)',
                'icon' => 'utensils',
                'color' => '#E67E22',
                'sound_enabled' => true,
                'sound_url' => '/sounds/kitchen-notif.mp3',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        // Create default printers
        \App\Models\Printer::firstOrCreate(
            ['outlet_id' => $outlet->id, 'name' => 'Printer Kasir'],
            [
                'outlet_id' => $outlet->id,
                'name' => 'Printer Kasir',
                'type' => 'thermal',
                'connection_type' => 'bluetooth',
                'paper_width' => 80,
                'device_address' => null,
                'ip_address' => null,
                'port' => null,
                'is_default' => true,
                'is_active' => true,
                'print_categories' => ['receipt', 'queue_ticket'],
            ]
        );

        \App\Models\Printer::firstOrCreate(
            ['outlet_id' => $outlet->id, 'name' => 'Printer Dapur'],
            [
                'outlet_id' => $outlet->id,
                'name' => 'Printer Dapur',
                'type' => 'thermal',
                'connection_type' => 'network',
                'paper_width' => 58,
                'device_address' => null,
                'ip_address' => '192.168.1.100',
                'port' => 9100,
                'is_default' => false,
                'is_active' => true,
                'print_categories' => ['kitchen_ticket'],
            ]
        );

        \App\Models\Printer::firstOrCreate(
            ['outlet_id' => $outlet->id, 'name' => 'Printer Barista'],
            [
                'outlet_id' => $outlet->id,
                'name' => 'Printer Barista',
                'type' => 'thermal',
                'connection_type' => 'network',
                'paper_width' => 58,
                'device_address' => null,
                'ip_address' => '192.168.1.101',
                'port' => 9100,
                'is_default' => false,
                'is_active' => true,
                'print_categories' => ['barista_ticket'],
            ]
        );

        // Create default tax rate
        \App\Models\TaxRate::firstOrCreate(
            ['outlet_id' => $outlet->id, 'name' => 'PPN 11%'],
            [
                'outlet_id' => $outlet->id,
                'name' => 'PPN 11%',
                'rate' => 0.11,
                'type' => 'percent',
                'is_default' => true,
                'is_active' => true,
            ]
        );

        // Create default rounding rule
        \App\Models\RoundingRule::firstOrCreate(
            ['outlet_id' => $outlet->id, 'name' => 'Bulat 100'],
            [
                'outlet_id' => $outlet->id,
                'name' => 'Bulat 100',
                'rounding_value' => 100,
                'type' => 'nearest',
                'is_default' => true,
                'is_active' => true,
            ]
        );

        // Create default service charge
        \App\Models\Setting::firstOrCreate(
            ['outlet_id' => $outlet->id, 'key' => 'service_charge'],
            [
                'outlet_id' => $outlet->id,
                'key' => 'service_charge',
                'value' => 0.05,
                'type' => 'decimal',
                'description' => 'Service charge 5%',
            ]
        );

        $this->command->info('Default data seeded successfully!');
        $this->command->info('Default login credentials:');
        $this->command->info('  Super Admin: superadmin@warkop.com / password123 (PIN: 123456)');
        $this->command->info('  Manager: manager@warkop.com / password123 (PIN: 123456)');
        $this->command->info('  Kasir: employee_id CSH001 / password123 (PIN: 123456)');
        $this->command->info('  Barista: employee_id BAR001 / password123 (PIN: 123456)');
        $this->command->info('  Koki: employee_id COOK001 / password123 (PIN: 123456)');
        $this->command->info('  Gudang: gudang@warkop.com / password123 (PIN: 123456)');
    }
}