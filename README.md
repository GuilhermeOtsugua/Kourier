# Koúrier

**Koúrier** is a secure dataset tooling MVP built with Laravel. It gives an
authenticated team a private workspace to upload dataset artifacts, review and
label them, build ZIP export packages with manifests, and download artifacts or
exports through authorized temporary links.

The project is intentionally lean: Laravel, Livewire, database queues, private
filesystem storage, policies, and audit logging. It avoids direct browser-to-S3
uploads, Redis/Horizon, and multi-service cloud orchestration until the product
shape requires them.

## Current status

| Capability | Status |
|---|---|
| Laravel 13 app with Livewire starter kit | ✅ Implemented |
| Team-scoped authentication / membership access | ✅ Implemented |
| Dataset project creation and listing | ✅ Implemented |
| Private artifact upload through Laravel | ✅ Implemented |
| Authorized temporary artifact downloads | ✅ Implemented |
| Lightweight artifact preview metadata job | ✅ Implemented |
| Artifact labels and review status | ✅ Implemented |
| Dataset export request from selected artifacts | ✅ Implemented |
| ZIP package + CSV manifest export job | ✅ Implemented |
| Authorized temporary export downloads | ✅ Implemented |
| Security audit events for key dataset activity | ✅ Implemented |
| Public landing page with demo CTA | ✅ Implemented |
| Seeded local demo workspace | ✅ Implemented |
| Local/fake storage test coverage | ✅ Implemented |
| S3-compatible filesystem adapter | ✅ Installed |
| Real S3/R2/MinIO runtime validation | ⏳ Requires bucket credentials |

## Secure dataset workflow

```text
Team auth
  → dataset projects
  → private artifact uploads
  → preview metadata job
  → labels + review status
  → export request
  → ZIP archive + CSV manifest job
  → authorized temporary download
  → audit event trail
```

A typical team member flow:

1. Sign in and enter the current team workspace.
2. Create a dataset project.
3. Upload private artifact files into the project.
4. Let the preview job collect lightweight metadata.
5. Add labels and review status to artifacts.
6. Select artifacts for an export request.
7. Let the export job build a ZIP package and `manifest.csv`.
8. Download the artifact or export through a short-lived signed route.

## Runtime defaults

| Area | Default | Notes |
|---|---|---|
| PHP | `^8.3`, developed on PHP `8.5` | Herd was used locally. |
| Framework | Laravel `13.x` | Livewire starter kit with teams. |
| UI | Livewire `4.x`, Flux UI, Tailwind `4.x` | Server-driven dashboard/forms. |
| Database | SQLite locally/tests | Configurable through Laravel database drivers. |
| Queue | `database` | Used for preview and export jobs. |
| Storage | `local` private disk | `DATASET_FILESYSTEM_DISK` can point to S3. |
| Testing | Pest `4.x` | Feature-first TDD coverage. |
| Static analysis | Larastan / PHPStan | Run through Composer scripts. |
| Formatting | Laravel Pint | Run through Composer scripts. |
| AI guidance | Laravel Boost | Guidelines/MCP config included; generated skills ignored. |

## Architecture

```text
                                  browser
                                     │
                                     ▼
                         Laravel HTTP / Livewire UI
                                     │
             ┌───────────────────────┼────────────────────────┐
             ▼                       ▼                        ▼
       ProjectController       ArtifactController      DatasetExportController
       team-scoped access      private uploads         export requests
             │                       │                        │
             ▼                       ▼                        ▼
        projects table         artifacts table          export_requests
                                     │                        │
                                     ▼                        ▼
                         ProcessArtifactPreview       BuildDatasetExport
                         database queue job           database queue job
                                     │                        │
                                     ▼                        ▼
                          private filesystem      ZIP archive + manifest.csv
                          local / S3-compatible    local / S3-compatible
                                     │                        │
                                     └──────────┬─────────────┘
                                                ▼
                                  signed authenticated downloads
                                                │
                                                ▼
                                         audit_events
```

## Module layout

| Path | Responsibility |
|---|---|
| `app/Models/Project.php` | Team-scoped dataset workspace. |
| `app/Models/Artifact.php` | Private uploaded file metadata and processing/review state. |
| `app/Models/ArtifactLabel.php` | Labels, notes, and user attribution for artifact review. |
| `app/Models/DatasetExport.php` | Export request lifecycle and resulting package paths. |
| `app/Models/ExportItem.php` | Artifact selections included in an export. |
| `app/Models/AuditEvent.php` | Security-relevant activity trail. |
| `app/Http/Controllers/*` | Public HTTP behavior for projects, artifacts, labels, exports, downloads. |
| `app/Http/Requests/*` | Authorization and validation for mutating dataset actions. |
| `app/Policies/*` | Team-scoped access rules for artifacts, projects, and exports. |
| `app/Jobs/ProcessArtifactPreview.php` | Lightweight preview metadata extraction. |
| `app/Jobs/BuildDatasetExport.php` | ZIP archive and CSV manifest generation. |
| `config/kourier.php` | Dataset storage disk and path configuration. |
| `tests/Feature/Datasets/*` | End-to-end behavior coverage for the dataset workflow. |

## Domain model

```text
users
teams / team_members
projects
artifacts
artifact_labels
export_requests
export_items
audit_events
jobs / failed_jobs
```

Important relationships:

- A `Team` owns many `Project` records.
- A `Project` owns many `Artifact` and `DatasetExport` records.
- An `Artifact` stores original filename, private disk/path, MIME type, byte size,
  checksum, processing status, review status, and preview metadata.
