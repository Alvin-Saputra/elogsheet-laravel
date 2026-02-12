# Form Transfer Approval Flow Migration

**Date**: 2026-02-09
**Change**: Convert Form Transfer (F/QCO-018) from 4-step to 2-step approval flow

## Summary

Changed approval workflow from:
- `prepared` → `checked` → `approved` → `acknowledged` (4-step)

To:
- `prepared` (Lead) → `approved` (Manager) (2-step)

## Pre-Deployment Checklist

- [ ] Verify no Form Transfer records are currently pending in `checked` or `acknowledged` states
- [ ] Backup `t_form_transfer_header` table
- [ ] Notify QC team of workflow change
- [ ] Schedule deployment during low-activity period

## Deployment Steps

### 1. Backend Deployment (Laravel)

```bash
# Pull latest code
git pull origin main

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Run migration
php artisan migrate

# Verify migration
php artisan migrate:status | grep form_transfer
```

### 2. Mobile App Deployment (Flutter)

The Flutter app needs to be updated on all devices. Use Shorebird for OTA update or release new APK.

```bash
# Build release APK
flutter build apk --release

# Or use Shorebird for OTA
shorebird release
```

## Rollback Procedure

If issues are encountered:

### 1. Rollback Migration

```bash
php artisan migrate:rollback --step=1
```

This will recreate the dropped columns:
- `checked_status`, `checked_by`, `checked_date`, `checked_status_remarks`, `checked_role`
- `acknowledged_status`, `acknowledged_by`, `acknowledged_date`, `acknowledged_status_remarks`, `acknowledged_role`

### 2. Restore Previous Code

```bash
git checkout HEAD~1 -- app/Http/Controllers/Api/FormTransferController.php
git checkout HEAD~1 -- app/Models/LSFormTransferHeader.php
git checkout HEAD~1 -- resources/views/rpt_form_transfer/
```

### 3. Clear Caches

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## Role Mappings

| Approval Level | Roles | Action |
|----------------|-------|--------|
| `prepared` (Lead) | LEAD, LEAD_QC | First approval step |
| `approved` (Manager) | MGR, MGR_QC, ADM | Final approval step |

## API Changes

**Endpoint**: `PUT /api/form-transfer/{id}/approve`

**Request Body**:
```json
{
  "level": "prepared",  // or "approved"
  "status": "Approved", // or "Rejected"
  "remarks": "Optional remark"
}
```

**Valid Levels**: `prepared`, `approved` only
- Old levels (`checked`, `acknowledged`) will return 400 Bad Request

## Files Changed

### Backend (Laravel)
- `app/Http/Controllers/Api/FormTransferController.php` - Approval logic
- `app/Models/LSFormTransferHeader.php` - Removed columns from $fillable
- `database/migrations/2026_02_09_141014_drop_form_transfer_checked_acknowledged_columns.php`
- `resources/views/rpt_form_transfer/*.blade.php` - Updated views

### Mobile (Flutter)
- `lib/features/form_transfer/presentation/provider/form_transfer_provider.dart`
- `lib/features/form_transfer/presentation/pages/form_transfer_*_page.dart`
- `lib/features/form_transfer/data/model/remote/form_transfer_header_model.dart`
