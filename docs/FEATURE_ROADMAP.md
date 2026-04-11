# Panchayat Chatbot — Feature Roadmap & Implementation Plan

> **Document Purpose:** Detailed phased implementation plan for 10 new features.  
> **Stack:** Laravel 11, PHP 8.2+, PostgreSQL, Blade, Alpine.js, Tailwind CSS  
> **Pattern Reference:** Follow existing patterns — CRUD in modals, `redirect()->route(...index)` after update, `$errors->any()` to reopen modals, database notifications via `Notifiable` trait  
> **Code Style:** Snake_case DB columns, backed string enums with `label()` / `badgeClass()`, thin controllers, `$request->validate()` inline

---

## Summary

| Phase | Features | Complexity | New Tables | Est. Time |
|-------|---------|------------|------------|-----------|
| Phase 1 | Visitor Pass, Resident Directory, Lost & Found | Low-Medium | 3 | 2–3 weeks |
| Phase 2 | Maintenance Requests, Parking/Amenity Booking, Expense Tracking | Medium-High | 5 | 4–6 weeks |
| Phase 3 | Duty Roster, Document Library, Analytics v2, Notification Preferences | Medium | 4 | 3–4 weeks |

---

---

# PHASE 1 — Quick Wins

> These features add high visible value with minimal new infrastructure. Each is self-contained and delivers immediate benefit to residents and security staff.

---

## Feature 1.1 — Visitor Pass Management

### What it is
Residents pre-register expected visitors (name, phone, vehicle, date/time). Security staff see a live list of expected visitors at the gate, approve/deny entry, and log actual arrival. Generates a simple pass reference number.

### Why it matters
Every gated community needs this. Eliminates verbal call-downs from the gate. Gives security a searchable record.

### Access Control
| Action | Role |
|--------|------|
| Create visitor pass | `resident` |
| View own passes | `resident` |
| View all passes (today) | `security_head` |
| Approve / log entry | `security_head` |
| View pass history | `admin`, `security_head` |

### New Migration: `create_visitor_passes_table`

```php
Schema::create('visitor_passes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('resident_id')->constrained('users')->cascadeOnDelete();
    $table->string('visitor_name', 100);
    $table->string('visitor_phone', 15)->nullable();
    $table->string('vehicle_number', 20)->nullable();
    $table->string('purpose', 255)->nullable();
    $table->date('expected_date');
    $table->time('expected_from')->nullable();
    $table->time('expected_to')->nullable();
    $table->string('pass_code', 10)->unique(); // e.g. VP-A3X9K
    $table->enum('status', ['pending', 'approved', 'checked_in', 'checked_out', 'expired', 'cancelled'])
          ->default('pending');
    $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('checked_in_at')->nullable();
    $table->timestamp('checked_out_at')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
    
    $table->index(['expected_date', 'status']); // for gate dashboard query
    $table->index('resident_id');
});
```

### New Enum: `app/Enums/VisitorPassStatus.php`

```php
enum VisitorPassStatus: string {
    case Pending    = 'pending';
    case Approved   = 'approved';
    case CheckedIn  = 'checked_in';
    case CheckedOut = 'checked_out';
    case Expired    = 'expired';
    case Cancelled  = 'cancelled';
    
    public function label(): string { ... }
    public function badgeClass(): string { ... }
}
```

### New Model: `app/Models/VisitorPass.php`

```php
fillable: [
    'resident_id', 'visitor_name', 'visitor_phone', 'vehicle_number',
    'purpose', 'expected_date', 'expected_from', 'expected_to',
    'pass_code', 'status', 'approved_by', 'checked_in_at', 'checked_out_at', 'notes'
]
casts: [
    'expected_date'  => 'date',
    'checked_in_at'  => 'datetime',
    'checked_out_at' => 'datetime',
    'status'         => VisitorPassStatus::class,
]
relationships:
    resident()    → BelongsTo(User, resident_id)
    approvedBy()  → BelongsTo(User, approved_by)

boot():
    auto-generate pass_code on creation → 'VP-' . strtoupper(Str::random(5))

scopes:
    todayExpected()   → where expected_date = today
    pending()         → where status = 'pending'
```

### New Controllers

**`app/Http/Controllers/Resident/VisitorPassController.php`**
```
index()   → resident's own passes (paginate 10, filter by date/status)
store()   → validate + create pass → notify security via DB notification
destroy() → cancel pass (only if status = pending)
```

**`app/Http/Controllers/Security/VisitorGateController.php`**
```
index()    → today's expected visitors, grouped by status
approve()  → PATCH /security/visitors/{pass}/approve
checkIn()  → PATCH /security/visitors/{pass}/check-in   → sets checked_in_at
checkOut() → PATCH /security/visitors/{pass}/check-out  → sets checked_out_at
```

