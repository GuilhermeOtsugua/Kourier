# Koúrier

Koúrier is a lean Laravel secure dataset tooling MVP for authenticated teams.

Core flow:

```text
Team auth → dataset projects → private artifact uploads → labels/review → preview jobs → export ZIP + manifest → signed downloads → audit trail
```

## Local setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
```

Run the app with Herd at:

```text
http://kourier.test
```

Run tests:

```bash
php artisan test
```

## Storage

Koúrier stores artifacts and exports through Laravel disks.

Local/default:

```env
FILESYSTEM_DISK=local
DATASET_FILESYSTEM_DISK=local
```

S3-compatible production/runtime:

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

Use a private bucket with public access blocked. Grant the application only the bucket/prefix permissions it needs for private object writes, reads, deletes, listing, and multipart uploads if large uploads are later added.

## Queue

The MVP uses Laravel's database queue:

```env
QUEUE_CONNECTION=database
```

Run a worker locally when you want preview/export jobs to process asynchronously:

```bash
php artisan queue:work --tries=3
```

## Implemented MVP capabilities

- Team-scoped dataset projects
- Private artifact upload
- Authorized, temporary signed artifact downloads
- Lightweight artifact preview metadata job
- Artifact labels and review status
- Dataset export request with selected artifacts
- ZIP package + CSV manifest build job
- Authorized, temporary signed export downloads
- Audit events for uploads, downloads, labels, and export requests/downloads
