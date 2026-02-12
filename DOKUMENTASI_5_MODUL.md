# DOKUMENTASI 5 MODUL UTAMA - ELOGSHEET LARAVEL

## Ringkasan Ke-5 Modul
Sistem logsheet terdiri dari 5 modul pelaporan utama yang semuanya mengikuti pola MVC (Model-View-Controller):

1. **Daily Storage Tank Analytical (F/QCO-001)**
2. **Daily Quality Composite Fractionation (F/QCO-003)**
3. **Analytical Result of Incoming Material by Vessel (F-QOC-09)**
4. **Analytical Result of Incoming Material by Truck (F-QOC-10)**
5. **Analytical Result of Out Going Shipment Product by Truck (F-QOC-13)**

---

## 1. DAILY STORAGE TANK ANALYTICAL (F/QCO-001)

### Deskripsi
Laporan analisis harian dari tangki penyimpanan mencakup analisis kualitas minyak di dalam tangki penyimpanan seperti FFA, Moisture, Lovibond Color, IV, PV, dll.

### Database
**Tabel:** `t_daily_storage_tank_analytical_report`

### Model
**File:** [app/Models/LSDailyStorageTankAnalytical.php](app/Models/LSDailyStorageTankAnalytical.php)

**Primary Key:** `id` (integer, auto-increment)
**Timestamps:** false

**Atribut Utama:**
```
Informasi Umum:
- transaction_date (datetime) - Tanggal analisis
- posting_date (datetime) - Tanggal posting
- company (string)
- plant (string)

Informasi Tangki:
- tank_no (string) - Nomor tangki
- oil_type (string) - Jenis minyak
- kapasitas_tanki (float)
- quantity (float) - Kuantitas dalam tangki
- empty_space (float) - Ruang kosong
- suhu (float) - Suhu minyak

Quality Parameters (qp_):
- qp_ffa (float) - Free Fatty Acid
- qp_moisture (float)
- qp_lovibond_color_r (float)
- qp_lovibond_color_y (float)
- qp_iv (float) - Iodine Value
- qp_pv (float) - Peroxide Value
- qp_slip_melting_point (float)
- qp_cloud_point (float)
- qp_anv (float) - Acid Number Value
- qp_beta_carotene (float)
- qp_p (float)
- qp_dobi (float) - Deterioration of Oil Bleaching Index
- qp_totox (float)
- qp_odor (string)

Validasi & Approval:
- prepared_by (string) - Siapa yang menyiapkan (LEAD/LEAD_QC)
- prepared_date (datetime)
- prepared_status (string) - Approved/Rejected/Pending
- prepared_status_remarks (string)
- approved_by (string) - Siapa yang approve (MGR/MGR_PROD/ADM)
- approved_date (datetime)
- approved_status (string) - Approved/Rejected/Pending
- approved_status_remarks (string)

Metadata:
- flag (char) - Status flag
- entry_by (string)
- entry_date (datetime)
- form_no (string) - F/QCO-001
- date_issued (datetime)
- revision_no (integer)
- revision_date (datetime)
```

### Controller
**File:** [app/Http/Controllers/RptDailyStorageTankAnalyticalController.php](app/Http/Controllers/RptDailyStorageTankAnalyticalController.php)

**Methods Utama:**

| Method | Deskripsi |
|--------|-----------|
| `index(Request $request)` | List laporan sesuai tanggal filter |
| `show($id)` | Tampilkan detail 1 laporan |
| `approveReport($id)` | Approve laporan (LEAD/MGR) |
| `rejectReport(Request $request, $id)` | Reject laporan dengan remark |
| `exportLayoutPreview(Request $request)` | Preview layout export |
| `exportPdf(Request $request)` | Export ke PDF |

**Approval Flow:**
```
LEAD/LEAD_QC:
  - Update prepared_status = 'Approved' atau 'Rejected'
  - Update prepared_date = now()
  - Update prepared_by = auth()->user()->username

MGR/MGR_PROD/ADM:
  - Update approved_status = 'Approved' atau 'Rejected'
  - Update approved_date = now()
  - Update approved_by = auth()->user()->username
```

