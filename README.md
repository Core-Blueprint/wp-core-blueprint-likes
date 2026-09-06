# Core Blueprint Likes

Lightweight privacy-first likes and optional dislikes for WordPress posts and users.

## 1.0.0-rc1 scope

- Reactions are always account-based; guest reactions are never stored and no guest fingerprinting is used.
- Logged-out reaction UI can be hidden globally or per post type. When visible, a customizable login notice is shown instead of storing a guest reaction.
- Select eligible public WordPress post types.
- Optional WordPress-user Like targets for profile integrations; users cannot like themselves and dislikes are not available for user targets.
- One normalized database row per user/target pair with a mutually exclusive `like` or `dislike` reaction.
- Existing pre-release like rows migrate to `reaction=like`.
- Global Like/Dislike labels with a curated built-in icon set, Media Library or safe inline SVG icon sources.
- Per-post-type label/icon overrides, including inheriting or changing the icon source.
- Golden Core Admin information architecture: Overview, General, Post types, User profiles and Integrations.
- Likes configuration lives under **Core Blueprint → Extensions → Community → Likes**; tabs use canonical Settings Hub `extension=core-blueprint-likes&tab=...` deep links.
- Dislikes can inherit the global default or be enabled/disabled per post type.
- Idempotent REST reaction API; switching Like → Dislike updates the existing row instead of creating a second reaction.
- Like/dislike counts and state helpers.
- Liked-post and most-liked queries remain Like-specific.
- WordPress privacy exporter/eraser integration.
- Builder-neutral data, conditions, queries and interaction components.
- Optional Bricks adapter with dedicated Like and Dislike elements, dynamic tags, conditions and Query Loops.
- Standalone shortcodes: `[cb_like_button]`, `[cb_like_count]`, `[cb_dislike_button]`, `[cb_dislike_count]`.
- Integration guidance exposes Foundation-powered Copy actions through Core Blueprint Base's public Clipboard Foundation.
- English source strings with bundled Dutch (`nl_NL`), German (`de_DE`), French (`fr_FR`), Spanish (`es_ES`), Italian (`it_IT`) and European Portuguese (`pt_PT`) translations.
- Administrative Likes setting changes are written to the central Core Blueprint Audit Log with changed setting paths only; normal member reaction activity is deliberately not audit-logged.
- No telemetry or external requests.
- No dependency on Profiles, Communities, Access, LMS, Paid Content, Subscriptions, WooCommerce or Bricks.

## Builder integration

Likes remains builder-agnostic. Builder-neutral contracts own reaction data, conditions, collections, target resolution and interaction components. Bricks is an optional thin adapter.

When Bricks is active, Likes provides:

- dedicated **Like** and **Dislike** elements;
- dynamic data for Like count, Dislike count, current-user Like state and current-user Dislike state;
- conditions for current-user Like/Dislike state;
- Query Loops for posts liked by the current user and most-liked posts.

Dedicated elements use the current template or Query Loop target by default. A specific target can be selected as an advanced override. Like supports enabled content and user targets; Dislike supports enabled content targets only.

## User target example

`[cb_like_button target_type="user" target_id="123"]`

User targets must first be enabled in Likes settings. Self-likes are blocked by default. Dislikes are intentionally disabled for user targets. User/profile reaction visibility follows the global logged-out setting.

## Logged-out visitors

**Show reactions only for logged-in users** is enabled by default. In that mode, logged-out visitors see neither reaction buttons nor reaction counts.

If it is disabled, reaction buttons/counts remain visible to logged-out visitors, but clicking a reaction only shows the configured login message. No write request is made and no guest identifier is created. Each post type can inherit or override the global visibility policy and login message.

## Labels and icons

Global labels/icons are fallback defaults. Each enabled public post type can override the Like label, active Like label, Like icon, Dislike label, active Dislike label and Dislike icon independently.

Icons can use one of eight bundled Lucide presets, an image/SVG selected from the WordPress Media Library, or safe inline SVG markup. Media Library choices are stored by attachment ID. Empty custom label fields use the translated built-in label. Custom inline SVG is sanitized and external references are not allowed.

## Extension points

- `cb_likes_user_can_view_target` — let sibling plugins enforce target visibility/access.
- `cb_likes_user_can_like_target` — let sibling plugins add stricter participation rules.
- `cb_likes_current_target` — let a profile/template integration expose a user as the current target.
- `cb_likes_liked` / `cb_likes_unliked` — Like lifecycle actions.
- `cb_likes_disliked` / `cb_likes_undisliked` — Dislike lifecycle actions.
- `cb_likes_reaction_changed` — generic reaction-change action containing old/new state.

## Release tooling

Repository-owned release tooling lives in `tools/`. The release path validates PHP 8.4/8.5 compatibility, translations, conformance, canonical package root `core-blueprint-likes/`, ZIP integrity and SHA256 output. See `tools/README.md`.

## Third-party assets

The curated built-in reaction icons are selected from Lucide and bundled locally. See `THIRD-PARTY-LICENSES.md` for the applicable ISC/MIT notices.
