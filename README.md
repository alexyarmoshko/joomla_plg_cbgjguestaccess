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

1. Download the ZIP from the [GitHub release](https://github.com/alexyarmoshko/joomla_plg_cbgjguestaccess/releases), or build one as described in [Build](#build).

2. Install via **Joomla Administrator** > Extensions > Manage > Install > Upload Package File.

3. In Plugin Manager, give "System - Yak Shaver CB GJ Guest Access Limiter" a **higher** ordering number than "System - Community Builder" (CB's ordering + 10 or more), so it runs after CB's own login and consent redirects.

4. Enable the plugin.

### Post-install verification

- Verify CB GroupJive Events > Settings > "Events Access" is set to **Public** (level 1).
- Verify `plg_system_cbgjvisibility` is enabled.
- Set the GroupJive tab access to "Registered" in CB > Tab Manager (companion step).

## Updates

The manifest's `<updateservers>` points at `manifests/plg_system_cbgjguestaccess.update.xml` in the public [joomla_update_system](https://github.com/alexyarmoshko/joomla_update_system) repository, so Joomla's built-in updater offers new releases from **System → Update → Extensions**. Joomla records the update site when the plugin is installed.

Version 1.0.0 shipped without `<updateservers>`. Sites running it will not see updates until 1.0.1 has been installed by hand once; after that the updater takes over.

## Build

Run from a POSIX shell at the repository root: Linux, macOS, or on Windows Git Bash or WSL. The build needs `make`, `git`, and PHP with the `zlib` and `SimpleXML` extensions. The packager is `tools/jzip.php`, a vendored copy of `jzip`, a deterministic ZIP writer shared across these extensions. If that file is missing, `dist_dev` falls back to `zip` with a warning that the package is not reproducible, and `dist_release` refuses to run.

| Target | What it does |
|---|---|
| `make info` | Print the version, the shipped file list, and every path the build reads or writes. |
| `make lint` | `php -l` every shipped PHP file and the packager; check that the manifest and the descriptor template parse as XML. |
| `make test` | No automated harness exists; verification is manual on a test site. The target exists so `release` has a uniform gate. |
| `make dist_dev` | Build a throwaway package from the **working tree** into `installation/dev/` for a test site. It strips `<updateservers>` from the packaged manifest and generates no descriptor. |
| `make release` | Tag the version. Refuses a dirty tree, an existing tag, or a version with no `## <version>` heading in `docs/RELEASE.md`, then runs `test` and `lint`. |
| `make dist_release` | Build the publishable package from **the tag** into `installation/release/` and write the update descriptor beside it. Requires a clean tree with `HEAD` at the tag. |
| `make clean` | Remove `build/` and both package directories. |

### Why the package is reproducible

`dist_release` builds from `git archive` of the tag, not the working tree, and `jzip` stamps every entry with the tag's commit date and writes every header field itself. Working-tree line endings, file mtimes and the host bits a `zip` binary records therefore never reach the archive. Rebuilding a tag on any machine gives the same bytes, and so the same `<sha256>` the descriptor claims. A checkout with `core.autocrlf=true` was checked to produce the same archive as an LF checkout.

Entries are deflated at level 9 by PHP's zlib. That output has matched across zlib 1.2.12 and 1.3, but a different implementation such as zlib-ng would compress differently. If a third party must re-derive the hash without that assumption, set `ZIP_LEVEL := 0` in the Makefile; the archive is about 2.5 times larger.

Dev packages are stamped with the HEAD commit date and built from the working tree as it is, so two dev builds of the same commit with unchanged files are identical. Any new commit restamps them, even one that changes no shipped file.

### Cutting a release

1. Bump `<version>` in `cbgjguestaccess.xml` and in `plg_system_cbgjguestaccess.update.xml`, add a `## <version> (<date>)` heading to `docs/RELEASE.md`, and commit.
2. `make release` validates and creates the tag. The tag is the bare version (`1.0.1`, not `v1.0.1`), because the download URL embeds it. `tag.gpgsign` is honoured, so a signing setup needs a terminal with gpg-agent ready.
3. `make dist_release` writes `installation/release/plg_system_cbgjguestaccess-v<X-Y-Z>.zip` and `installation/release/plg_system_cbgjguestaccess.update.xml`.
4. Push the tag and upload **that exact ZIP** as the GitHub release asset.
5. Copy the generated descriptor over `manifests/plg_system_cbgjguestaccess.update.xml` in `joomla_update_system`, then commit and tag there.

Do steps 4 and 5 in that order. A descriptor published before its asset points at a download that returns 404, and one published late hides the release from every site.

The tracked `plg_system_cbgjguestaccess.update.xml` is a template: its `<sha256>` is 64 zeros, and `dist_release` refuses to run if that placeholder has been replaced. The real descriptor is a build artifact and is not committed here, because it holds the checksum of a package built from a tag in this repository.

Reproducibility starts with 1.0.1. Tag `1.0.0` was packaged by the old `zip`-based build, so rebuilding it gives a different archive from the published one. Leave 1.0.0's asset and checksum as they are.

## Status

> **Release** — `1.0.1` released on 2026-10-10. It is the first version that receives updates through Joomla's updater; see [Updates](#updates). See [docs/RELEASE.md](docs/RELEASE.md) for release notes.
