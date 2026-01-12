# Caregiver Module Security Implementation Plan

## Overview
This document outlines the step-by-step implementation of security foundations for the Caregiver module.

## Implementation Order

### Phase 1: Data Foundation (15 min)
1. Create `caregiver_patient_assignments` migration
2. Add `pin_hash` column to `care_givers` table
3. Add `caregiver_id` to `vital_signs` table (for audit trail)

### Phase 2: Model Relationships (10 min)
4. Add relationships to `CareGiver` model
5. Add relationships to `Patient` model
6. Update `VitalSign` model relationship

### Phase 3: Authorization Policies (20 min)
7. Create `CareGiverPolicy`
8. Update `PatientPolicy` to support caregivers
9. Update `VitalSignPolicy` to support caregivers
10. Register policies in `AuthServiceProvider`

### Phase 4: PIN Verification (15 min)
11. Create `VerifyCareGiverPin` middleware
12. Register middleware in `bootstrap/app.php`
13. Apply middleware to caregiver routes

### Phase 5: Audit Logging (10 min)
14. Enhance `ActivityLogService` for caregiver actions
15. Add audit logging to policy methods

**Total Estimated Time:** 70 minutes

---

## Security Rules Summary

### Caregiver Access Rules:
- ✅ Can ONLY access patients explicitly assigned via `caregiver_patient_assignments`
- ✅ Role-based: `primary` = full access, `secondary` = read/write, `backup` = read-only
- ✅ Must verify PIN before accessing any patient data
- ✅ All access attempts logged to ActivityLog

### Doctor Access Rules:
- ✅ Read access to all patients they have consultations with
- ✅ Can add recommendations for caregivers
- ✅ Cannot modify caregiver logs (read-only on caregiver actions)

### Family Access Rules:
- ✅ Read-only access (if implemented in future)
- ✅ Can view assigned patient data but cannot modify

### Admin Access Rules:
- ✅ Full access to all patients and caregiver actions
- ✅ Can assign caregivers to patients
- ✅ Can manage caregiver PINs

---

## Database Schema Changes

### New Table: `caregiver_patient_assignments`
```sql
- id (bigint, primary)
- caregiver_id (foreign key to care_givers)
- patient_id (foreign key to patients)
- care_plan_id (nullable, foreign key to care_plans - future)
- role (enum: primary, secondary, backup)
- status (enum: active, inactive)
- assigned_by (foreign key to admin_users)
- created_at, updated_at
- Indexes: (caregiver_id, patient_id), (patient_id), (status)
```

### Modified Table: `care_givers`
```sql
- Add: pin_hash (string, nullable)
- Index: pin_hash (for verification lookups)
```

### Modified Table: `vital_signs`
```sql
- Add: caregiver_id (nullable, foreign key to care_givers)
- Index: caregiver_id (for audit queries)
```

---

## Middleware Flow

```
Request → Auth Guard Check → PIN Verification → Policy Check → Action → Audit Log
```

---

## Next Steps After Security Foundation

Once this is complete, we can:
- Build caregiver dashboards (with proper authorization)
- Create observation/medication log models (with policies)
- Implement care plan features (with access controls)

---

**Status:** Ready for implementation
**Started:** [Date]
**Completed:** [Date]