### Views
**Directory:** `resources/views/rpt_daily_storage_tank_analytical/`

| File | Deskripsi |
|------|-----------|
| `index.blade.php` | List dengan filter tanggal |
| `show.blade.php` | Detail laporan lengkap |
| `preview.blade.php` | Preview untuk layout/PDF |
| `_table.blade.php` | Template tabel data |

**UI Features:**
- Filter by tanggal
- Tombol: View Layout, Download PDF
- List data dalam tabel dengan status approval
- Approve/Reject inline buttons dengan modal remark

### Routes
```php
Route::prefix('daily-storage-tank-analytical')
  ->name('daily-storage-tank-analytical.')
  ->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/{id}/approve-report', 'approveReport')->name('approveReport');
    Route::post('/{id}/reject-report', 'rejectReport')->name('rejectReport');
    Route::get('/{id}', 'show')->name('show');
    Route::get('/export/view', 'exportLayoutPreview')->name('export.view');
    Route::get('/export/pdf', 'exportPdf')->name('export.pdf');
  });
```

---

## 2. DAILY QUALITY COMPOSITE FRACTIONATION (F/QCO-003)

### Deskripsi
Laporan harian dari proses fraksinasi yang mencakup parameter kualitas 3 output produk (Raw Material, Finished Good, By Product) dengan pembagian by Work Center (FRAC-01 atau FRAC-02).

### Database
**Tabel:** `t_daily_quality_composite_fractionation`

### Model
**File:** [app/Models/LSDailyQualityCompositeFractionation.php](app/Models/LSDailyQualityCompositeFractionation.php)

**Primary Key:** `id` (string)
**Timestamps:** false

**Atribut Utama:**
```
Informasi Proses:
- transaction_date (datetime)
- time (string) - Jam pengukuran
- work_center (string) - FRAC-01 atau FRAC-02
- crystalizer (string) - Kristalizer mana

Raw Material Parameters (rm_):
- rm_mni (float) - M&I
- rm_iv (float)
- rm_color_r (float)
- rm_color_y (float)
- rm_color_w (float)
- rm_color_b (float)

Finished Good Parameters (fg_):
- fg_ffa (float)
- fg_mni (float)
- fg_iv (float)
- fg_color_r (float)
- fg_color_y (float)
- fg_color_w (float)
- fg_color_b (float)
- fg_cp (float) - Cloud Point
- fg_clarity (string)
- fg_to_tank (string)

By Product Parameters (bp_):
- bp_ffa (float)
- bp_mni (float)
- bp_iv (float)
- bp_pv (float)
- bp_color_r (float)
- bp_color_y (float)
- bp_color_w (float)
- bp_color_b (float)
- bp_to_tank (string)

Validasi & Approval:
- prepared_by (string)
- prepared_date (datetime)
- prepared_status (string)
- prepared_status_remarks (string)
- checked_by (string) - Siapa yang check (MGR/MGR_QC/ADM)
- checked_date (datetime)
- checked_status (string)
- checked_status_remarks (string)

Metadata:
- flag (char)
- entry_by (string)
- entry_date (datetime)
- form_no (string) - F/QCO-003
- date_issued (datetime)
- revision_no (integer)
- revision_date (datetime)
```

### Controller
**File:** [app/Http/Controllers/RptDailyQualityCompositeFractionation.php](app/Http/Controllers/RptDailyQualityCompositeFractionation.php)

**Methods Utama:**

| Method | Deskripsi |
|--------|-----------|
| `index(Request $request)` | List dengan filter tanggal, jam, work_center |
| `show($id)` | Detail 1 laporan |
| `approveReport($id)` | Approve (LEAD/LEAD_QC) |
| `rejectReport(Request $request, $id)` | Reject dengan remark |
| `exportLayoutPreview(Request $request)` | Preview layout |
| `exportPdf(Request $request)` | Export PDF |

**Filter Options:**
- `filter_tanggal` - Tanggal laporan
- `filter_jam` - Waktu pengukuran
- `filter_work_center` - FRAC-01 atau FRAC-02

**Approval Flow:**
```
LEAD/LEAD_QC:
  - Update prepared_status

MGR/MGR_QC/ADM:
  - Update checked_status
  - Note: Menggunakan 'checked_' bukan 'approved_'
```