- An `ArtifactLabel` records label key/value pairs, optional notes, and the user
  who created the label.
- A `DatasetExport` owns many `ExportItem` records and eventually stores a ZIP
  path plus manifest path.
- An `AuditEvent` records who did what, for which team/project/model, with
  request metadata.

## Storage and security model

Koúrier stores dataset artifacts and export packages through Laravel disks. The
local default uses the private local disk; production can use any S3-compatible
disk supported by Laravel's Flysystem adapter.

Local/default:

```env
FILESYSTEM_DISK=local
DATASET_FILESYSTEM_DISK=local
```

S3-compatible runtime:

```env
FILESYSTEM_DISK=s3
DATASET_FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_URL=
AWS_ENDPOINT=
AWS_USE_PATH_STYLE_ENDPOINT=false
```

Security decisions:

- Uploaded artifacts are private by default.
- Downloads are never public object URLs by default.
- Laravel authorizes the user first, then redirects to a short-lived signed route.
- Signed routes still require authentication and team authorization.
- Export packages are generated server-side.
- Direct browser-to-S3 uploads are deliberately deferred.
- Real S3/R2/MinIO validation should use a private bucket with public access
  blocked and least-privilege IAM scoped to the application bucket/prefix.

Minimum IAM-style permissions for a production bucket/prefix are typically:

```text
s3:PutObject
s3:GetObject
s3:DeleteObject
s3:ListBucket
```

Multipart permissions should be added only if large upload/export workflows need
them.

## Queue jobs

Koúrier uses the database queue for MVP simplicity:

```env
QUEUE_CONNECTION=database
```

Run a worker locally:

```bash
php artisan queue:work --tries=3
```

### `ProcessArtifactPreview`

- Marks an artifact as processing.
- Verifies the private file still exists.
- Stores lightweight metadata such as filename, MIME type, byte size, checksum,
  processing timestamp, and line count for small text-like files.
- Marks the artifact `ready` or `failed`.

### `BuildDatasetExport`

- Marks an export request as processing.
- Writes `manifest.csv` with selected artifact metadata.
- Builds a ZIP with `manifest.csv` plus selected artifact files under `artifacts/`.
- Stores the ZIP and manifest privately.
- Marks the export `completed` or `failed`.

## Authorization and audit trail

Authorization is team-scoped and policy-backed:

- A user can view only projects for teams they belong to.
- A user can upload, label, export, or download only inside authorized projects.
- Artifact and export download routes verify both project ownership and current
  user authorization.
- Signed download routes expire and still run authorization checks.

Audit events are recorded for:

- `artifact.uploaded`
- `artifact.downloaded`
- `artifact.labeled`
- `export.requested`
- `export.downloaded`

Each audit event can include user, team, project, audited model, event name,
metadata, IP address, user agent, and timestamp.

## Local setup

Requirements:

- PHP 8.3+
- Composer
- Node/npm
- SQLite extension
- Zip extension

Install and initialize:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=DemoDatasetSeeder
npm run build
```

Run the app locally:

```bash
php artisan serve
```

With Herd, the project can be opened at:

```text
http://kourier.test
```

The demo seed creates a verified local workspace with private files, labels,
audit events, and a completed ZIP export:

```text
Email:    otsugua@example.com
Password: pass
```

To refresh only the demo data, run:

```bash
php artisan db:seed --class=DemoDatasetSeeder
```

Run a queue worker when testing async preview/export processing manually:

```bash
php artisan queue:work --tries=3
```

## Testing and quality gates

Run the full backend quality gate:

```bash
composer run test
```

That clears config and runs:

```text
pint --parallel --test
phpstan analyse --memory-limit=512M
php artisan test
```

Run the frontend production build:

```bash
npm run build
```

Current local verification:

```text
Pint:   passed
PHPStan: passed
Pest:   84 tests / 261 assertions passed
Vite:   production build passed
```

## Laravel Boost

Laravel Boost is installed for first-party Laravel guidance and MCP support.

Committed Boost files:

- `AGENTS.md`
- `CLAUDE.md`
- `.mcp.json`
- `.codex/config.toml`
- `boost.json`

Generated skill directories are intentionally ignored to keep the public repo
small and avoid committing regenerated documentation copies:

```text
.agents/skills
.claude/skills
.junie/skills
```

Regenerate/update local Boost resources when needed:

```bash
php artisan boost:update --discover
```

## Deliberately deferred

These are intentionally out of scope for the lean MVP:

- direct browser-to-S3 multipart uploads,
- Redis/Horizon queue infrastructure,
- browser automation test suite,
- external API token access,
- semantic/vector dataset search,
- complex RBAC beyond team/project authorization,
- public dataset sharing,
- billing,
- production autoscaling / ECS / CloudTrail / KMS-heavy architecture.

## License

Koúrier is licensed under the GNU Affero General Public License v3.0 only (`AGPL-3.0-only`). See [LICENSE](LICENSE) and [NOTICE](NOTICE).

Commercial/proprietary licenses may be available separately. If you want to use Koúrier or derivative work in a closed-source product, hosted service, bundled offering, or other proprietary context, see [COMMERCIAL.md](COMMERCIAL.md).

By contributing to this repository, you agree that your contributions are licensed under `AGPL-3.0-only` and may also be used by the project maintainer under separate commercial license terms. See [CONTRIBUTING.md](CONTRIBUTING.md).

## Repository status

This repository was built with a vertical TDD workflow: one failing behavior test,
minimal implementation, green verification, then a focused commit for each slice.
