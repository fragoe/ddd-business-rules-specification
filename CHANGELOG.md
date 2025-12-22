# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial implementation of Specification Pattern for DDD
- `BusinessRule` interface with `isSatisfiedBy()` method
- `CompositeBusinessRule` abstract class with fluent API
- `AndBusinessRule` for AND logic composition
- `OrBusinessRule` for OR logic composition
- `NotBusinessRule` for NOT logic (negation)
- `xor()` method for XOR logic
- Concrete return types for all composition methods
- Variadic constructors for `AndBusinessRule` and `OrBusinessRule`
- Validation requiring at least one rule in composite constructors
- Short-circuit evaluation for performance
- Comprehensive test suite
- Documentation:
  - README.md with usage examples
  - CLAUDE.md for AI development guide
  - Static analysis approach documentation
  - Database query integration architecture
  - Package ecosystem overview
- Proof-of-concept analyzer tool in `tools/`
- MIT License

### Changed
- Renamed classes from `Conjunction/Disjunction/Negation` to `And/Or/Not` naming convention
- Property naming from `$specification` to `$businessRule` for consistency
- Test namespace from `fragoe\DDDBusinessRules` to `fragoe\DDDBusinessRules\Tests`

### Fixed
- PSR-4 namespace compliance in tests
- Comment accuracy in composite rules
- Snake_case to camelCase in test parameters

## [1.0.0] - TBD

### Release Notes
First stable release of the DDD Business Rules library.

**Core Features:**
- Pure domain layer implementation
- Zero infrastructure dependencies
- Full Specification Pattern support
- Fluent composition API
- Type-safe with concrete return types
- Performance optimized with short-circuit evaluation

**Companion Packages:**
- `fragoe/ddd-business-rules-analyzer` - Static analysis and visualization
- `fragoe/ddd-business-rules-doctrine` - Doctrine ORM/DBAL bridge

[Unreleased]: https://github.com/fragoe/ddd-business-rules/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/fragoe/ddd-business-rules/releases/tag/v1.0.0