### Views
**Directory:** `resources/views/rpt_daily_quality_composite_fractionation/`

| File | Deskripsi |
|------|-----------|
| `index.blade.php` | List dengan 3 filter (tanggal, jam, work_center) |
| `show.blade.php` | Detail lengkap |
| `preview.blade.php` | Preview layout |
| `_table.blade.php` | Tabel dengan kolom-kolom produk |

**UI Features:**
- Filter by tanggal, jam, dan work_center dengan dropdown
- Work center labels: FRAC-01 (500 MT) dan FRAC-02 (400 MT)
- Tabel dengan grouping by work_center jika filter kosong
- Status badges untuk approval

### Routes
```php
Route::prefix('daily-quality-composite-fractionation')
  ->name('daily-quality-composite-fractionation.')
  ->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/{id}/approve-report', 'approveReport')->name('approveReport');
    Route::post('/{id}/reject-report', 'rejectReport')->name('rejectReport');
    Route::get('/{id}', 'show')->name('show');
    Route::get('/export/view', 'exportLayoutPreview')->name('export.view');
    Route::get('/export/pdf', 'exportPdf')->name('export.pdf');
  });
```

---

## 3. ANALYTICAL RESULT OF INCOMING MATERIAL BY VESSEL (F-QOC-09)

### Deskripsi
Laporan hasil analisis material yang masuk dari kapal dengan detail per "Palka" (kompartemen kapal). Terdapat 3 palka: Starboard (S), Center (C), Port (P). Setiap palka memiliki parameter: FFA, IV, DOBI, M&I.

### Database
**Tabel Header:** `t_analytical_result_incoming_material_by_vessel`
**Tabel Detail:** `t_analytical_result_incoming_material_by_vessel_detail`

### Model
**File:** [app/Models/ARIMByVesselHeader.php](app/Models/ARIMByVesselHeader.php)

**Primary Key:** `id` (string)

**Header Atribut:**
```
Informasi Umum:
- id (string) - Primary key
- company (string)
- plant (string)
- transaction_date (datetime)
- material (string)
- arrival (datetime) - Tanggal kedatangan kapal
- quantity (float)
- supplier (string)
- ship_name (string) - Nama kapal

Summary Parameters:
- ffa (float) - Summary FFA
- mni (float) - Summary M&I
- dobi (float) - Summary DOBI
- others (string)

Hasil Analisa Komposit (composite analysis):
- hasil_analisa_ffa (float)
- hasil_analisa_iv (float)
- hasil_analisa_moisture (float)
- hasil_analisa_dobi (float)
- hasil_analisa_pv (float)
- hasil_analisa_anv (float)

Kontrak:
- contract_do_nomor (string)

Validasi & Approval:
- prepared_by (string) - LEAD/LEAD_QC
- prepared_date (datetime)
- prepared_status (string)
- prepared_status_remarks (string)
- approved_by (string) - MGR/MGR_QC/ADM
- approved_date (datetime)
- approved_status (string)
- approved_status_remarks (string)

Metadata:
- flag (char)
- entry_by (string)
- entry_date (datetime)
- form_no (string) - F-QOC-09
- date_issued (datetime)
- revision_no (integer)
- revision_date (datetime)
```

**Detail Atribut:**
```
Per Palka (Starboard, Center, Port):
Palka S:
- palka_s_no (integer)
- palka_s_ffa (float)
- palka_s_iv (float)
- palka_s_dobi (float)
- palka_s_mni (float)

Palka C:
- palka_c_no (integer)
- palka_c_ffa (float)
- palka_c_iv (float)
- palka_c_dobi (float)
- palka_c_mni (float)

Palka P:
- palka_p_no (integer)
- palka_p_ffa (float)
- palka_p_iv (float)
- palka_p_dobi (float)
- palka_p_mni (float)
```

**Relationship:**
```php
Header::hasMany(Detail::class, 'id_hdr', 'id')
```

### Controller
**File:** [app/Http/Controllers/ARIMByVesselController.php](app/Http/Controllers/ARIMByVesselController.php)

