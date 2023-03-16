# Changelog

## [Unreleased] - TBA
### Fixed
### Added
### Changed
### Removed

## [0.2.0] - 2022-03-16
### Fixed
### Added
* Feature: Handle name resolving with PHP-Parser's NameResolver by @eliashaeussler in https://github.com/composer-unused/symbol-parser/pull/92
### Changed
### Removed

## [0.1.13] - 2022-03-16
### Fixed
* Add test cases to cover #66 by @icanhazstring in https://github.com/composer-unused/symbol-parser/pull/90
* Fix: Match short namespace symbols by @eliashaeussler in https://github.com/composer-unused/symbol-parser/pull/91
### Added
### Changed
### Removed

## [0.1.12] - 2023-03-10
### Fixed
* Ignore non existing files/dirs by @icanhazstring in https://github.com/composer-unused/symbol-parser/pull/88
* Add test cases to avoid undefined property calls by @icanhazstring in https://github.com/composer-unused/symbol-parser/pull/89
### Added
### Changed
### Removed

## [0.1.11] - 2022-12-22
### Fixed
### Added
* feat: add support for excludedDirs into FileSymbolLoader by @simPod in https://github.com/composer-unused/symbol-parser/pull/74
### Changed
### Removed

## [0.1.10] - 2022-10-07
### Fixed
* Fix composer.json constraint for symfony/finder by @Jean85 in https://github.com/composer-unused/symbol-parser/pull/44
* Return empty symbol list when reflection could not be loaded by @icanhazstring in https://github.com/composer-unused/symbol-parser/pull/63
### Added
* Add AnnotationStrategy by @LeoVie in https://github.com/composer-unused/symbol-parser/pull/62
### Changed
### Removed

## [0.1.9] - 2022-05-03
### Fixed
* Add interface recognition by @samuelnogueira in https://github.com/composer-unused/symbol-parser/pull/42
### Added
### Changed
### Removed

## [0.1.8] - 2022-03-09
### Fixed
### Added
* Add ability to merge symbol names into FQN for consumed symbols by @icanhazstring in https://github.com/composer-unused/symbol-parser/pull/26
* Resolve #11: Add TypedAttributeStrategy by @icanhazstring in https://github.com/composer-unused/symbol-parser/pull/33
* Add FullQualifiedParameterStrategy by @icanhazstring in https://github.com/composer-unused/symbol-parser/pull/34
### Changed
### Removed

## [0.1.7] - 2022-02-15
### Fixed
- Fix issue with psr4/0 when having array of paths
### Added
### Changed
### Removed

## [0.1.6] - 2022-02-08
### Fixed
- Fix `FileContentProvider` to throw an exception when the file does not exist
- Add `try/catch` to `FileSymbolProvider` to continue working instead of crashing if a file could not be parsed
- Hotfix issue when attempting to parse an invalid php file, this will be caught silently and the file will not be parsed (will get reported in the future)
### Added
### Changed
### Removed

## [0.1.5] - 2022-02-03
### Fixed
### Added
### Changed
- Allow `composer-unused/contracts` in version `0.2`
### Removed

## [0.1.4] - 2022-01-05
### Added
- Added `FunctionInvocationStrategy` to find consumed symbols by function invocation
- Added possibility to parse symbols from `define()`
- Added `ConstStrategy` to parse consumed constants
- Added support for `symfony/finder` up until version `^6.0`
- Added symlink support for `autoload.files`
- Added dependency to `composer-unused/contracts`
### Changed
### Removed
- Dropped support for php `7.3`

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
