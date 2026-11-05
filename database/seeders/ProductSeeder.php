<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Curated demo catalog with realistic tiered pricing and real product photos
 * (bundled in public/images/products, credits in docs/CREDITS.md).
 * Idempotent: products are matched by SKU and their tiers are rebuilt.
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // [name, sku, description, base price, stock, [[min, max, unit], ...]]
        $catalog = [
            ['Industrial Steel Bolts M8 (Box of 100)', 'BLT-M8-100', 'Zinc-plated grade 8.8 hex bolts for structural assembly.', 18.50, 12000,
                [[10, 49, 17.25], [50, 199, 15.90], [200, null, 14.20]]],
            ['Recycled Kraft Shipping Boxes 12x12x12', 'BOX-KR-1212', 'Double-wall corrugated boxes, 100% recycled fiber.', 1.85, 85000,
                [[100, 999, 1.62], [1000, 4999, 1.41], [5000, null, 1.19]]],
            ['Nitrile Gloves, Powder-Free (Case of 1000)', 'GLV-NTR-1000', 'Food-safe blue nitrile gloves, size L.', 64.00, 3400,
                [[5, 19, 59.50], [20, 99, 55.00], [100, null, 49.75]]],
            ['LED Panel Light 600x600 40W', 'LED-PNL-6040', '4000K neutral white, 4400 lm, flicker-free driver.', 27.90, 5200,
                [[20, 99, 25.40], [100, 499, 22.80], [500, null, 20.10]]],
            ['Organic Arabica Coffee Beans 1kg', 'COF-ARB-1KG', 'Single-origin Colombian, medium roast, whole bean.', 21.00, 7800,
                [[24, 119, 19.20], [120, 599, 17.60], [600, null, 15.90]]],
            ['EUR Wooden Pallets 1200x800 (Heat-Treated)', 'PLT-EUR-1208', 'ISPM-15 heat-treated softwood pallets, 1,500 kg dynamic load.', 12.40, 15000,
                [[36, 143, 11.30], [144, 575, 10.25], [576, null, 9.10]]],
            ['Stainless Steel Water Bottle 750ml', 'BTL-SS-750', 'Vacuum-insulated, BPA-free, laser-engravable.', 8.95, 22000,
                [[50, 249, 8.20], [250, 999, 7.45], [1000, null, 6.60]]],
            ['A4 Copy Paper 80gsm (Box of 5 Reams)', 'PPR-A4-80', 'FSC-certified bright white multipurpose paper.', 24.75, 9600,
                [[10, 49, 23.10], [50, 249, 21.40], [250, null, 19.80]]],
            ['Cotton Crew T-Shirt Blank, Assorted', 'TEE-CTN-BLK', '180gsm ring-spun cotton, sizes S-XXL.', 3.60, 41000,
                [[100, 499, 3.25], [500, 2499, 2.90], [2500, null, 2.55]]],
            ['White Stoneware Mugs 350ml (Case of 12)', 'MUG-CRM-12', 'Speckled glaze stoneware, dishwasher and microwave safe.', 29.99, 60,
                [[12, 47, 27.50], [48, null, 24.99]]],
            ['ABS Safety Hard Hats, Yellow (Case of 20)', 'HAT-ABS-YEL', 'EN 397 ABS shell, 6-point ratchet harness, adjustable 53-63 cm.', 89.00, 1800,
                [[5, 19, 84.50], [20, 99, 79.00], [100, null, 72.50]]],
            ['Hi-Vis Safety Vests, Yellow (Pack of 10)', 'VST-HV-YEL', 'EN ISO 20471 Class 2, two reflective bands, ID pocket, one size.', 24.50, 6400,
                [[10, 49, 22.90], [50, 199, 21.10], [200, null, 19.40]]],
            ['Kraft Paper Shopping Bags with Handles, Medium', 'BAG-KRF-M', '100 gsm recycled kraft, twisted paper handles, 32x12x41 cm.', 0.42, 120000,
                [[500, 2499, 0.37], [2500, 9999, 0.32], [10000, null, 0.27]]],
            ['Natural Cotton Tote Bags 38x42cm, Blank', 'TOT-CTN-NAT', '140 gsm unbleached cotton, long handles, ready for printing.', 1.95, 30000,
                [[100, 499, 1.75], [500, 1999, 1.55], [2000, null, 1.35]]],
            ['Glass Storage Jars 750ml with Metal Lid (Case of 12)', 'JAR-GLS-750', 'Food-grade glass, airtight screw lid, dishwasher safe.', 18.90, 2600,
                [[10, 49, 17.40], [50, 199, 16.10], [200, null, 14.80]]],
            ['Kraft Paper Hot Cups 8oz (Case of 1000)', 'CUP-KRF-8', 'Single-wall kraft cups for hot drinks, PLA lined, fits 80 mm lids.', 46.00, 4100,
                [[5, 19, 43.20], [20, 99, 40.50], [100, null, 37.90]]],
        ];

        foreach ($catalog as [$name, $sku, $description, $price, $stock, $tiers]) {
            $product = Product::updateOrCreate(
                ['sku' => $sku],
                [
                    'name' => $name,
                    'description' => $description,
                    'base_price' => $price,
                    'stock' => $stock,
                    'image' => Product::BUNDLED_IMAGE_PREFIX.'products/'.strtolower($sku).'.webp',
                ],
            );

            $product->bulkPrices()->delete();

            foreach ($tiers as [$min, $max, $unit]) {
                $product->bulkPrices()->create([
                    'min_quantity' => $min,
                    'max_quantity' => $max,
                    'price_per_unit' => $unit,
                ]);
            }
        }
    }
}
