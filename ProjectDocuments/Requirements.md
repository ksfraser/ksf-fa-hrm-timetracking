# Business Requirements — ksf_FA_HRM_TimeTracking

**Package:** `ksfraser/ksf-fa-hrm-timetracking`
**Version:** `1.0.0`
**Namespace:** `Ksfraser\FrontAccounting\HRM\TimeTracking\*`
**Module sequence:** TT (Time Tracking)

---

## BR-TT-001 — Event code master data

**Status:** IMPLEMENTED

The system SHALL provide `ksf_FA_HRM_TimeTracking` as the owning module for
event codes and time entries. Event codes use ref codes for cross-referencing.

1. Event codes SHALL have a unique ref code (`event_code`).
2. Event codes SHALL define an impact multiplier (e.g., REG=1.0, OT=1.5, HOL=2.0).
3. Event codes SHALL define a pay type (hourly|salary|commission|none).
4. Default event codes seeded: REG, OT, OT2, HOL, SICK, VAC, COMM, BEREAVE, JURY.

---

## BR-TT-002 — Time entries against event codes

**Status:** IMPLEMENTED

Time entries SHALL record hours against an event code for an employee on a date.

1. Time entries cross-reference event codes by ref code.
2. Pay impact is calculated: hours × impact_multiplier × base_rate.
3. Different event codes have different impacts on salary/hourly/commission.

---

## BR-TT-003 — Pay impact calculation

**Status:** IMPLEMENTED

The system SHALL calculate pay impact from event code + hours + base rate.

1. `calculatePayImpact()` returns multiplier, impacted hours, and amount.
2. Overtime (1.5x) and holiday (2.0x) increase the effective hours.
3. Salary and commission event codes use different pay type handling.

---

## BR-TT-004 — Resolved DTOs for RelatedTo

**Status:** IMPLEMENTED

Time Tracking SHALL build resolved display data for both `time_entry` and
`event_code` types for RelatedTo display.

1. Resolved data wraps a `RelatedRecordDto` with resolved fields,
   overridable labels, and a URL.
2. `fieldMap()` returns the field name => display label mapping for each type.