### New Routes

```php
// Resident
Route::prefix('resident')->middleware(['auth', 'role:resident'])->group(function () {
    Route::resource('visitor-passes', VisitorPassController::class)->only(['index', 'store', 'destroy']);
});

// Security
Route::prefix('security')->middleware(['auth', 'role:security_head'])->group(function () {
    Route::get('visitors', [VisitorGateController::class, 'index'])->name('security.visitors.index');
    Route::patch('visitors/{pass}/approve',   [VisitorGateController::class, 'approve'])->name('security.visitors.approve');
    Route::patch('visitors/{pass}/check-in',  [VisitorGateController::class, 'checkIn'])->name('security.visitors.check-in');
    Route::patch('visitors/{pass}/check-out', [VisitorGateController::class, 'checkOut'])->name('security.visitors.check-out');
});
```

### New Notification: `app/Notifications/VisitorPassNotification.php`

Sent to all `security_head` users when a resident creates a new pass.
```php
via: ['database']
data: ['type' => 'visitor_pass', 'pass_id', 'visitor_name', 'expected_date', 'resident_name', 'url']
```

### Views

```
resources/views/resident/visitor-passes/
    index.blade.php   — card list of passes + "Register Visitor" modal

resources/views/security/visitors/
    index.blade.php   — today's gate dashboard: pending | checked-in | checked-out tabs
```

### User Relationships to Add (User model)

```php
visitorPasses()          → HasMany(VisitorPass, resident_id)
approvedVisitorPasses()  → HasMany(VisitorPass, approved_by)
```

---

## Feature 1.2 — Resident Directory

### What it is
An opt-in community phonebook. Residents choose to list their flat, name, and optional contact info. Other residents can browse and search by block/flat. Privacy-first: everything is opt-in.

### Why it matters
Eliminates "does anyone know who lives in B-204?" on WhatsApp groups. Builds community bonds.

### Access Control
| Action | Role |
|--------|------|
| Toggle listing on/off | `resident` (own profile) |
| View directory | All authenticated users |
| Remove any listing | `admin` |

### New Columns on `users` table (migration: `add_directory_columns_to_users_table`)

```php
$table->boolean('is_listed_in_directory')->default(false)->after('is_active');
$table->string('directory_display_name', 100)->nullable()->after('is_listed_in_directory');
$table->text('bio')->nullable()->after('directory_display_name');
$table->string('whatsapp', 15)->nullable()->after('bio'); // optional contact
$table->json('interests')->nullable()->after('whatsapp');  // e.g. ["gardening","yoga"]
```

> **Note:** No new table needed — extends existing `users` table.

### New Controller: `app/Http/Controllers/Shared/ResidentDirectoryController.php`

```
index()   → GET /directory  — all listed residents, filter by block/flat
           → returns $residents paginated, $blocks (distinct list)
update()  → PATCH /profile/directory  — toggle listing + update directory fields
```

### New Routes

```php
Route::middleware('auth')->group(function () {
    Route::get('directory', [ResidentDirectoryController::class, 'index'])->name('directory.index');
    Route::patch('profile/directory', [ResidentDirectoryController::class, 'update'])->name('profile.directory.update');
});
```

### Views

```
resources/views/shared/directory/
    index.blade.php  — search bar + block filter + resident cards grid
                       each card: name, flat/block, optional bio, whatsapp link

resources/views/profile/
    edit.blade.php   — add "Directory Listing" section with toggle + fields
```

### Implementation Notes
- Add `is_listed_in_directory = true` scope on User model: `scopeListed($q)`
- Directory index queries: `User::listed()->orderBy('block')->orderBy('flat_number')`
- Privacy: phone/whatsapp only shown if user opted in (`show_phone_in_directory` bool, or reuse existing `phone` column)

---

## Feature 1.3 — Lost & Found Board

### What it is
A simple community bulletin board. Residents post lost or found items with description, photo, location. Items can be marked as resolved (returned/claimed).

### Why it matters
High goodwill-to-effort ratio. Simple CRUD. Replaces the WhatsApp group spam.

### Access Control
| Action | Role |
|--------|------|
| Post item | All authenticated users |
| Edit own item | Owner |
| Mark resolved | Owner |
| Delete any item | `admin` |

### New Migration: `create_lost_and_found_items_table`

```php
Schema::create('lost_and_found_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->enum('type', ['lost', 'found']);
    $table->string('title', 100);
    $table->text('description');
    $table->string('location_found', 255)->nullable(); // where found / last seen
    $table->date('date_occurred');                      // when lost / when found
    $table->string('contact_info', 255)->nullable();    // overrides profile contact
    $table->string('photo_path', 500)->nullable();
    $table->boolean('is_resolved')->default(false);
    $table->timestamp('resolved_at')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['type', 'is_resolved', 'created_at']);
});
```

