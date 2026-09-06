# Release tooling

Core Blueprint Likes ships a repository-owned release path for the public `1.0.0-rc1` line.

## Requirements

- PHP 8.4 or 8.5
- Python 3
- `polib==1.2.0`
- GNU gettext (`xgettext`)
- `zip`, `unzip`, `sha256sum`
- Git

## Translation sync

Run:

```bash
python3 tools/sync-i18n.py
```

The script rebuilds the POT from production PHP sources, merges the six launch catalogs (`nl_NL`, `de_DE`, `fr_FR`, `es_ES`, `it_IT`, `pt_PT`), applies repository translation maps and fails if any active string is untranslated. PO files are the source catalogs; MO files are generated from them.

## Conformance

Run:

```bash
php tools/conformance.php
```

The regression suite covers the builder-neutral boundary, dedicated Bricks elements, Golden Core Admin/Foundation ownership, Base dependency policy, domain security/privacy and retained public frontend APIs.

## Release build

Run:

```bash
bash tools/build-release
```

The builder fails closed when:

- the visible public version is not exactly `1.0.0-rc1`;
- translations are incomplete or differ from the checked-in catalogs after sync;
- any PHP file fails lint;
- conformance fails;
- the ZIP cannot be validated;
- developer-only directories leak into the package.

Successful output is written to `dist/`:

- `core-blueprint-likes-1.0.0-rc1.zip`
- `core-blueprint-likes-1.0.0-rc1.zip.sha256`

The ZIP always contains the canonical plugin root `core-blueprint-likes/`.

## Maintenance

When user-facing PHP strings change, add translations to `tools/i18n-translations*.json`, run the sync command and commit the resulting POT/PO/MO updates. When release contracts change, update the matching regression before changing the build gate. Do not weaken a gate merely to make a release pass.
