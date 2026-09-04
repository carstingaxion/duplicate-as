# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased](https://github.com/carstingaxion/duplicate-as/compare/0.5.0...HEAD)

### Fixed

- Duplicated GatherPress events now appear in GatherPress' own event queries immediately, instead of only after the duplicate is opened and saved again.

## [0.5.0](https://github.com/carstingaxion/duplicate-as/compare/0.4.1...0.5.0) - 2026-09-04

### 🚀 Added

- Update hook docs automatically ([#113](https://github.com/carstingaxion/duplicate-as/pull/113))
- Write the GatherPress event row after a duplication ([#100](https://github.com/carstingaxion/duplicate-as/pull/100))

### Dependency Updates & Maintenance

- Bump simple-git from 3.33.0 to 3.36.0 ([#52](https://github.com/carstingaxion/duplicate-as/pull/52))
- Bump tmp from 0.2.5 to 0.2.7 ([#59](https://github.com/carstingaxion/duplicate-as/pull/59))
- Bump immutable from 5.1.4 to 5.1.9 ([#77](https://github.com/carstingaxion/duplicate-as/pull/77))
- Bump websocket-driver from 0.7.4 to 0.7.5 ([#83](https://github.com/carstingaxion/duplicate-as/pull/83))
- Bump brace-expansion from 1.1.12 to 1.1.18 ([#98](https://github.com/carstingaxion/duplicate-as/pull/98))
- Bump fast-xml-parser from 5.5.5 to 5.11.1 ([#106](https://github.com/carstingaxion/duplicate-as/pull/106))
- Bump svgo from 3.3.2 to 3.3.5 ([#107](https://github.com/carstingaxion/duplicate-as/pull/107))
- Bump ip-address from 10.1.0 to 10.7.0 ([#109](https://github.com/carstingaxion/duplicate-as/pull/109))
- Bump postcss from 8.5.6 to 8.5.28 ([#118](https://github.com/carstingaxion/duplicate-as/pull/118))
- Bump postcss-selector-parser ([#119](https://github.com/carstingaxion/duplicate-as/pull/119))
- Bump fast-xml-builder from 1.1.3 to 1.3.1 ([#112](https://github.com/carstingaxion/duplicate-as/pull/112))
- Bump fast-uri from 3.1.0 to 3.1.7 ([#114](https://github.com/carstingaxion/duplicate-as/pull/114))
- Bump nanoid from 3.3.11 to 3.3.18 ([#115](https://github.com/carstingaxion/duplicate-as/pull/115))
- Bump follow-redirects from 1.15.11 to 1.16.0 ([#39](https://github.com/carstingaxion/duplicate-as/pull/39))
- Bump @babel/plugin-transform-modules-systemjs from 7.28.5 to 7.29.8 ([#96](https://github.com/carstingaxion/duplicate-as/pull/96))
- Bump the wordpress-packages group across 1 directory with 2 updates ([#26](https://github.com/carstingaxion/duplicate-as/pull/26))
- Bump the composer group across 1 directory with 2 updates ([#101](https://github.com/carstingaxion/duplicate-as/pull/101))
- Bump wp-phpunit/wp-phpunit from 7.0.0 to 7.1.0 ([#105](https://github.com/carstingaxion/duplicate-as/pull/105))
- Bump actions/checkout from 6 to 7 ([#70](https://github.com/carstingaxion/duplicate-as/pull/70))

## [0.4.1](https://github.com/carstingaxion/duplicate-as/compare/0.4.0...0.4.1) - 2026-07-05

* No changes, bur Re-BUILD

## [0.4.0](https://github.com/carstingaxion/duplicate-as/compare/0.3.2...0.4.0) - 2026-06-02

- Update hook docs automatically ([#61](https://github.com/carstingaxion/duplicate-as/pull/61))
- Assign GatherPress shadow taxonomy term from source to target post ([#60](https://github.com/carstingaxion/duplicate-as/pull/60))

### Dependency Updates & Maintenance

- Update deps ([#63](https://github.com/carstingaxion/duplicate-as/pull/63))
- Bump actions/checkout from 4 to 6 ([#6](https://github.com/carstingaxion/duplicate-as/pull/6))
- Bump wp-phpunit/wp-phpunit from 6.9.4 to 7.0.0 ([#57](https://github.com/carstingaxion/duplicate-as/pull/57))

## [0.3.2](https://github.com/carstingaxion/duplicate-as/compare/0.3.1...0.3.2) - 2026-05-21

- https://github.com/carstingaxion/duplicate-as/commit/8d3489e6bcadf6a2e6503a56addbff2947702947 Enable default support for `gatherpress_play` from the [gatherpress-productions](https://github.com/carstingaxion/gatherpress-productions) plugin

## [0.3.1](https://github.com/carstingaxion/duplicate-as/compare/0.3.0...0.3.1) - 2026-03-22

* Ignore tests from distribution

## [0.3.0](https://github.com/carstingaxion/duplicate-as/compare/0.2.0...0.3.0) - 2026-03-22

- Add php tests & bootstrap ([#19](https://github.com/carstingaxion/duplicate-as/pull/19))
- Update hook docs automatically ([#18](https://github.com/carstingaxion/duplicate-as/pull/18))

### 🚀 Added

- Add de_DE translation and fix minor (mainly linting) issues  ([#17](https://github.com/carstingaxion/duplicate-as/pull/17))

### Dependency Updates & Maintenance

- Bump akirk/extract-wp-hooks from 1.3.0 to 1.4.0 ([#12](https://github.com/carstingaxion/duplicate-as/pull/12))

## [0.2.0](https://github.com/carstingaxion/duplicate-as/compare/0.1.0...0.2.0) - 2026-01-21

- Update hook docs automatically ([#4](https://github.com/carstingaxion/duplicate-as/pull/4))
- Update hook docs automatically ([#3](https://github.com/carstingaxion/duplicate-as/pull/3))
- Use extract-hooks ([#2](https://github.com/carstingaxion/duplicate-as/pull/2))

## 0.1.0

Initial release

- Works on every post type, because its build based on post type supports
- Duplicate posts and pages instantly from the Editor Sidebar or the Admin List Tables
- Complete duplication post-data, taxonomy terms and postmeta-data (incl. featured image)
- Can transform posts to different post types when configured
- New duplicates are created as drafts
- Loading states and graceful error messages if something goes wrong
- Only visible to users with appropriate capabilities
- Proper ARIA labels and WordPress admin integration
- Multiple filter and action hooks for customization
