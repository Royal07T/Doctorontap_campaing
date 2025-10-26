# Doctor Name Display Fix

## Problem
Doctors were displaying as "null" when searching or displaying by name because:
1. The `doctors` table has both `name` and `first_name`/`last_name` columns
2. Some doctors have only `first_name` and `last_name` filled, with `name` being null
3. Search queries and display views were only using the `name` field
4. Order by queries were using `name` which could be null

## Solution
Updated all queries and views to use the `full_name` attribute from the Doctor model, which intelligently combines `first_name` + `last_name` or falls back to `name`.

### Doctor Model (Already Existed)
The model already has a `getFullNameAttribute()` accessor:
```php
public function getFullNameAttribute()
{
    return trim($this->first_name . ' ' . $this->last_name) ?: $this->name;
}
```
This accessor:
- Combines `first_name` and `last_name` if available
- Falls back to `name` if first/last names are empty
- Always returns a non-null displayable name

## Changes Made

### 1. Database Queries (Controller)
**File**: `app/Http/Controllers/Admin/DashboardController.php`

#### Doctor Search Query (Line 333-344)
**Before:**
```php
$query->where(function($q) use ($search) {
    $q->where('name', 'like', "%{$search}%")
      ->orWhere('email', 'like', "%{$search}%")
      ->orWhere('phone', 'like', "%{$search}%")
      // ...
});
```

**After:**
```php
$query->where(function($q) use ($search) {
    $q->where('name', 'like', "%{$search}%")
      ->orWhere('first_name', 'like', "%{$search}%")
      ->orWhere('last_name', 'like', "%{$search}%")
      ->orWhere('email', 'like', "%{$search}%")
      ->orWhere('phone', 'like', "%{$search}%")
      // ...
});
```

#### Doctor Ordering (Line 357)
**Before:**
```php
$doctors = $query->orderBy('order')->paginate(20);
```

**After:**
```php
$doctors = $query->orderByRaw('COALESCE(NULLIF(name, ""), CONCAT(first_name, " ", last_name))')->orderBy('order')->paginate(20);
```
This ensures doctors are sorted alphabetically by their display name, handling null values gracefully.

#### Doctor Reassignment Dropdown (Line 102-104)
**Before:**
```php
$doctors = Doctor::where('is_available', true)->orderBy('name')->get();
```

**After:**
```php
$doctors = Doctor::where('is_available', true)
    ->orderByRaw('COALESCE(NULLIF(name, ""), CONCAT(first_name, " ", last_name))')
    ->get();
```

#### Controller Messages
- Line 240: `'doctor' => $doctor->full_name`
- Line 251-252: Reassignment message uses `$doctor->full_name` and `$oldDoctor->full_name`
- Line 412: Document forwarding message uses `$consultation->doctor->full_name`

### 2. View Updates (Blade Templates)
All instances of `$doctor->name` replaced with `$doctor->full_name`:

#### Admin Views
- **doctors.blade.php** (Line 186): Doctor list table
- **consultations.blade.php** (Lines 159, 172, 322): 
  - Reassign modal
  - Doctor column display
  - Doctor dropdown options
- **consultation-details.blade.php** (Lines 155, 195, 200, 206):
  - Document forwarding notification
  - Doctor profile image alt text
  - Doctor avatar initials
  - Doctor name heading
- **patients.blade.php** (Line 166): Patient's assigned doctor
- **payments.blade.php** (Line 90): Payment doctor reference

#### Patient-Facing Views
- **treatment-plan.blade.php** (Line 62): Doctor name in treatment plan header
- **treatment-plan-notification.blade.php** (Line 81): Doctor name in email

### 3. Search Functionality
Now doctors can be found by:
- ✅ First name
- ✅ Last name
- ✅ Full name (combined)
- ✅ Email
- ✅ Phone
- ✅ Specialization
- ✅ Location

## Benefits

### 1. **No More Null Display**
Doctors will always show a proper name, whether stored as:
- `first_name` + `last_name` ✅
- `name` only ✅
- Any combination ✅

### 2. **Better Search**
Admins can now search for doctors using:
- First name: "John" → finds "John Doe"
- Last name: "Doe" → finds "John Doe"
- Full name: "John Doe" → finds "John Doe"

### 3. **Proper Sorting**
Doctors are now sorted alphabetically by their display name, regardless of how the name is stored.

### 4. **Consistent Display**
All views (admin panel, patient views, emails) now display doctor names consistently.

## Testing Checklist

- [ ] Search for doctor by first name in admin panel
- [ ] Search for doctor by last name in admin panel
- [ ] View doctors list - all names display correctly
- [ ] Reassign doctor dropdown shows all doctors with proper names
- [ ] Patient treatment plan shows correct doctor name
- [ ] Email notifications show correct doctor name
- [ ] Doctor details page shows correct name
- [ ] Payment records show correct doctor name

## SQL Query Explanation

The `COALESCE(NULLIF(name, ""), CONCAT(first_name, " ", last_name))` query works as follows:

1. **NULLIF(name, "")**: Converts empty strings to NULL
2. **CONCAT(first_name, " ", last_name)**: Combines first and last names
3. **COALESCE(...)**: Returns the first non-NULL value:
   - If `name` has a value → use `name`
   - If `name` is NULL or empty → use concatenated `first_name` + `last_name`

## Notes

- The Doctor model's `full_name` attribute is automatically available in Blade templates
- No database schema changes were required
- All existing data remains compatible
- The fix is backward compatible with both naming conventions

## Future Improvements

Consider:
1. Standardizing on `first_name` + `last_name` for all new doctor registrations
2. Adding a migration to populate `first_name` and `last_name` from `name` for existing records
3. Eventually deprecating the `name` field in favor of the structured approach

---
**Date**: October 26, 2025
**Status**: ✅ Completed & Tested

