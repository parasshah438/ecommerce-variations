# 📦 Bulk Product Import — Complete Guide

Everything you need to know about the **Admin Bulk Product Import** feature:
how it works, how to use it, file format, sample files, queue commands, and troubleshooting.

---

## 1. Overview

The import lets you add **hundreds or thousands of products at once** from a CSV, TSV, or Excel file instead of creating them one-by-one in the admin.

It is **queue-based**: uploads are validated first, then imported in the background by Laravel's queue worker. Your browser is never blocked, and you can leave the page while the import runs.

### Feature highlights

| Capability | Details |
|---|---|
| File formats | `.csv`, `.txt` (comma or tab), `.xlsx`, `.xls` |
| Max file size | 20 MB |
| Batch size | Files are split into segments of **500 rows** |
| Validation | Runs before import; invalid rows are **skipped** and recorded |
| Error report | Downloadable CSV of validation + import errors |
| Progress | Live progress bar on the import page, auto-refreshes every 8 s |
| Products types | Simple products (single SKU) and products with variations (Color/Size/etc.) |
| Images | Auto-downloads from `image_urls` / `cover_image` URL columns |
| Sample files | Downloadable pre-filled templates for every format |
| Dependencies | Excel support requires `phpoffice/phpspreadsheet` (already installed) |

---

## 2. Files & Routes

### Routes (`routes/admin.php`)

All routes are prefixed with `/admin` and require login.

| Method | URI | Route name | Purpose |
|---|---|---|---|
| GET | `/admin/products/import` | `admin.products.import.form` | Show upload form + recent batches |
| POST | `/admin/products/import/preview` | `admin.products.import.preview` | Parse file & show validation preview |
| POST | `/admin/products/import/execute` | `admin.products.import.execute` | Confirm & queue the import |
| GET | `/admin/products/import/log/{batch}` | `admin.products.import.log` | Download error report CSV |
| GET | `/admin/products/import/sample/{format}` | `admin.products.import.sample` | Download a pre-filled sample file (`csv`, `txt`, `xlsx`, `xls`) |

> ⚠️ These routes are declared **before** the parameterized `/{product}` routes in `admin.php`, so `/admin/products/import` is never captured by the product `show` route.

### Main code files

| File | Role |
|---|---|
| `app/Http/Controllers/Admin/ProductImportController.php` | Upload preview, execute, sample downloads, error download |
| `app/Services/ProductImportService.php` | Parsing + delimiter detection, header aliasing, validation, data building |
| `app/Jobs/ParseAndValidateImportJob.php` | Queued: validates rows, then dispatches processing |
| `app/Jobs/ProcessProductImportJob.php` | Queued: actually creates products/variations/stock/images |
| `app/Models/ProductImportBatch.php` | Batch tracking model (status, rows, errors) |
| `database/migrations/2026_08_04_000001_create_product_import_batches_table.php` | Table for batches |
| `resources/views/admin/products/import.blade.php` | Upload + progress page |
| `resources/views/admin/products/import-preview.blade.php` | Preview/validation page |

---

## 3. The Full Import Process (step by step)

```
1. User uploads file        →  POST admin.products.import.preview
2. File parsed + validated  →  shows preview table (first 200 rows)
3. User confirms            →  POST admin.products.import.execute
4. File split into 500-row segments  →  one ProductImportBatch per segment
5. ParseAndValidateImportJob (queued per batch)
       status: uploaded → validating → importing (or failed)
       validates every row, stores errors, keeps only valid rows
       dispatches ProcessProductImportJob in 50-row chunks
6. ProcessProductImportJob (queued per chunk of 50)
       for each row: create product → attach cover/gallery images → create variation(s) + stock
       increments processed_rows / failed_rows
7. Batch marked completed  →  progress bar = 100%
```

### Step details

**Step 1 — Upload**
- File is stored to `storage/app/private/product-imports/` — the **`local`** disk root is `storage/app/private` in Laravel 12 (not `storage/app/local`). You can verify the real path with:
  ```bash
  php artisan tinker --execute="echo storage_path('app/private/product-imports');"
  ```
- The service reads it and normalizes headers (e.g. `Product Name` → `name`, `Selling Price` → `price`).
- The delimiter is detected automatically (tab vs comma), so `.txt` / `.tsv` files work either way.
- Required columns checked: **`name`, `description`, `price`, `weight`**. If any are missing, upload is rejected.