### New Model: `app/Models/LostAndFoundItem.php`

```php
fillable: [
    'user_id', 'type', 'title', 'description', 'location_found',
    'date_occurred', 'contact_info', 'photo_path', 'is_resolved', 'resolved_at'
]
casts: [
    'date_occurred' => 'date',
    'resolved_at'   => 'datetime',
    'is_resolved'   => 'boolean',
]
relationships:
    poster() → BelongsTo(User, user_id)

accessor:
    photo_url → Storage::url($this->photo_path)

scopes:
    active()   → where is_resolved = false
    lost()     → where type = 'lost'
    found()    → where type = 'found'
```

### New Controller: `app/Http/Controllers/Shared/LostFoundController.php`

```
index()   → GET  /lost-found              → all active items, filter by type
store()   → POST /lost-found              → validate + upload photo + create
edit()    → (modal on index/show page)
update()  → PATCH /lost-found/{item}      → update fields
resolve() → PATCH /lost-found/{item}/resolve → mark resolved
destroy() → DELETE /lost-found/{item}     → admin or own item only
```

### New Routes

```php
Route::middleware('auth')->group(function () {
    Route::resource('lost-found', LostFoundController::class)->except('show');
    Route::patch('lost-found/{item}/resolve', [LostFoundController::class, 'resolve'])
         ->name('lost-found.resolve');
});
```

### Views

```
resources/views/shared/lost-found/
    index.blade.php  — "Lost | Found" tab filter + item cards with photo
                       "Report Item" modal (create form with photo upload)
                       Edit/Delete/Resolve modals per item
```

### Photo Storage Pattern
Follow `ComplaintMedia` pattern:
```php
$path = $request->file('photo')->store('lost-found', 'public');
```

### User Relationship to Add

```php
lostFoundItems() → HasMany(LostAndFoundItem, user_id)
```

---
---

# PHASE 2 — Core Value Features

> These features directly address operational needs of the management committee. Each requires more domain logic than Phase 1 but reuses existing patterns heavily.

---

## Feature 2.1 — Maintenance Requests & Work Orders

### What it is
A dedicated workflow for maintenance work (broken elevator, leaking roof, electrical fault). Separate from complaints — these track vendor assignment, scheduled dates, cost, and completion photos. Admin manages the entire lifecycle.

### Why it matters
Complaints are grievances; maintenance requests are work orders. The lifecycle differs: they need scheduled dates, external vendors, cost tracking, and photo documentation of completion.

### Access Control
| Action | Role |
|--------|------|
| Create request | `resident`, `admin` |
| View own requests | `resident` |
| View & manage all | `admin` |
| Update status/completion | `admin` |

### New Migration: `create_maintenance_requests_table`

```php
Schema::create('maintenance_requests', function (Blueprint $table) {
    $table->id();
    $table->string('request_number', 20)->unique(); // MNT-2026-00001
    $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
    $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('category_id')->nullable()->constrained('complaint_categories')->nullOnDelete();
    $table->string('title', 255);
    $table->text('description');
    $table->string('location', 255)->nullable();
    $table->enum('status', ['pending', 'approved', 'scheduled', 'in_progress', 'completed', 'rejected', 'cancelled'])
          ->default('pending');
    $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
    $table->string('vendor_name', 100)->nullable();
    $table->string('vendor_contact', 15)->nullable();
    $table->decimal('estimated_cost', 10, 2)->nullable();
    $table->decimal('actual_cost', 10, 2)->nullable();
    $table->dateTime('scheduled_at')->nullable();
    $table->dateTime('completed_at')->nullable();
    $table->text('completion_notes')->nullable();
    $table->text('rejection_reason')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['status', 'priority']);
    $table->index('requested_by');
});
```

### New Migration: `create_maintenance_media_table`

```php
Schema::create('maintenance_media', function (Blueprint $table) {
    $table->id();
    $table->foreignId('maintenance_request_id')->constrained()->cascadeOnDelete();
    $table->string('file_path', 500);
    $table->string('file_name', 255);
    $table->string('mime_type', 100);
    $table->unsignedBigInteger('file_size');
    $table->enum('stage', ['before', 'during', 'after', 'document'])->default('before');
    $table->timestamps();
});
```

### New Enum: `app/Enums/MaintenanceStatus.php`

