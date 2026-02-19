# Yak Shaver CB GJ Guest Access Limiter — Joomla System Plugin

A Joomla 5 system plugin that enforces selective guest access to **Community Builder GroupJive** content.

## Purpose

CB GroupJive is a group management system built on Community Builder. By default, guests (unauthenticated visitors) can browse group listings, group detail pages, and all group-related content as long as the associated category has a "Public" access level.

This plugin adds a finer layer of control: **guests may view group events from non-private groups, but are blocked from all other group content** — such as the group directory, individual group pages, group members, files, forums, and the About section.

## The Problem It Solves

CB GroupJive's built-in access system works at the **category level**. Setting categories to "Registered" would block guest access to groups — but it would also block events, since events belong to groups that belong to categories. There is no built-in option to say "allow events but block everything else" for guests.

This plugin bridges that gap without modifying any CB or GroupJive core files.

## What Guests Can and Cannot See

| Content | Guest Access |
|---------|-------------|
| Group directory (all groups) | ❌ Blocked — redirected to login |
| Group categories listing | ❌ Blocked — redirected to login |
| Individual group pages | ❌ Blocked — redirected to login |
| Group members list | ❌ Blocked — redirected to login |
| Group files | ❌ Blocked — redirected to login |
| Group forums | ❌ Blocked — redirected to login |
| Group about/description pages | ❌ Blocked — redirected to login |
| **Events from non-private groups** | ✅ Allowed |
| Events from private groups | ❌ Blocked (by GroupJive itself) |

## How It Works

This plugin is **Layer 1** of a two-plugin design:

1. **`plg_system_cbgjguestaccess`** (this plugin) — subscribes to Joomla's `onAfterRoute` event and redirects guests to the CB login page when they visit any GroupJive URL except events.
2. **`plg_system_cbgjvisibility`** (deployed separately) — strips group-identifying metadata (host name, group link, attendee count) from the rendered event page for guests.

Both plugins must be enabled. Neither alone is sufficient.

The plugin uses an **allowlist**: only the `cbgroupjiveevents` plugin element is permitted for guests. All other `cbgroupjive*` elements are redirected. Non-GroupJive CB plugins are not affected.

## Requirements

- Joomla 5.x
- PHP 8.1+
- Community Builder 2.x
- CB GroupJive plugin family (`cbgroupjive`, `cbgroupjiveevents`)
- `plg_system_cbgjvisibility` (companion plugin, deployed separately)

## Installation

1. Build the ZIP package (Git Bash):

   ```bash
   make dist
   ```

   This creates `installation/plg_system_cbgjguestaccess-v1-0-0.zip` containing the plugin files and LICENSE. Use `make info` to inspect plugin metadata and `make clean` to remove the built ZIP.

2. Install via **Joomla Administrator** > Extensions > Manage > Install > Upload Package File.

3. In Plugin Manager, set the ordering of "System - Yak Shaver CB GJ Guest Access Limiter" **above** the "System - Community Builder" plugin (CB's ordering + 10 or more).

4. Enable the plugin.

### Post-install verification

- Verify CB GroupJive Events > Settings > "Events Access" is set to **Public** (level 1).
- Verify `plg_system_cbgjvisibility` is enabled.
- Set the GroupJive tab access to "Registered" in CB > Tab Manager (companion step).

## Joomla Update Server

The file `plg_system_cbgjguestaccess.update.xml` is the update manifest for Joomla's built-in extension updater. Host it at a stable public URL and register that URL under **Extensions → Manage → Update Sites** to enable one-click updates from the Joomla back-end.

The `make dist` target rebuilds the ZIP and automatically rewrites the version, download URL, and SHA256 checksum in the manifest, so the file stays accurate after every release.

## Status

> **Implementation** — Plugin code complete. See [docs/execution_plan.md](docs/execution_plan.md) for the full plan and acceptance tests.
