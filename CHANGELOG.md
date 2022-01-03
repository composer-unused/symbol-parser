# Changelog

## [Unreleased] - TBA
### Added
- Added `FunctionInvocationStrategy` to find consumed symbols by function invocation
- Added possibility to parse symbols from `define()`
- Added `ConstStrategy` to parse consumed constants
- Added support for `symfony/finder` up until version `^6.0`
- Added symlink support for `autoload.files`
### Changed
### Removed

## [0.1.3] - 2021-08-02
### Fixed
- Fix issue with parsed include expression where there could be concat operations
  resulting in wrong path usages for included files to parse

## [0.1.2] - 2021-08-01
### Added
- Added missing `symfony/finder` dependency

## [0.1.1] - 2021-08-01
### Fixed
- Fixed issue with `require` or `include` statements

## [0.1.0] - 2021-02-12
Initial release
