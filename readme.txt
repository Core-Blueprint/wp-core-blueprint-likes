=== Core Blueprint Likes ===
Contributors: coreblueprint
Tags: likes, reactions, privacy, users, engagement
Requires at least: 7.0
Requires PHP: 8.4
Stable tag: 1.0.0-rc1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Privacy-first account-based likes and optional dislikes for WordPress posts and users.

== Description ==

Core Blueprint Likes adds account-based Like reactions and optional Dislike reactions to selected public WordPress content types. Optional user-profile Likes are also supported.

Guest reactions are never stored and the plugin does not use IP addresses, browser fingerprints, device identifiers or location data. Logged-out visitors can either see no reaction UI or receive a configurable login notice.

Likes stores one normalized reaction row per user and target. Like and Dislike are mutually exclusive states.

The plugin integrates with the WordPress personal data exporter and eraser.

Core Blueprint Base is required. Likes uses public Base settings, UI and extension contracts.

Bricks support is optional. When Bricks is active, Likes provides dedicated Like and Dislike elements, dynamic data, conditions and Query Loops. Likes remains builder-agnostic.

The plugin does not send telemetry or tracking data to external services.

== Installation ==

1. Install and activate Core Blueprint Base.
2. Install and activate Core Blueprint Likes.
3. Open Core Blueprint > Extensions > Community > Likes.
4. Select the post types that may receive reactions.
5. Configure logged-out visibility, optional dislikes and presentation labels/icons.

== Privacy ==

Likes stores account-based reaction records containing the WordPress user ID, target type, target ID, reaction state and timestamps.

The plugin does not store guest reactions, IP addresses, browser fingerprints, device identifiers or location data.

Personal reaction data is exposed through the WordPress personal data exporter and can be removed through the WordPress personal data eraser.

== Frequently Asked Questions ==

= Can logged-out visitors react? =

No. Reactions are account-based. Logged-out visitors may see a configurable login notice, but no guest reaction is stored.

= Can users dislike other users? =

No. Optional user targets support Likes only. Users also cannot Like themselves.

= Is Bricks required? =

No. Bricks is an optional presentation adapter.

= Does Likes send data to external services? =

No. Likes does not include telemetry, tracking or external requests.

== Changelog ==

= 1.0.0-rc1 =
* First public release candidate.
* Adds account-based Like and optional Dislike reactions.
* Adds post and optional user targets.
* Adds WordPress privacy exporter and eraser support.
* Adds optional Bricks elements, dynamic data, conditions and Query Loops.
* Bundles reviewed Dutch, German, French, Spanish, Italian and European Portuguese translations.