```php
enum MaintenanceStatus: string {
    case Pending    = 'pending';
    case Approved   = 'approved';
    case Scheduled  = 'scheduled';
    case InProgress = 'in_progress';
    case Completed  = 'completed';
    case Rejected   = 'rejected';
    case Cancelled  = 'cancelled';

    public function label(): string { ... }
    public function badgeClass(): string { ... }
    public function nextAllowedStatuses(): array { ... } // workflow guard
}
```

### New Models

**`app/Models/MaintenanceRequest.php`**
```php
fillable: [
    'request_number', 'requested_by', 'assigned_to', 'category_id', 'title',
    'description', 'location', 'status', 'priority', 'vendor_name', 'vendor_contact',
    'estimated_cost', 'actual_cost', 'scheduled_at', 'completed_at',
    'completion_notes', 'rejection_reason'
]
casts: [
    'status'       => MaintenanceStatus::class,
    'scheduled_at' => 'datetime',
    'completed_at' => 'datetime',
]
relationships:
    requestedBy() → BelongsTo(User, requested_by)
    assignedTo()  → BelongsTo(User, assigned_to)
    category()    → BelongsTo(ComplaintCategory, category_id)
    media()       → HasMany(MaintenanceMedia)

boot():
    auto-generate request_number → 'MNT-' . date('Y') . '-' . str_pad(...)
```

**`app/Models/MaintenanceMedia.php`**
```php
fillable: ['maintenance_request_id', 'file_path', 'file_name', 'mime_type', 'file_size', 'stage']
relationships:
    request() → BelongsTo(MaintenanceRequest)
accessor:
    file_url → Storage::url($this->file_path)
```

### New Controllers

**`app/Http/Controllers/Resident/MaintenanceController.php`** (resident view)
```
index()  → GET  /resident/maintenance        → own requests, paginated
store()  → POST /resident/maintenance        → validate + create
```

**`app/Http/Controllers/Admin/AdminMaintenanceController.php`** (admin management)
```
index()        → GET   /admin/maintenance             → all requests, filter by status/priority
show()         → GET   /admin/maintenance/{req}        → detail view
updateStatus() → PATCH /admin/maintenance/{req}/status → status workflow
assign()       → PATCH /admin/maintenance/{req}/assign → assign to staff
storeMedia()   → POST  /admin/maintenance/{req}/media  → upload completion photos
```

### New Routes

```php
// Resident
Route::prefix('resident')->middleware(['auth', 'role:resident'])->group(function () {
    Route::get('maintenance', [MaintenanceController::class, 'index'])->name('resident.maintenance.index');
    Route::post('maintenance', [MaintenanceController::class, 'store'])->name('resident.maintenance.store');
});

// Admin
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('maintenance', AdminMaintenanceController::class)->only(['index', 'show']);
    Route::patch('maintenance/{req}/status', [AdminMaintenanceController::class, 'updateStatus'])
         ->name('admin.maintenance.status');
    Route::patch('maintenance/{req}/assign', [AdminMaintenanceController::class, 'assign'])
         ->name('admin.maintenance.assign');
    Route::post('maintenance/{req}/media', [AdminMaintenanceController::class, 'storeMedia'])
         ->name('admin.maintenance.media.store');
});
```

### User Relationships to Add

```php
maintenanceRequests()         → HasMany(MaintenanceRequest, requested_by)
assignedMaintenanceRequests() → HasMany(MaintenanceRequest, assigned_to)
```

---

## Feature 2.2 — Parking & Amenity Booking

### What it is
A calendar-based reservation system for shared resources: guest parking slots, community hall, gym, rooftop. Residents book a time slot; admin can approve or auto-approve per resource. Prevents double-booking.

### Why it matters
Eliminates "is the hall free on Saturday?" WhatsApp threads and double-booking conflicts.

### Access Control
| Action | Role |
|--------|------|
| View available slots | All authenticated |
| Book a slot | `resident` |
| Cancel own booking | `resident` |
| Manage resources | `admin` |
| Approve/reject bookings | `admin` |

### New Migrations

**`create_amenities_table`**
```php
Schema::create('amenities', function (Blueprint $table) {
    $table->id();
    $table->string('name', 100);           // "Community Hall", "Guest Parking A", "Gym"
    $table->string('type', 50);            // "parking", "hall", "gym", "other"
    $table->text('description')->nullable();
    $table->integer('capacity')->default(1); // max concurrent bookings
    $table->boolean('requires_approval')->default(false);
    $table->decimal('fee_per_hour', 8, 2)->default(0); // 0 = free
    $table->string('opening_time', 5)->nullable(); // "08:00"
    $table->string('closing_time', 5)->nullable(); // "22:00"
    $table->json('available_days')->nullable(); // [0,1,2,3,4,5,6] (0=Sunday)
    $table->boolean('is_active')->default(true);
    $table->string('photo_path', 500)->nullable();
    $table->timestamps();
});
```