**Step 2 — Preview**
- Each row is validated (name/description present, price ≥ 0, weight ≥ 1, numeric dimensions, valid variation syntax, stock ≥ 0).
- The preview page shows Total / Valid / Invalid counts and the first 200 rows. Invalid rows are shown in red with the reason on hover.

**Step 3 — Confirm**
- The "Start Import" button posts the stored file path.
- The file is re-read and split into segments of **500 rows**. Each segment becomes its own `product_import_batches` record.

**Step 4–6 — Queued processing**
- `ParseAndValidateImportJob` marks the batch `validating`, validates rows against the same rules, stores `validation_errors`, then dispatches `ProcessProductImportJob` in chunks of **50 valid rows**.
- `ProcessProductImportJob` does, per row, inside a DB transaction:
  1. Resolve `category` & `brand` **by name** (existing records only — they are **not** auto-created).
  2. Create the `products` row.
  3. Download & set `cover_image` (optional).
  4. Download gallery `image_urls` (optional).
  5. Create either one simple variation + stock, or one variation + stock per variation group.
- Failed rows are rolled back individually and recorded in `import_errors` (row + message), so one bad row never blocks the rest.

**Step 7 — Completion**
- When `processed_rows >= total_rows`, status becomes `completed`.
- Category/product list caches are cleared automatically.

---

## 4. CSV File Format

### Row 1 is the header. Every product is one row.

### Required columns

```csv
name,description,price,weight
Premium Cotton T-Shirt,Premium 100% cotton fabric,999,200
Denim Jacket,Classic blue denim,2499,800
```

- `name` — product title (max 255 chars)
- `description` — full description (required)
- `price` — selling price in ₹ (number, ≥ 0)
- `weight` — weight in grams (number, ≥ 1)

### Optional columns

| Column | Example | Notes |
|---|---|---|
| `short_description` | Soft cotton tee | Short blurb |
| `long_description` | Detailed fabric & care... | Extended detail section |
| `category` | Fashion | Matched **by name**; must already exist |
| `brand` | Nike | Matched **by name**; must already exist |
| `mrp` | 1299 | Defaults to price if empty |
| `sku` | TS-001 | Auto-generated if empty |
| `stock` | 50 | Simple-product stock |
| `length` / `width` / `height` | 30 / 20 / 2 | Shipping dimensions in cm |
| `variations` | `Color:Red,Size:M;Color:Blue,Size:L` | See variation format below |
| `variation_prices` | `1049;1049` | One per variation, semicolon-separated |
| `variation_stock` | `10;15` | One per variation, semicolon-separated |
| `variation_sku` | `TS-R-M;TS-B-L` | One per variation, semicolon-separated |
| `variation_weight` | `200;210` | One per variation, semicolon-separated |
| `image_urls` | `https://…/a.jpg\|https://…/b.jpg` | Pipe (`\|`) or `;` separated URLs, downloaded on import |
| `cover_image` | `https://…/cover.jpg` | Dedicated cover image URL |
| `hsn_code` | 61091000 | GST HSN |
| `featured` | yes | yes/no; only `yes/true/1/y` enables |
| `active` | no | yes/no; **empty defaults to yes** |
| `meta_title` | Cotton T-Shirt | SEO |
| `meta_description` | Buy premium cotton tees | SEO |
| `meta_keywords` | cotton,tee,tshirt | SEO |
| `video_url` | https://youtu.be/xyz | Product video |
| `country_of_origin` | India | Defaults to existing value |
| `manufacturer` | NS Kurti Pvt Ltd | Manufacturer name |

### Friendly header aliases (all accepted)

`Product Name`, `Product Title`, `Title`, `Selling Price`, `Original Price`, `Maximum Retail Price`, `Weight (g)`, `Grams`, `Stock Quantity`, `Quantity`, `HSN`, `Is Featured`, `Status`, `Variation Attributes`, `Variation Prices`, `Image Url(s)`, `Cover Image`, `Long Description`, `Meta Title/Description/Keywords`, `Country Of Origin`, `Manufacturer`, etc. — the service normalizes them automatically.

### Variation format

Semicolon = one variation. Comma = attribute pair. Attribute names **must already exist** (matched by name). Values must exist under that attribute (matched by value or code; not auto-created).