**Key Methods:**
- `index()` - List laporan
- `create()` - Buat laporan baru (API)
- `show($id)` - Detail laporan
- `update($id)` - Update laporan
- `approveReport()` - Approve
- `rejectReport()` - Reject
- `exportPdf()` - Export ke PDF

**Approval Role Logic:**
```
Role decisioning:
- LEAD, LEAD_QC → prepared
- MGR, MGR_QC, ADM → approved
```

### Views
**Directory:** `resources/views/rpt_analytical_result_incoming_material_by_vessel/`

| File | Deskripsi |
|------|-----------|
| `index.blade.php` | List laporan dengan filter tanggal |
| `show.blade.php` | Detail dengan tabel 3 palka |
| `preview_layout.blade.php` | Preview untuk print/PDF |

**Tabel Layout:**
```
Header Info: Ship Name, Material, Quantity, Arrival Date, etc.

3-Column Table Structure:
├─ Palka S (Starboard)
│  ├─ Palka No
│  ├─ FFA
│  ├─ IV
│  ├─ DOBI
│  └─ M&I
├─ Palka C (Center)
│  └─ [Same columns]
└─ Palka P (Port)
   └─ [Same columns]

Bottom Section:
├─ Hasil Analisa Komposit (composite analysis results)
└─ Signature fields
```

---

## 4. ANALYTICAL RESULT OF INCOMING MATERIAL BY TRUCK (F-QOC-10)

### Deskripsi
Laporan hasil analisis material yang masuk dari truck. Lebih sederhana dari vessel karena tidak ada pembagian palka, hanya analisis per sampel truck dengan multiple sampling points.

### Database
**Tabel Header:** `t_analytical_result_incoming_material_by_truck`
**Tabel Detail:** `t_analytical_result_incoming_material_by_truck_detail`

### Model
**File:** [app/Models/ARIMByTruckHeader.php](app/Models/ARIMByTruckHeader.php)

**Primary Key:** `id` (string)

**Header Atribut:**
```
Informasi Umum:
- id (string)
- company (string)
- plant (string)
- transaction_date (datetime)
- material (string)
- arrival_date (datetime)
- contract_do (string)
- supplier (string)
- vessel_vehicle (string) - Nomor polisi truck

Summary Parameters:
- ss_ffa (float) - Sample Summary FFA
- ss_mni (float) - Sample Summary M&I
- ss_others (string)

Validasi & Approval:
- prepared_by (string)
- prepared_date (datetime)
- prepared_status (string)
- prepared_status_remarks (string)
- approved_by (string)
- approved_date (datetime)
- approved_status (string)
- approved_status_remarks (string)

Metadata:
- flag (char)
- entry_by (string)
- entry_date (datetime)
- form_no (string) - F-QOC-10
- date_issued (datetime)
- revision_no (integer)
- revision_date (datetime)
```

**Detail Atribut (per sampling):**
```
Informasi Sampel:
- no (integer) - Sample number
- sampling_date (date)
- police_no (string) - Truck license plate

Parameter Analisis per Sampel:
- p_ffa (float)
- p_moisture (float)
- p_iv (float)
- p_dobi (float)
- p_pv (float)
- p_color_r (float)
- p_color_y (float)
- analis (string) - Nama analis
- remarks (string)
```

**Relationship:**
```php
Header::hasMany(Detail::class, 'id_hdr', 'id')
```

### Controller
**File:** [app/Http/Controllers/ARIMByTruckController.php](app/Http/Controllers/ARIMByTruckController.php)

**Key Methods:**
- `index()` - List
- `create()` - Create (API)
- `show($id)` - Detail
- `update($id)` - Update
- `approveReport()` - Approve
- `rejectReport()` - Reject
- `exportPdf()` - Export

### Views
**Directory:** `resources/views/rpt_analytical_result_incoming_material_by_truck/`

| File | Deskripsi |
|------|-----------|
| `index.blade.php` | List dengan filter |
| `show.blade.php` | Detail |
| `preview_layout.blade.php` | Preview |

