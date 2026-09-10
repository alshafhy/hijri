# Muqayem v5

Laravel 13 valuation platform rebuilt from the clean template.

## Legacy picture files

Imported picture metadata lives in `property_pictures`. Binary files are **not** stored in this repo.

### Environment

```env
# Comma-separated absolute directories that contain legacy request photo folders.
# Each base should be the `images/requests` directory (or a directory that contains
# `{legacy_request_id}/{filename}` directly beneath it).
LEGACY_PICTURE_PATHS=/absolute/path/to/muqaym/public/images/requests
```

### On-disk layout the app expects

```text
{LEGACY_PICTURE_PATHS}/{legacy_request_id}/{filename}
```

Example:

```text
/data/muqaym/public/images/requests/3638/1518160.png
```

- `{legacy_request_id}` = `valuation_requests.legacy_id` (old `request.idRequest`)
- `{filename}` = `property_pictures.filename` (old `picture.Picture`)

### Verify after mounting files

```bash
docker exec muquem_v3_app php artisan legacy:verify-pictures
# or one-off path:
docker exec muquem_v3_app php artisan legacy:verify-pictures --path=/absolute/path/to/images/requests
```

Safe to re-run. Updates only `property_pictures.file_exists` and `relative_path`.

The command prints total / found / missing and a sample of unresolved filenames.

### Missing images in the UI / PDF

When a file is absent the app shows an SVG placeholder (never a broken image icon). PDF report helpers call `ValuationReportPictureHtml` / `PropertyPictureMedia::forPdfReport()` so generation succeeds and notes unavailable images as text.