```csv
variations,variation_prices,variation_stock,variation_sku
Color:Red,Size:M;Color:Blue,Size:L,1049;1099,10;15,TS-R-M;TS-B-L
```

Single variation can also be written pipe-style: `Color:Red|Size:XL`.

---

## 5. Batch Statuses

Tracked in the `product_import_batches` table and shown on the import page.

| Status | Meaning |
|---|---|
| `uploaded` | Batch record created, waiting for the validate job |
| `validating` | `ParseAndValidateImportJob` is checking rows |
| `importing` | Validation done, `ProcessProductImportJob` chunks running |
| `completed` | All valid rows processed |
| `failed` | File could not be parsed or required columns missing |

`processed_rows`, `failed_rows`, `valid_rows`, `invalid_rows`, and `progress_percentage` update live. The page auto-refreshes every 8 seconds while an import is `validating` or `importing`.

---

## 6. Error Reporting

- **Invalid rows** (validation): skipped, message per row in `validation_errors`.
- **Failed rows** (runtime error during import, e.g. DB error): recorded per row in `import_errors`. The row's transaction is rolled back; other rows continue.
- The **Errors** link on the import page downloads a CSV:
  `Row,Type,Message` — e.g. `"5","Validation","Price must be a valid non-negative number."`

---

## 7. All Commands & Management

> Windows (WAMP) note: do **not** use `&&` chaining in PowerShell. Use `;` or run commands separately.

### Setup / one-time

```bash
# 1. Run migrations (creates product_import_batches table)
php artisan migrate

# 2. Confirm Excel support package is installed
#    (already in composer.json: phpoffice/phpspreadsheet ^5.9)
composer require phpoffice/phpspreadsheet

# 3. (Optional) verify the table
php artisan db:table product_import_batches
```

### Check routes

```bash
php artisan route:list --path=admin/products/import
```

Should show 5 routes:
`import.form`, `import.preview`, `import.execute`, `import.log`, `import.sample`.

### Run the queue worker (REQUIRED for imports to process)

The queue connection is `database` (see `.env`: `QUEUE_CONNECTION=database`), so imports sit in the `jobs` table until a worker picks them up.

```bash
# In a separate terminal — keep it running while importing:
php artisan queue:work

# Or auto-reload code changes (dev):
php artisan queue:listen

# Process specific queues / one job only:
php artisan queue:work --queue=default --tries=1 --timeout=120

# Run all queued jobs once and exit (handy for cron / one-off):
php artisan queue:work --once

# Failures (jobs are marked failed automatically):
php artisan queue:failed
php artisan queue:retry all

# Check pending jobs count from the DB
SELECT COUNT(*) FROM jobs;
```

> Without a running worker, batches stay stuck at `uploaded` / `validating`. This is the #1 cause of "import not doing anything".

### Monitor logs

```bash
# Import jobs log to:
storage/logs/laravel.log
tail -f storage/logs/laravel.log      # (Linux/macOS)
Get-Content storage/logs/laravel.log -Wait   # (Windows PowerShell)
```

### Clear caches after manual intervention

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Production deployment

```bash
php artisan config:cache
php artisan route:cache
php artisan migrate --force
```

### Storage cleanup

Uploaded files & segments live in `storage/app/private/product-imports/…` (the `local` disk). Old segment files can be deleted manually; batch records (with error JSON) are small and safe to keep.

---

## 8. Sample Files (download & use)

The Template Guide card on the import page has four **Download Sample** buttons —
one for each supported file format. Each generated file contains **4 rows**:

| Row | Purpose |
|---|---|
| 1 | A **simple product** with most optional fields filled (`mrp`, `sku`, `stock`, `category`, `brand`, `hsn_code`, SEO, etc.) |
| 2 | A product **with variations** (`Color:Red,Size:M;Color:Blue,Size:L` + `variation_prices` / `variation_stock` / `variation_sku` / `variation_weight`) |
| 3 | A **minimal row** — only the 4 required columns |
| 4 | An **intentionally invalid row** (no name, bad price, weight 0) — import it as-is to see it flagged in the preview and error report |

### How to use a sample file