**Tabel Layout:**
```
Header Info: Material, Supplier, Truck #, Arrival Date, etc.

Simple Table (No Palka Division):
├─ No (Sample Number)
├─ Sampling Date
├─ Police No (Truck number)
├─ Parameters (FFA, Moisture, IV, DOBI, PV, Color R, Color Y)
├─ Analis
└─ Remarks
```

---

## 5. ANALYTICAL RESULT OF OUT GOING SHIPMENT PRODUCT BY TRUCK (F-QOC-13)

### Deskripsi
Laporan hasil analisis produk yang keluar melalui truck. Mencakup quality check produk yang akan dikirim dengan detail per tank truck.

### Database
**Tabel Header:** `t_analytical_result_outgoing_shipment_product_truck`
**Tabel Detail:** `t_analytical_result_outgoing_shipment_product_truck_detail`

### Model
**File:** [app/Models/AROSProductByTruckHeader.php](app/Models/AROSProductByTruckHeader.php)

**Primary Key:** `id` (string)

**Header Atribut:**
```
Informasi Pengiriman:
- id (string)
- company (string)
- plant (string)
- loading_date (datetime) - Tanggal loading
- product_name (string)
- quantity (float)
- ships_name (string) - Bisa jadi destination/buyer
- destination (string)
- load_port (string)

Validasi & Approval:
- corrected_by (string) - Shift leader (LEAD/LEAD_QC)
- corrected_date (datetime)
- corrected_status (string) - Approved/Rejected
- corrected_status_remarks (string)
- approved_by (string) - QC Head (MGR/MGR_QC/ADM)
- approved_date (datetime)
- approved_status (string) - Approved/Rejected
- approved_status_remarks (string)

Metadata:
- entry_by (string)
- entry_date (datetime)
- updated_by (string)
- updated_date (datetime)
- form_no (string) - F/QCO-013
- date_issued (datetime)
- revision_no (integer)
- revision_date (datetime)

Note: Menggunakan 'corrected_' prefix bukan 'prepared_'
```

**Detail Atribut:**
```
Per Tank Information:
- ships_tank (string) - Tank number in ship/truck
- no_police (string) - Truck license plate
- lovibond_color_red (float)
- lovibond_color_yellow (float)
- ffa (float)
- m_and_i (float) - M&I percentage
- iv (float) - Iodine Value
- pv (float) - Peroxide Value
- other (string) - Catatan lain
- remark (string)
```

**Relationship:**
```php
Header::hasMany(Detail::class, 'id_hdr', 'id')
```

### Controller
**File:** [app/Http/Controllers/AROSProductByTruckController.php](app/Http/Controllers/AROSProductByTruckController.php)

**Key Methods:**
- `index()` - List laporan
- `show($id, $intention)` - Detail dengan intention parameter
- `preview($id)` - Preview HTML
- `export($id, $intention)` - Export PDF
- `approveReport()` - Approve
- `rejectReport()` - Reject

**Special Features:**
- Intention parameter: `show`, `preview`, `export`
- Fleksibel field mapping (multiple names untuk same field)
- Support untuk null/empty values dengan fallback

**Approval Prefix:**
```
LEAD/LEAD_QC:
  - Prefix: 'corrected_' (bukan 'prepared_')
  - Status: corrected_status

MGR/MGR_QC/ADM:
  - Prefix: 'approved_'
  - Status: approved_status
```

### Views
**Directory:** `resources/views/rpt_analytical_result_of_out_going_shipment_product_by_truck/`

| File | Deskripsi |
|------|-----------|
| `index.blade.php` | List dengan filter tanggal |
| `show.blade.php` | Detail dengan data per tank |
| `preview_layout.blade.php` | Preview untuk print |

**Tabel Layout:**
```
Header Info: Loading Date, Product, Quantity, Ship's Name, Destination

Analytical Table:
├─ Ship's Tank
├─ No. Police
├─ FFA %
├─ M&I %
├─ IV
├─ Lovibond Red
├─ Lovibond Yellow
├─ PV
├─ Other
└─ Remark
```

---

## POLA UMUM SEMUA MODUL

### 1. Workflow Approval Dua Tahap

```
Entry → Prepared (LEAD/LEAD_QC) → Approved (MGR/ADM)
        ↓
      Draft
        ↓
      Reviewed/Prepared
        ↓
      Final/Approved
```