**`create_amenity_bookings_table`**
```php
Schema::create('amenity_bookings', function (Blueprint $table) {
    $table->id();
    $table->string('booking_code', 12)->unique(); // BK-ABC123
    $table->foreignId('amenity_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->dateTime('starts_at');
    $table->dateTime('ends_at');
    $table->string('purpose', 255)->nullable();
    $table->integer('guest_count')->default(0);
    $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'completed'])
          ->default('pending');
    $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
    $table->text('rejection_reason')->nullable();
    $table->decimal('total_fee', 8, 2)->default(0);
    $table->timestamps();
    
    $table->index(['amenity_id', 'starts_at', 'ends_at', 'status']); // conflict check index
    $table->index('user_id');
});
```

### New Models

**`app/Models/Amenity.php`**
```php
fillable: ['name', 'type', 'description', 'capacity', 'requires_approval', 'fee_per_hour',
           'opening_time', 'closing_time', 'available_days', 'is_active', 'photo_path']
casts: ['is_active' => 'boolean', 'requires_approval' => 'boolean', 'available_days' => 'array']
relationships:
    bookings() → HasMany(AmenityBooking)

methods:
    isAvailableAt(Carbon $start, Carbon $end): bool
        → counts approved/pending bookings that overlap the slot
        → checks capacity not exceeded
        → checks within opening/closing time and available_days
    
    photo_url accessor → Storage::url($this->photo_path)
```

**`app/Models/AmenityBooking.php`**
```php
fillable: ['booking_code', 'amenity_id', 'user_id', 'starts_at', 'ends_at', 'purpose',
           'guest_count', 'status', 'reviewed_by', 'rejection_reason', 'total_fee']
casts: ['starts_at' => 'datetime', 'ends_at' => 'datetime']
relationships:
    amenity()    → BelongsTo(Amenity)
    user()       → BelongsTo(User)
    reviewedBy() → BelongsTo(User, reviewed_by)

boot():
    auto-generate booking_code → 'BK-' . strtoupper(Str::random(6))
    auto-calculate total_fee from amenity->fee_per_hour × hours
```

### New Controllers

**`app/Http/Controllers/Shared/AmenityController.php`**
```
index() → list all active amenities with available/booked status for today
show()  → amenity detail + upcoming bookings + book form (modal)
```

**`app/Http/Controllers/Shared/AmenityBookingController.php`**
```
store()   → validate + conflict check + create booking
destroy() → cancel own booking (only if pending or approved future booking)
```

**`app/Http/Controllers/Admin/AdminAmenityController.php`**
```
index()   → manage amenity catalogue
store()   → create amenity
update()  → update amenity
destroy() → deactivate amenity

// Booking management
bookings()  → GET  /admin/amenities/bookings  → pending approvals + all bookings
approve()   → PATCH /admin/bookings/{booking}/approve
reject()    → PATCH /admin/bookings/{booking}/reject
```

### Conflict Check Logic (in `store()`)

```php
// Check if any approved/pending booking overlaps the requested slot
$conflict = AmenityBooking::where('amenity_id', $amenityId)
    ->whereIn('status', ['approved', 'pending'])
    ->where(function ($q) use ($start, $end) {
        $q->whereBetween('starts_at', [$start, $end])
          ->orWhereBetween('ends_at', [$start, $end])
          ->orWhere(function ($q) use ($start, $end) {
              $q->where('starts_at', '<=', $start)->where('ends_at', '>=', $end);
          });
    })->count();

if ($conflict >= $amenity->capacity) {
    return back()->withErrors(['starts_at' => 'This slot is not available.']);
}
```

---

## Feature 2.3 — Expense & Budget Tracking

### What it is
Admin logs community expenses (maintenance, events, security, utility bills). Residents see a public-facing dashboard showing how community funds are spent. Monthly totals, category breakdowns, and an annual summary chart using Chart.js (already installed).

### Why it matters
Financial transparency builds trust. Residents stop questioning "what are our fees used for?" when they can see it clearly.

### Access Control
| Action | Role |
|--------|------|
| View expense dashboard | All authenticated users |
| Log / edit / delete expenses | `admin` |
| Export to CSV | `admin` |

### New Migration: `create_expenses_table`

```php
Schema::create('expenses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // logged by
    $table->string('title', 255);
    $table->text('description')->nullable();
    $table->enum('category', [
        'maintenance', 'security', 'utilities', 'events', 'staff_salary',
        'cleaning', 'landscaping', 'administrative', 'emergency', 'other'
    ])->default('other');
    $table->decimal('amount', 12, 2);
    $table->date('expense_date');
    $table->string('vendor', 100)->nullable();
    $table->string('invoice_number', 50)->nullable();
    $table->string('receipt_path', 500)->nullable(); // uploaded receipt
    $table->boolean('is_recurring')->default(false);
    $table->timestamps();
    
    $table->index(['category', 'expense_date']);
    $table->index('expense_date');
});
```

