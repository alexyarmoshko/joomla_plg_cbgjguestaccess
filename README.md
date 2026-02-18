# CB GroupJive Guest Access — Joomla System Plugin

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

## Requirements

- Joomla 5.x
- Community Builder 2.x
- CB GroupJive plugin family (`cbgroupjive`, `cbgroupjiveevents`)

## Status

> **Planning** — See [docs/planning_spike.v1.md](docs/planning_spike.v1.md) for analysis and design notes.