**Kolom Status Pattern:**
```
prepared_status        (nilai: 'Approved', 'Rejected', 'Pending', null)
prepared_status_remarks (remark kalau rejected)
prepared_by           (username)
prepared_date         (datetime)

approved_status
approved_status_remarks
approved_by
approved_date
```

### 2. View Helper Components

Semua views menggunakan Blade components:
- `<x-section>` - Grouping field dengan title
- `<x-info>` - Display label + value
- Status badge dengan warna: blue=pending, green=approved, red=rejected

### 3. Export Capability

Setiap modul support:
- **Layout Preview** (HTML view untuk print/preview)
- **PDF Export** (menggunakan DomPDF)
- Filter options untuk export

### 4. Routing Pattern

```
Route::prefix('module-name')
  ->name('module-name.')
  ->group(function () {
    Route::get('/', 'index');                    // List
    Route::get('/{id}', 'show');                 // Detail
    Route::post('/{id}/approve-report', 'approveReport');  // Approve
    Route::post('/{id}/reject-report', 'rejectReport');    // Reject
    Route::get('/export/view', 'exportLayoutPreview');      // Preview
    Route::get('/export/pdf', 'exportPdf');      // PDF
  });
```

### 5. Data Model Pattern

Semua tabel mengikuti struktur:
```
PK (id)
├─ Transaction Info (transaction_date, posting_date, etc)
├─ Material/Product Info (material, tank_no, oil_type, etc)
├─ Quality Parameters (qp_*, param_*, hasil_*, etc)
├─ Summary/Composite Results
├─ Entry Info (entry_by, entry_date)
├─ Prepared Approval (prepared_by, prepared_date, prepared_status)
├─ Final Approval (approved_by, approved_date, approved_status)
└─ Form Metadata (form_no, date_issued, revision_no)
```

### 6. Database Schema Conventions

**Column Naming:**
- `qp_*` - Quality Parameters
- `rm_*` - Raw Material
- `fg_*` - Finished Good
- `bp_*` - By Product
- `ss_*` - Sample Summary
- `hasil_analisa_*` - Analysis Result (composite)
- `palka_s_*`, `palka_c_*`, `palka_p_*` - Per hold (Starboard, Center, Port)

**Status Values:**
- `'Approved'` / `'Rejected'` / `'Pending'` / `null`

**Common Fields:**
- `flag` (char 1) - T/F flags
- `remarks` - User remarks/notes
- `updated_by`, `updated_date` - Last modification

---

## ROUTES CONFIGURATION

Semua routes terdaftar di [routes/web.php](routes/web.php):

```php
Route::middleware(['auth'])->group(function () {
  // Modul 1
  Route::prefix('daily-storage-tank-analytical')
    ->name('daily-storage-tank-analytical.')
    ->group(function () { ... });

  // Modul 2
  Route::prefix('daily-quality-composite-fractionation')
    ->name('daily-quality-composite-fractionation.')
    ->group(function () { ... });

  // Modul 3
  Route::prefix('analytical-result-incoming-material-by-vessel')
    ->name('analytical-result-incoming-material-by-vessel.')
    ->group(function () { ... });

  // Modul 4
  Route::prefix('analytical-result-incoming-material-by-truck')
    ->name('analytical-result-incoming-material-by-truck.')
    ->group(function () { ... });

  // Modul 5
  Route::prefix('analytical-result-outgoing-shipment-product-by-truck')
    ->name('analytical-result-outgoing-shipment-product-by-truck.')
    ->group(function () { ... });
});
```

---

## TEKNOLOGI YANG DIGUNAKAN

### Backend
- **Framework:** Laravel 10
- **Database:** MySQL/MariaDB
- **ORM:** Eloquent
- **PDF Generation:** Barryvdh/DomPDF

### Frontend
- **Template Engine:** Blade
- **CSS Framework:** Tailwind CSS
- **JavaScript:** Alpine.js (untuk modal interaktif)
- **Icons:** SVG inline

### Helper & Utilities
- **Carbon:** DateTime handling
- **Validation:** Illuminate\Validation
- **Request Validation:** Form Request classes
- **Authorization:** Can/Policy (implicit via middleware)