### New Model: `app/Models/Expense.php`

```php
fillable: [
    'user_id', 'title', 'description', 'category', 'amount', 'expense_date',
    'vendor', 'invoice_number', 'receipt_path', 'is_recurring'
]
casts: [
    'expense_date' => 'date',
    'amount'       => 'decimal:2',
    'is_recurring' => 'boolean',
]
relationships:
    loggedBy() → BelongsTo(User, user_id)

accessor:
    receipt_url → Storage::url($this->receipt_path)

scopes:
    thisMonth()         → whereMonth('expense_date', now()->month)
    byCategory($cat)    → where('category', $cat)
    dateRange($from, $to) → whereBetween('expense_date', [$from, $to])

static methods:
    monthlyTotals(int $year): Collection  → SUM(amount) grouped by month
    categoryBreakdown(int $year): Collection → SUM(amount) grouped by category
```

### New Controllers

**`app/Http/Controllers/Shared/ExpenseDashboardController.php`**
```
index() → GET /expenses
    → returns: monthly_totals (Chart.js data), category_breakdown, recent_expenses
    → year filter via ?year=2026
```

**`app/Http/Controllers/Admin/AdminExpenseController.php`**
```
index()   → GET  /admin/expenses         → full list with filters (modal CRUD)
store()   → POST /admin/expenses         → validate + upload receipt + create
update()  → PATCH /admin/expenses/{exp}  → edit expense
destroy() → DELETE /admin/expenses/{exp} → delete
export()  → GET  /admin/expenses/export  → CSV download (uses existing export pattern)
```

### New Routes

```php
Route::middleware('auth')->group(function () {
    Route::get('expenses', [ExpenseDashboardController::class, 'index'])->name('expenses.index');
});

Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('expenses', AdminExpenseController::class)->except('show');
    Route::get('expenses/export', [AdminExpenseController::class, 'export'])->name('admin.expenses.export');
});
```

### Chart.js Integration

Uses existing Chart.js (v4.5, already in package.json). Two charts on the dashboard:
1. **Bar chart** — monthly spend for current year
2. **Doughnut chart** — spend by category

Data served via same view as Blade variables (not AJAX — keep it simple).

---
---

# PHASE 3 — Polish & Engagement

> These features deepen engagement, add analytical depth, and improve the notification experience. Each builds on Phase 1 & 2 infrastructure.

---

## Feature 3.1 — Duty Roster & Volunteer Scheduling

### What it is
Admin creates duty rosters (e.g., weekly gate duty rotation, event volunteers, committee shifts). Residents see their assigned duties and can voluntarily sign up for open slots.

### Why it matters
Community governance requires distributed responsibility. A digital roster replaces manual WhatsApp scheduling.

### New Migrations

**`create_duty_rosters_table`**
```php
Schema::create('duty_rosters', function (Blueprint $table) {
    $table->id();
    $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
    $table->string('title', 100);              // "Gate Duty — Week 15"
    $table->text('description')->nullable();
    $table->enum('type', ['weekly_duty', 'event_volunteer', 'committee', 'other']);
    $table->date('roster_date');
    $table->string('shift_start', 5);          // "08:00"
    $table->string('shift_end', 5);            // "12:00"
    $table->integer('slots_required')->default(1);
    $table->boolean('is_open_signup')->default(false); // allow self-signup
    $table->timestamps();
    $table->index(['roster_date', 'type']);
});
```

**`create_duty_assignments_table`**
```php
Schema::create('duty_assignments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('roster_id')->constrained('duty_rosters')->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->enum('status', ['assigned', 'confirmed', 'declined', 'completed'])->default('assigned');
    $table->text('notes')->nullable();
    $table->boolean('is_voluntary')->default(false); // true if self-signed up
    $table->timestamps();
    $table->unique(['roster_id', 'user_id']); // one assignment per roster per user
});
```

### New Models

**`app/Models/DutyRoster.php`**
```php
relationships:
    createdBy()   → BelongsTo(User, created_by)
    assignments() → HasMany(DutyAssignment)
    assignedUsers() → BelongsToMany(User, 'duty_assignments')

methods:
    isFull(): bool → assignments()->count() >= slots_required
    isUserAssigned(User $user): bool
```

**`app/Models/DutyAssignment.php`**
```php
relationships:
    roster() → BelongsTo(DutyRoster)
    user()   → BelongsTo(User)
```

