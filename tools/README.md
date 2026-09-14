# Core Blueprint Likes release tooling

## Prerequisites

The release tooling expects PHP 8.4+, Python 3, WP-CLI with the i18n commands, GNU gettext (`msgmerge`, `msgattrib`, `msgfmt`), Node.js, `zip`, `unzip`, and `sha256sum`.

## Translation workflow

The English runtime source is authoritative. Reviewed translation sources live in the six PO catalogs for `nl_NL`, `de_DE`, `fr_FR`, `es_ES`, `it_IT`, and `pt_PT`.

When translatable source changes, run:

```bash
tools/i18n/update
```

Review the resulting POT/PO/MO diff and commit it deliberately. This canonical update path is the only mutating localization authority.

For read-only verification run:

```bash
tools/i18n/check
```

This verifies the canonical implementation reference, source/POT freshness, locale completeness, metadata, placeholders, shared translations, and reproducibility of committed MO files.

## Conformance

Run:

```bash
php tools/conformance.php
```

The regression suite covers Bootstrap/Base dependency policy, dependency-loss fail-closed behavior, public API/domain security, the builder-neutral boundary, Core Admin/Foundation ownership and the licensed Updates adapter contract.

## Release build

Run from the repository root:

```bash
tools/build-release
```

The builder fails closed unless canonical localization, PHP lint, JavaScript syntax and Likes conformance pass. It packages only customer runtime files beneath the canonical `core-blueprint-likes/` root and writes a SHA-256 checksum only after archive validation.

Successful builds create:

- `dist/core-blueprint-likes-1.0.0-rc1.zip`
- `dist/core-blueprint-likes-1.0.0-rc1.zip.sha256`

Do not bypass a failed release gate. Fix source or reviewed catalogs and rerun the canonical workflow.