---

## PERMISSION/ROLE SYSTEM

**Roles yang digunakan:**
- `LEAD` - Shift Leader (Prepared stage)
- `LEAD_QC` - QC Lead (Prepared stage)
- `MGR` - Manager (Approval stage)
- `MGR_PROD` - Production Manager (Approval stage)
- `MGR_QC` - QC Manager (Approval stage)
- `ADM` - Admin (Approval stage)

**Access Pattern:**
- Prepared stage: LEAD, LEAD_QC
- Approval stage: MGR, MGR_PROD, MGR_QC, ADM
- Admin user dapat access semua

---

## FILE ORGANIZATION

```
elogsheet-laravel/
├── app/
│   ├── Models/
│   │   ├── LSDailyStorageTankAnalytical.php
│   │   ├── LSDailyQualityCompositeFractionation.php
│   │   ├── ARIMByVesselHeader.php
│   │   ├── ARIMByVesselDetail.php
│   │   ├── ARIMByTruckHeader.php
│   │   ├── ARIMByTruckDetail.php
│   │   ├── AROSProductByTruckHeader.php
│   │   └── AROSProductByTruckDetail.php
│   └── Http/Controllers/
│       ├── RptDailyStorageTankAnalyticalController.php
│       ├── RptDailyQualityCompositeFractionation.php
│       ├── ARIMByVesselController.php
│       ├── ARIMByTruckController.php
│       └── AROSProductByTruckController.php
├── resources/views/
│   ├── rpt_daily_storage_tank_analytical/
│   ├── rpt_daily_quality_composite_fractionation/
│   ├── rpt_analytical_result_incoming_material_by_vessel/
│   ├── rpt_analytical_result_incoming_material_by_truck/
│   ├── rpt_analytical_result_of_out_going_shipment_product_by_truck/
│   └── exports/
│       ├── report_daily_storage_tank_analytical_pdf.blade.php
│       ├── report_daily_quality_composite_fractionation_pdf.blade.php
│       ├── report_analytical_result_incoming_material_by_vessel_pdf.blade.php
│       ├── report_analytical_result_incoming_material_by_truck_pdf.blade.php
│       └── report_rpt_analytical_result_of_out_going_shipment_product_by_truck_pdf.blade.php
└── routes/
    └── web.php (routes definition)
```

---

## SUMMARY TABEL PERBANDINGAN

| Aspek | Modul 1 | Modul 2 | Modul 3 | Modul 4 | Modul 5 |
|-------|---------|---------|---------|---------|---------|
| **Form Code** | F/QCO-001 | F/QCO-003 | F-QOC-09 | F-QOC-10 | F-QOC-13 |
| **Jenis Data** | Storage Tank | Fractionation | Incoming (Vessel) | Incoming (Truck) | Outgoing (Truck) |
| **Detail? ** | No (Single) | No (Single) | Yes (Multi Palka) | Yes (Multi Sample) | Yes (Multi Tank) |
| **Tabel Header** | LSDailyStorageTankAnalytical | LSDailyQualityCompositeFractionation | ARIMByVesselHeader | ARIMByTruckHeader | AROSProductByTruckHeader |
| **Tabel Detail** | - | - | ARIMByVesselDetail | ARIMByTruckDetail | AROSProductByTruckDetail |
| **Filter Utama** | Tanggal | Tanggal, Jam, Work Center | Tanggal | Tanggal | Tanggal |
| **Approval Prefix** | prepared/approved | prepared/checked | prepared/approved | prepared/approved | corrected/approved |
| **Key Parameters** | QP (Quality Param) | RM/FG/BP (3 produk) | Palka S/C/P | Sample Per Truck | Tank Per Truck |

---

## NEXT STEPS UNTUK DEVELOPMENT

Setelah memahami ke-5 modul ini, Anda dapat:

1. **Tambah Fields:** Modifikasi Model (fillable), Migration, View
2. **Ubah Validation:** Update controller validation rules
3. **Customize Report:** Modifikasi template PDF/Preview
4. **Add Features:** Filter, export, approval workflow enhancements
5. **Testing:** Unit test & Feature test untuk approval flow