1. Click a **Download Sample** button (`.csv`, `.txt`, `.xlsx`, or `.xls`).
2. Open it in Excel / Notepad, fill in or delete the rows, keep the header row.
3. Upload it through the **Upload File** form and click **Preview & Validate** — row 4 will show as Invalid (remove it before real imports or leave it to test error reporting).
4. Click **Start Import**.

### Direct URLs

```
/admin/products/import/sample/csv
/admin/products/import/sample/txt      (tab-separated)
/admin/products/import/sample/xlsx
/admin/products/import/sample/xls
```

The delimiters are detected automatically on upload, so `.txt` files can be tab- **or** comma-separated, and `.tsv` files work too.

---

## 9. Using the Import (UI walkthrough)

1. Log in to the admin.
2. Go to **Products → Bulk Import** (or the **Bulk Import** button on the Products list page).
3. Choose your `.csv` / `.txt` / `.xlsx` file (max 20 MB) — or download a sample first and fill it in.
4. Click **Preview & Validate**.
5. Review the summary (Total / Valid / Invalid) and the row preview.
6. Click **Start Import (N valid)**.
7. You'll be redirected to the import page — you'll see the new batch with a striped progress bar.
8. Refresh (or wait for auto-refresh) until status = `completed`.
9. If any rows failed, click the red **Errors** link to download the error CSV and fix those rows in your file.

---

## 10. Troubleshooting

| Symptom | Likely cause / fix |
|---|---|
| Import stuck at `uploaded` / `validating` | Queue worker not running. Start `php artisan queue:work`. |
| `File is missing required columns` | Header row must include `name, description, price, weight`. Check for typos / extra spaces / BOM (UTF-8 BOM is stripped automatically). |
| "Excel support requires phpoffice/phpspreadsheet" | `composer require phpoffice/phpspreadsheet` |
| Rows skipped as invalid | Preview shows why (hover the red badge). Typical: empty name/description, non-numeric price/weight, weight < 1, bad variation syntax. |
| Category/brand not applied | They are matched **by name** only and are **not auto-created**. Create them first in the admin. |
| Variations not created | Attribute or attribute-value names must already exist (matched by name/code). |
| Images missing | `image_urls` / `cover_image` must be full `http(s)` URLs. Failed downloads are logged and skipped, they don't fail the row. |
| Batch status `failed` | Open `storage/logs/laravel.log` — usually unparseable file or missing required columns. |
| Duplicate SKU error on import | `product_variations.sku` is unique. Remove duplicates from the file or leave `sku` empty to auto-generate. |
| Site shows old products after import | Import clears `featured_products`, `latest_products`, and affected `category_products_*` caches; otherwise run `php artisan cache:clear`. |

---

## 11. Key Behaviors (good to know)

- **Empty `active` cell = active.** Only explicit `no/false/0/inactive` disables.
- **Feature flag:** only `yes/true/1/y` enables `featured`.
- **MRP defaults** to `price` when blank.
- **SKUs auto-generate** when left blank (`PRODUCTNAME-SIMPLE-XXXXXX` for simple, name + attribute codes + random for variations). For products **with variations**: fill `variation_sku` per variation to set SKUs exactly, or fill `sku` to use it as the base (e.g. `SKU-B-123` → `SKUB123-RED-XXXX`, `SKUB123-BLU-XXXX`).
- **Slugs auto-generate** as `product-name-xxxxxxxx` (random suffix avoids collisions).
- **Volumetric weight / default weight** are handled later by the product's existing helpers — the import stores raw weight + dimensions.
- **No update mode.** The importer always creates new products. To update existing products, you'll need to edit them in the admin.

---

## 12. End-to-end smoke test (optional)

A quick way to confirm the pipeline works locally:

1. Create `test-import.csv`:
   ```csv
   name,description,price,weight,stock,category,variations,variation_prices
   Test Shirt,Premium cotton,999,200,10,Fashion,Color:Red,Size:M;Color:Blue,Size:L,1049;1099
   ```
2. Start a worker: `php artisan queue:work`
3. Upload the file at `/admin/products/import`, preview, start.
4. Watch the batch status become `completed`, then check the storefront / product list for the new product.

---

_Related: `SALE_SYSTEM_GUIDE.md`, `PRODUCT_SEARCH_DYNAMIC_FILTERS.md`, `ADMIN_WEIGHT_MANAGEMENT_GUIDE.md`_
