# Changelog

## 1.0.0-rc1 — 2026-08-27

- Aligns the Likes admin screen with the current Core Blueprint WordPress-Pro Foundation while keeping the plugin version at `1.0.0-rc1`.
- Replaces the legacy eyebrow/page-header markup with the shared `cb-core-title` and `cb-core-intro` hierarchy.
- Updates metadata and divider markup to the current Foundation contract.
- Uses the shared Core Blueprint Lucide disclosure icon on Core-admin screens and a native Dashicon fallback when Base is unavailable.
- Keeps General and Post types as intentional configuration/workspace surfaces while simplifying User profiles and Usage into quiet Foundation sections.
- Adds Core Foundation toast feedback after settings saves without converting the WordPress `options.php` settings flow to AJAX.
- Gives the standalone Settings fallback a WordPress-native presentation instead of relying on Base-owned visual components.
- Integrates the Base v2.0.0-rc90 Clipboard Foundation on the Usage tab, with a Copy button for every shortcode example and no Likes-specific clipboard, icon or toast implementation.
- Hardens internationalization across the current rc1 UI: English remains the source/default language, with bundled Dutch (`nl_NL`), German (`de_DE`), French (`fr_FR`), Spanish (`es_ES`), Italian (`it_IT`) and European Portuguese (`pt_PT`) translations. Recent clipboard/save-feedback strings use the Likes textdomain, and visible JavaScript strings are supplied through translated PHP data instead of local English fallbacks.
- Adds governed settings auditing through Core Blueprint Base: meaningful changes to `cb_likes_settings` emit a `likes.settings_changed` notice event with changed setting paths only. Stored labels, SVG/icon payloads and normal member Like/Dislike activity are never copied into the audit context.
- No reaction storage, REST, frontend, Bricks, privacy or entitlement behavior changes.

## 0.1.0-rc8 — 2026-08-25

- Adds a curated built-in Lucide icon picker with Heart, Heart Crack, Thumbs Up, Thumbs Down, Lightbulb, Circle Slash, Star and Sparkles.
- Keeps the built-in icon set fully local: no Lucide runtime, CDN or external request is loaded.
- Adds a compact live preview for built-in icon choices globally and per post type.
- Preserves rc7 icon-source settings; existing built-in Like/Dislike choices default to Heart and Thumbs Down.
- Adds the Lucide ISC/MIT license notice in `THIRD-PARTY-LICENSES.md`.
- Updates bundled Dutch and German translations for the built-in icon picker.

## 0.1.0-rc7 — 2026-08-25

- Splits the admin screen into Core Blueprint-styled General, Post types, User profiles and Usage tabs without reloading the page.
- Adds reaction icon sources: built-in icon, WordPress Media Library or safe inline SVG.
- Stores Media Library icon choices as attachment IDs and renders only valid image attachments.
- Adds per-post-type icon-source overrides with an explicit Inherit global option.
- Preserves existing rc4-rc6 custom SVG settings by automatically recognizing them as Inline SVG when the new source field is absent.
- Keeps all Media Library and inline SVG functionality optional; the built-in local icons remain the zero-configuration fallback.
- Updates bundled Dutch and German translations for the new admin UI.

## 0.1.0-rc6 — 2026-08-25

- Adds a global **Show reactions only for logged-in users** setting. It defaults to enabled for privacy-first, account-based reactions.
- Adds per-post-type reaction visibility overrides: inherit the global setting, logged-in users only, or show reactions to everyone with a login notice.
- Adds a customizable logged-out login message globally and per post type.
- Keeps guest reactions impossible: logged-out clicks never call the reaction write endpoint and no guest identifier, cookie, IP address or fingerprint is stored.
- Hides both reaction buttons and counts from logged-out visitors when the effective visibility policy is logged-in-only.
- Clarifies the User targets setting as **Enable likes for user profiles** and explains its intended use without requiring Core Blueprint Profiles.
- Updates bundled Dutch and German translations for all new settings and frontend messaging.

## 0.1.0-rc5 — 2026-08-25

- Makes every post-type override section collapsible and collapsed by default to reduce admin-page scrolling.
- Keeps each post type's Enabled/Disabled state and Core Blueprint switch visible in the collapsed header, so targets can be toggled without opening their override settings.
- Reuses the Core Blueprint Base rack toggle and collapse/chevron primitives; Likes owns only the small disclosure-state wiring and token-driven layout.
- Keeps enable/disable state independent from disclosure state.
- Adds accessible `aria-expanded`, `aria-hidden` and `inert` handling for collapsed override controls.

## 0.1.0-rc4 — 2026-08-25

- Evolves the single-state Like row into a mutually exclusive Like/Dislike reaction row while preserving existing likes as `reaction=like`.
- Adds optional Dislike button/count shortcodes and matching Bricks dynamic tags/conditions.
- Adds global Like/Dislike labels and safe inline SVG icons.
- Adds per-post-type label/icon overrides and per-post-type Dislike inherit/enabled/disabled control.
- Keeps WordPress-user targets Like-only in this RC.
- Adds bundled Dutch (`nl_NL`) and German (`de_DE`) translations; English remains the source language.
- Updates the privacy exporter to include reaction type and timestamps.
- Keeps one database row per user/target pair and makes Like ↔ Dislike switches update that row atomically.

## 0.1.0-rc3 — 2026-08-25

- Fixes `[cb_like_count]` so it renders as a standalone count element instead of bare text.
- Keeps standalone count elements synchronized live when the same target is liked or unliked.
- No storage, REST or permission changes.

## 0.1.0-rc2 — 2026-08-25

- Fixes Core Blueprint card markup on the Likes admin page so shared header/body spacing tokens are applied correctly.
- Replaces Likes-page inline spacing with the shared Core Blueprint divider and token-driven Likes layout styles.
- No functional changes.

## 0.1.0-rc1 — 2026-08-25

- Initial release candidate.
- Adds privacy-first logged-in likes for selected public post types and optional WordPress users.
- Adds normalized `wp_cb_likes` storage with no IP, fingerprint, device, browser or location fields.
- Adds idempotent REST state writes, counts and query APIs.
- Adds Bricks dynamic data, condition and query integration without requiring Bricks.
- Adds WordPress personal-data export/erasure integration.
