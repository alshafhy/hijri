# Phase 7–8 Valuation Domain Migration Plan

> **For agentic workers:** Execute task-by-task. Gate 6 approved with Qima MIGRATE, privacy/SLA SKIP, client-log SKIP, Word PDF SKIP.

**Goal:** Migrate valuation domain from read-only `muqaym` into `muquem_v3` with a direct request→property FK, then rebuild Spatie ACL + template leftovers.

**Architecture:** Legacy MySQL connection (read-only). New normalized schema. Chunked, idempotent, resumable Artisan importers with `import_quarantine` + `import_checkpoints`. Pictures: metadata + path verify only (queued). Phase 8: Spatie roles/policies + real dashboard.

**Tech Stack:** Laravel 13, Spatie Permission, queued Jobs, Pest.

**Spec:** Gate 6 approval message (this conversation).

## Integrity baseline (measured 2026-09-10)

| Metric | Count |
|--------|------:|
| requests | 4488 |
| REI | 4433 |
| locations | 4528 |
| pictures (DB) | 37176 |
| exact_one REI per request | 4427 |
| none | 61 |
| more_than_one | 0 |
| ambiguous Location (multi-request or multi-REI) | 0 |
| resolvable_certain | 4427 |
| unmatched/orphan requests | 61 |
| REI with no request | 6 |
| orphan locations | 34 |

Note: user estimate ~259k pictures; DB has 37,176. Disk files largely absent in container — importer logs missing files, does not fail the run.

## Global Constraints

- Never write to `muqaym`. Target DB only `muquem_v3`.
- Direct `properties.valuation_request_id` FK — never shared Location join.
- Importer refuses to guess; quarantine with reason.
- نعم/لا → boolean/enum; comps `_1.._4` → rows; building_areas/price → component rows.
- No `real_estate_data` / `requests_grouping` tables; no view-as-table recreations.
- Qima fields + locking rules migrate.
- Privacy/SLA, client-log throttle, Word contractor PDF: SKIP.

---

### Task 1: Legacy connection + import infra tables
- [ ] Add `legacy` DB connection (env-synced)
- [ ] Migrations: `import_runs`, `import_checkpoints`, `import_quarantine`

### Task 2: Domain schema
- [ ] geo, companies, valuation_requests (+ qima), properties (FK), property_locations
- [ ] property_comparables, property_adjustments, property_components
- [ ] property_pictures, request_fee_shares, partners/contractors/offers/contracts (approved)
- [ ] Models + enums (request state, yes/no)

### Task 3: Importers
- [ ] `legacy:import` orchestrator, chunked, resumable
- [ ] Request↔property linker with certainty rules + quarantine
- [ ] Picture job: metadata + `file_exists` check
- [ ] Report resolved/ambiguous/orphaned counts

### Task 4: Gate 7
- [ ] migrate on muquem_v3; dry-run report; commit + push

### Task 5: Phase 8 ACL
- [ ] Roles Manager/Coordinator/Evaluator + permissions
- [ ] Policies for approve/unapprove/mark-evaluated/qima lock
- [ ] No Blade-only enforcement

### Task 6: Phase 8 UI
- [ ] Real role-aware dashboard; footer from config; placeholder audit
- [ ] Qima upload UX on new template
- [ ] Gate 8 commit + push
