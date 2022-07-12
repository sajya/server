# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## 5.0.2 - 2022-07-12

### Fixed

- Don't use a queue for pending requests

## 5.0.1 - 2022-04-25

### Fixed

- Set $data of InvalidRequestException [#32](https://github.com/sajya/server/issues/32)

## 5.0.0 - 2022-02-10

### Changed

- Minimum version of Laravel 9.x

## 4.0.6 - 2022-01-24

### Fixed

- Problem with request with big payload [#27](https://github.com/sajya/server/issues/27)

## 4.0.5 - 2022-01-21

### Changed

- Parser: `isAssociative()` optimization [#28](https://github.com/sajya/server/pull/28)
- Added static method `makeFromResult()` [#30](https://github.com/sajya/server/pull/30)

## 4.0.4 - 2021-12-26

### Added

- Tests for PHP 8.1
- New `terminate` method for to execute pending tasks

## 4.0.3 - 2021-10-22

### Fixed

- Status code is not integer set the code to -1 [#22](https://github.com/sajya/server/pull/22)

## 4.0.2 - 2021-08-20

### Fixed

- Cyrillic display in generated documentation [#20](https://github.com/sajya/server/issues/20)

## 4.0.1 - 2021-08-17

### Changed

- Return nothing on notifications [#19](https://github.com/sajya/server/issues/19)

## 4.0.0 - 2021-08-17

### Added

- Route binding [#18](https://github.com/sajya/server/pull/18)

### Changed

- Result for call in batchmode combined with notification [#19](https://github.com/sajya/server/issues/19)

## 3.0.0 - 2021-04-02

### Added

- Command for generating documentation

### Changed

- Procedures no longer use controller traits

## 2.4.0 - 2021-02-12

### Added

- Middleware for compressing the response

## 2.3.0 - 2021-02-05

### Added

- Trait to make testing easier

## 2.2.0 - 2021-01-13

### Added

- Customization delimiter method [#11](https://github.com/sajya/server/issues/11)
- Support for strings as identifiers [#11](https://github.com/sajya/server/issues/11)

## 2.1.0 - 2020-11-23

### Added
- Support PHP 8.0

## 2.0.0 - 2020-09-08

### Added
- Support Laravel 8

## 1.1.0 - 2020-08-17

### Added
- Added extended output in debug mode [#5](https://github.com/sajya/server/pull/5)

### Fixed
- Fixed correct header output [#5](https://github.com/sajya/server/pull/5)

## 0.0.2 - 2020-03-23

### Fixed
- Replace `Collection` to `Request` for make procedure


## 0.0.1 - 2020-03-22

### Added
- initial release