### New Controllers

**`app/Http/Controllers/Admin/AdminDutyRosterController.php`**
```
index()  → all upcoming rosters (modal CRUD)
store()  → create roster
update() → edit roster
destroy()→ delete roster
assign() → POST /admin/rosters/{roster}/assign → bulk-assign users
```

**`app/Http/Controllers/Shared/DutyRosterController.php`**
```
index()  → GET /my-duties → user's upcoming assignments
signup() → POST /rosters/{roster}/signup → self-signup for open slots
decline()→ PATCH /assignments/{assignment}/decline
```

---

## Feature 3.2 — Document Library

### What it is
Centralized repository for community documents: bylaws, meeting minutes, maintenance contracts, forms. Admin uploads; residents download. Versioned by upload date.

### Why it matters
Eliminates "can someone send the lease agreement template?" every month. Authoritative single source.

### New Migrations

**`create_document_categories_table`**
```php
Schema::create('document_categories', function (Blueprint $table) {
    $table->id();
    $table->string('name', 50)->unique();   // "Bylaws", "Meeting Minutes", "Forms"
    $table->string('icon', 30)->nullable(); // emoji or icon name
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

**`create_documents_table`**
```php
Schema::create('documents', function (Blueprint $table) {
    $table->id();
    $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
    $table->foreignId('category_id')->constrained('document_categories')->cascadeOnDelete();
    $table->string('title', 255);
    $table->text('description')->nullable();
    $table->string('file_path', 500);
    $table->string('file_name', 255);
    $table->string('mime_type', 100);
    $table->unsignedBigInteger('file_size');
    $table->integer('version')->default(1);
    $table->enum('access_level', ['all', 'resident', 'admin'])->default('all');
    $table->unsignedInteger('download_count')->default(0);
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['category_id', 'access_level']);
});
```

### New Controllers

**`app/Http/Controllers/Admin/AdminDocumentController.php`**
```
index()   → GET  /admin/documents         → manage all docs (modal CRUD)
store()   → POST /admin/documents         → upload + create
update()  → PATCH /admin/documents/{doc}  → update title/description/category
destroy() → DELETE /admin/documents/{doc} → soft delete
```

**`app/Http/Controllers/Shared/DocumentLibraryController.php`**
```
index()    → GET /documents              → browse by category, search by title
download() → GET /documents/{doc}/download → increment count + serve file
```

### Notes
- Files stored at `storage/app/private/documents/` (NOT public — served via controller for access control)
- Use `Storage::disk('local')->download($path, $filename)` in download()
- File size limit: 25MB (adjust in `php.ini` / Laravel validation)

---

## Feature 3.3 — Analytics v2 (Enhanced Dashboard)

### What it is
Extends the existing `AdminAnalyticsController` with: complaint SLA tracking (average resolution time), geographic heatmap by location, monthly trend comparison, and complaint category performance.

### Why it matters
The existing analytics page has basic charts. This makes it genuinely useful for operations management.

### No New Tables Required
All data already exists in: `complaints`, `complaint_updates`, `patrol_assignments`, `security_incidents`

### New Methods on `AdminAnalyticsController`

```php
// Extend existing controller — no new controller needed

slaReport()        → GET /admin/analytics/sla
    → avg resolution time by category, by priority, by month
    → complaints breaching SLA (open > 7 days for urgent, > 30 days for others)

categoryPerformance() → GET /admin/analytics/categories
    → per-category: count, avg resolution, open %, assignee workload

locationHeatmap()  → GET /admin/analytics/heatmap
    → JSON: complaint locations grouped/counted for chart rendering

monthlyComparison() → GET /admin/analytics/monthly
    → current month vs same month last year
```

### New Views

```
resources/views/admin/analytics/
    sla.blade.php             — SLA breach table + avg resolution time chart
    categories.blade.php      — per-category performance table
    heatmap.blade.php         — location frequency table (text-based; no mapping API needed)
    index.blade.php           — extend with tabs linking to new pages
