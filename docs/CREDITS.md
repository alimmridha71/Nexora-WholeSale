# Image credits

All catalog and hero photos are bundled in `public/images/` and come from [Unsplash](https://unsplash.com). They are used under the [Unsplash License](https://unsplash.com/license), which allows free commercial and non-commercial use without permission. Attribution is not required, but we credit every photographer here.

No Unsplash+ (paid) images are used. Every photo below was confirmed as a "Free Photo" on its Unsplash page when it was added. The files were resized and converted to WebP (800×600 for products, 1920×960 for the hero).

| File | Used for | Photographer | Source |
|---|---|---|---|
| `images/hero-warehouse.webp` | Home page hero | Lance Chang (@carmendis) | [Unsplash](https://unsplash.com/photos/a-large-warehouse-filled-with-lots-of-shelves-h3pVxOIpnzk) |
| `images/products/blt-m8-100.webp` | Industrial Steel Bolts M8 | Marcel Strauß (@martzzl) | [Unsplash](https://unsplash.com/photos/grey-stainless-steel-bolt-and-screw-lot-FDTEzCJ11fk) |
| `images/products/box-kr-1212.webp` | Recycled Kraft Shipping Boxes | Brandable Box (@brandablebox) | [Unsplash](https://unsplash.com/photos/shallow-focus-photo-of-brown-cardboard-box-8mCsyImZRGY) |
| `images/products/glv-ntr-1000.webp` | Nitrile Gloves | 3DVISU (@3dvisu) | [Unsplash](https://unsplash.com/photos/person-in-blue-gloves-with-white-background-A4MkgsyXt1Q) |
| `images/products/led-pnl-6040.webp` | LED Panel Light 600x600 | Oleksii Tsaryuk (@photobyredder) | [Unsplash](https://unsplash.com/photos/illuminated-square-ceiling-panels-creating-a-geometric-grid-pattern-gvSv72-rbeM) |
| `images/products/cof-arb-1kg.webp` | Organic Arabica Coffee Beans | Tina Guina (@kittinskie) | [Unsplash](https://unsplash.com/photos/shallow-focus-photography-of-coffee-beans-in-sack-obV_LM0KjxY) |
| `images/products/plt-eur-1208.webp` | EUR Wooden Pallets | Lucas van Oort (@switch_dtp_fotografie) | [Unsplash](https://unsplash.com/photos/a-large-stack-of-wooden-pallets-stacked-on-top-of-each-other-tWLgDQCKRYU) |
| `images/products/btl-ss-750.webp` | Stainless Steel Water Bottle | Joan Tran (@joanofarts) | [Unsplash](https://unsplash.com/photos/green-bottle-on-white-table-reEySFadyJQ) |
| `images/products/ppr-a4-80.webp` | A4 Copy Paper | ron dyar (@prolabprints) | [Unsplash](https://unsplash.com/photos/stack-of-papers-V29UWcALNko) |
| `images/products/tee-ctn-blk.webp` | Cotton Crew T-Shirt Blank | Md Salman (@mohammadsalman) | [Unsplash](https://unsplash.com/photos/assorted-color-folded-shirts-on-wooden-panel-tWOz2_EK5EQ) |
| `images/products/mug-crm-12.webp` | White Stoneware Mugs | Tom Crew (@tomcrewceramics) | [Unsplash](https://unsplash.com/photos/white-ceramic-mug-lot-on-white-surface-oHrC8V_XRU4) |
| `images/products/hat-abs-yel.webp` | ABS Safety Hard Hats | Declan Sun (@declansun) | [Unsplash](https://unsplash.com/photos/several-bright-yellow-hard-hats-are-neatly-lined-up-LutB1xxyArA) |
| `images/products/vst-hv-yel.webp` | Hi-Vis Safety Vests | Tanya Paquet (@tanyapaquet) | [Unsplash](https://unsplash.com/photos/hard-hats-and-safety-vests-wjHdeYmI-XU) |
| `images/products/bag-krf-m.webp` | Kraft Paper Shopping Bags | Kelly Sikkema (@kellysikkema) | [Unsplash](https://unsplash.com/photos/beige-eco-bag-1Pgq9ZpIatI) |
| `images/products/tot-ctn-nat.webp` | Natural Cotton Tote Bags | Brando Makes Branding (@brandomakesbranding) | [Unsplash](https://unsplash.com/photos/white-reusable-bag-on-gray-surface-smTDI-z1rlY) |
| `images/products/jar-gls-750.webp` | Glass Storage Jars 750ml | Kier in Sight Archives (@kierinsightarchives) | [Unsplash](https://unsplash.com/photos/empty-glass-jar-with-black-lid-b29z0iltxk4) |
| `images/products/cup-krf-8.webp` | Kraft Paper Hot Cups 8oz | Vlad Deep (@vladdeep) | [Unsplash](https://unsplash.com/photos/several-empty-paper-cups-arranged-on-a-white-surface-Q3EOR8YuTDE) |

## Replacing photos

1. Put an 800×600 WebP in `public/images/products/` named after the lower-case SKU (e.g. `blt-m8-100.webp`).
2. Set the product's `image` column to `images/products/<file>.webp` (the `ProductSeeder` does this automatically for the demo catalog).
3. Add a row to this file with the photographer, source and license.

Photos uploaded by admins are stored on the `public` disk instead and are not covered by this file.
