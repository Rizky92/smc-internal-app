# 0001: No clean-architecture layers; Livewire talks to Eloquent directly

- Status: accepted
- Date: 2026-10-02

## Context

The Indikator Mutu feature was first built with Application (Actions + DTOs), Domain (repository interfaces), and Infrastructure (Eloquent repositories) layers, bound together in `AppServiceProvider`. Every other feature in this app uses Livewire components that query Eloquent models directly.

In practice the layers added indirection without adding logic:

- Most Actions only forwarded to a repository, and most repositories only wrapped `paginate()`, `updateOrCreate()`, or `destroy()`.
- Understanding one save meant reading the component, Action, DTO, interface, and repository (four or five files).
- The feature ended up with two patterns, because Validasi Data, Koreksi, Audit Log, and Dashboard Mutu already used Eloquent directly.
- The record DTO cast the recorder's NIK to `int`, which silently stored `0` for NIKs in a string column.

## Decision

Don't use Application/Domain/Infrastructure layers in this project. Livewire components query and persist Eloquent models directly:

- Validation lives in the component's `rules()`. No DTOs.
- Search uses `Searchable` and `searchColumns()` on the model.
- Filters reused across pages are local scopes on the model, e.g. `QualityIndicator::departemen()` and `QualityIndicatorRecord::periode()`.
- Small business rules are model methods and constants, e.g. `QualityIndicatorRecord::isLocked()` and `STATUS_*`.
- Writes stay wrapped in `tracker_start()` / `tracker_end()`.

Indikator Mutu was refactored to this pattern behind Livewire feature tests in `tests/Feature/Mutu/`.

## Consequences

- One pattern across the codebase, and fewer files per feature.
- Business rules are only as reusable as the model they live on. If a rule ever spans several models or needs orchestration (queues, external services), introduce a focused class for that case instead of reintroducing whole layers.
- Tests target Livewire components and HTTP routes, which are the seams this project already uses. There are no repository mocks.
