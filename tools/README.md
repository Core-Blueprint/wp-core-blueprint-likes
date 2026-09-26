# Release tooling

Core Blueprint Likes ships a repository-owned release path for the public `1.0.0-rc1` line.

## Requirements

- PHP 8.4 or 8.5
- Python 3
- WP-CLI with the `wp i18n` commands
- GNU gettext (`msgmerge`, `msgattrib`, `msgfmt`)
- `zip`, `unzip`, `sha256sum`
- Git

No third-party Python package is required by the canonical Likes release path.

## Localization authority

The canonical localization commands are:

```bash
./tools/i18n/check-reference
./tools/i18n/update
./tools/i18n/check
```

`./tools/i18n/update` is the only command allowed to mutate committed localization catalogs. It regenerates the POT from product source, merges the six reviewed PO catalogs (`nl_NL`, `de_DE`, `fr_FR`, `es_ES`, `it_IT`, `pt_PT`), removes obsolete entries, validates translations and regenerates committed MO runtime artifacts.

`./tools/i18n/check` is read-only. It proves source/POT/PO parity, translation completeness and MO reproducibility.

Translation authority is:

1. English product source
2. generated POT
3. reviewed PO catalogs
4. generated MO runtime artifacts

The former `tools/i18n-translations*.json` overlays were reconciled into the reviewed PO catalogs before removal. They are not a second translation authority.

When user-facing strings change:

```bash
./tools/i18n/update
git diff -- languages/
./tools/i18n/check
```

Review the catalog diff before committing it.

## Conformance

Run:

```bash
./tools/check
```

This performs PHP syntax validation, the canonical localization check and all Likes regression contracts exposed through `tools/conformance.php`.

## Release build

Run:

```bash
./tools/build-release
```

The release builder is read-only with respect to tracked release-visible source. It runs `./tools/check` before packaging and fails closed when any mandatory gate fails.

Accepted customer artifacts are written only to:

```text
dist/core-blueprint-likes-1.0.0-rc1.zip
dist/core-blueprint-likes-1.0.0-rc1.zip.sha256
```

The ZIP preserves the canonical `core-blueprint-likes/` root, validates packaged version identity and rejects development-only paths.

## Local operator state

`dist/`, `build/` and `.venv/` are local/generated state and are ignored by Git. Do not install ad-hoc Python packages to make the release path pass. If a future third-party Python dependency becomes genuinely necessary, declare it with exact pins in `tools/requirements-release.txt` and document its isolated bootstrap.
