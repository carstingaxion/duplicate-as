# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased](https://github.com/carstingaxion/duplicate-as/compare/0.3.1...HEAD)

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