```

### Chart.js Charts to Add
- **Line chart** — monthly complaint trend (this year vs last year)
- **Bar chart** — avg resolution time by category
- **Horizontal bar** — open complaints by age bucket (0-7 days, 7-30 days, 30+ days)

---

## Feature 3.4 — Notification Preferences

### What it is
Let users control which notifications they receive and how often. Options: instant (default), daily digest, or off. Per feature-type (complaints, events, announcements, etc.).

### Why it matters
The current system sends database notifications for emergency alerts only. As more notification types are added (visitor passes, maintenance updates, etc.), users need control to avoid fatigue.

### New Migration: `create_notification_preferences_table`

```php
Schema::create('notification_preferences', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('notification_type', 50); // 'complaint_update', 'new_announcement', etc.
    $table->enum('frequency', ['instant', 'daily_digest', 'off'])->default('instant');
    $table->timestamps();
    $table->unique(['user_id', 'notification_type']);
});
```

### Supported Notification Types

| Key | Description |
|-----|-------------|
| `complaint_update` | Status changes on own complaints |
| `new_announcement` | New published announcements |
| `visitor_pass`     | Gate activity on visitor passes |
| `maintenance_update` | Status updates on maintenance requests |
| `new_event`        | New community events |
| `duty_reminder`    | 24h before assigned duty |
| `emergency_alert`  | Emergency alerts (always instant, cannot be disabled) |

### New Controller: `app/Http/Controllers/Shared/NotificationPreferenceController.php`

```
index()  → GET   /profile/notifications    → preference management page
update() → PATCH /profile/notifications    → save bulk preferences
```

### Helper Trait: `app/Traits/RespectsNotificationPreferences.php`

```php
trait RespectsNotificationPreferences {
    // Check preference before sending
    public static function shouldNotify(User $user, string $type): bool
    {
        $pref = NotificationPreference::where('user_id', $user->id)
                    ->where('notification_type', $type)
                    ->value('frequency');
        
        return ($pref ?? 'instant') === 'instant';
    }
}
```

### Daily Digest (Phase 3 optional extension)
- Laravel `php artisan make:command SendDailyDigest`
- Scheduled via `Schedule::command('digest:send')->dailyAt('08:00')` in `routes/console.php`
- Queries `notification_preferences` where `frequency = 'daily_digest'`
- Batches unsent notifications into a single DB notification

---
---

# Cross-Phase Considerations

## Notifications to Add Per Phase

| Phase | Feature | Notification Type | Recipients |
|-------|---------|-------------------|------------|
| 1 | Visitor Pass created | `visitor_pass` | All `security_head` users |
| 1 | Lost item claimed | (optional) | Item poster |
| 2 | Maintenance status changed | `maintenance_update` | Request creator |
| 2 | Booking approved/rejected | `booking_status` | Booking user |
| 3 | Duty assignment | `duty_reminder` | Assigned user |

## File Storage Paths Summary

| Feature | Path | Disk |
|---------|------|------|
| Visitor Pass photo | (none needed in Phase 1) | — |
| Lost & Found photo | `lost-found/{item_id}/{file}` | `public` |
| Maintenance media | `maintenance/{request_id}/{stage}/{file}` | `public` |
| Amenity photos | `amenities/{file}` | `public` |
| Expense receipts | `expenses/{expense_id}/{file}` | `public` |
| Documents | `documents/{category_id}/{file}` | `local` (private) |

## Navigation Items to Add

### Admin Sidebar
- Phase 1: Visitor Passes (link to security side), Lost & Found
- Phase 2: Maintenance Requests, Amenities, Expenses/Budget
- Phase 3: Duty Roster, Document Library, Analytics (expanded)

### Resident Sidebar
- Phase 1: Visitor Passes, Community Directory, Lost & Found
- Phase 2: Maintenance Requests, Book Amenity
- Phase 3: My Duties, Documents

### Security Sidebar
- Phase 1: Visitor Gate (prominent, top link)

## Naming Conventions for New Code

| Item | Convention | Example |
|------|------------|---------|
| Model | PascalCase | `VisitorPass`, `AmenityBooking` |
| Table | snake_case plural | `visitor_passes`, `amenity_bookings` |
| Controller | PascalCase + Controller | `VisitorGateController` |
| Route name | `resource.action` | `resident.visitor-passes.index` |
| Enum | PascalCase, backed string | `VisitorPassStatus::CheckedIn` |
| Migration | `timestamp_action_table` | `create_visitor_passes_table` |
| Notification | `NounVerbNotification` | `VisitorPassCreatedNotification` |
| View path | `role/resource/action` | `resident/visitor-passes/index.blade.php` |

---

# Implementation Checklist Template (per feature)

Use this checklist when implementing each feature:

```
[ ] Migration created and run
[ ] Model created (fillable, casts, relationships, scopes)
[ ] Enum created (if new status type)
[ ] Controller(s) created
[ ] Routes registered in web.php
[ ] Views created (index with modals, show if needed)
[ ] Notification class created (if needed)
[ ] Notification wired into controller
[ ] User model relationships added
[ ] Navigation link added in layouts/app.blade.php
[ ] CLAUDE.md updated with new conventions (if any)
[ ] Feature manually tested end-to-end
[ ] git commit
```

---

*Document generated: 2026-04-11. Review before each phase kickoff to ensure accuracy against current codebase state.*
