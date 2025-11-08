# Contributing to Laravel Route Cache

First off, thank you for considering contributing to Laravel Route Cache! It's people like you that make this package better.

## Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the existing issues to avoid duplicates. When you create a bug report, include as many details as possible:

- Use a clear and descriptive title
- Describe the exact steps to reproduce the problem
- Provide specific examples to demonstrate the steps
- Describe the behavior you observed and what you expected
- Include your environment details (PHP version, Laravel version, Redis version)

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion:

- Use a clear and descriptive title
- Provide a detailed description of the suggested enhancement
- Explain why this enhancement would be useful
- List any alternative solutions you've considered

### Pull Requests

#### Branch Naming Convention

Please follow our branch naming convention to keep the repository organized:

| Type | Branch Name | Example | Description |
|------|-------------|---------|-------------|
| **Feature** | `feature/description` | `feature/add-cache-warmup` | New features or enhancements |
| **Bugfix** | `bugfix/description` | `bugfix/fix-redis-connection` | Bug fixes for existing functionality |
| **Hotfix** | `hotfix/description` | `hotfix/critical-security-patch` | Urgent fixes for production issues |
| **Docs** | `docs/description` | `docs/update-readme` | Documentation-only changes |
| **Refactor** | `refactor/description` | `refactor/improve-cache-manager` | Code refactoring without changing functionality |
| **Test** | `test/description` | `test/add-integration-tests` | Adding or updating tests |
| **Chore** | `chore/description` | `chore/update-dependencies` | Maintenance tasks, dependency updates |

#### Development Workflow

1. **Fork the repository** and create your branch from `main`:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make your changes** following our coding standards:
   - Follow PSR-12 coding standards
   - Write meaningful commit messages
   - Add tests for new functionality
   - Update documentation as needed

3. **Test your changes** thoroughly:
   ```bash
   # Run tests
   vendor/bin/phpunit
   
   # Run static analysis
   vendor/bin/phpstan analyse
   
   # Fix code style
   vendor/bin/php-cs-fixer fix
   ```

4. **Commit your changes** with descriptive commit messages:
   ```bash
   git commit -m "Add cache warmup feature for improved performance"
   ```

5. **Push to your fork**:
   ```bash
   git push origin feature/your-feature-name
   ```

6. **Open a Pull Request** using our PR template

#### Pull Request Guidelines

- Fill out the pull request template completely
- Follow the branch naming convention
- Ensure all tests pass
- Update documentation for any changed functionality
- Keep pull requests focused on a single feature or fix
- Write clear, descriptive commit messages
- Reference related issues using `Fixes #123` or `Relates to #456`

#### Code Style

This project follows PSR-12 coding standards. We use PHP CS Fixer to automatically format code:

```bash
# Check code style
vendor/bin/php-cs-fixer fix --dry-run --diff

# Fix code style automatically
vendor/bin/php-cs-fixer fix
```

#### Running Tests

```bash
# Run all tests
vendor/bin/phpunit

# Run tests with coverage
vendor/bin/phpunit --coverage-html coverage

# Run specific test file
vendor/bin/phpunit tests/Unit/CacheManagerTest.php
```

#### Static Analysis

We use PHPStan for static analysis:

```bash
# Run PHPStan
vendor/bin/phpstan analyse

# Run with specific level
vendor/bin/phpstan analyse --level=max
```

## Development Setup

1. **Clone the repository**:
   ```bash
   git clone https://github.com/mojiburrahaman/laravel-route-cache.git
   cd laravel-route-cache
   ```

2. **Install dependencies**:
   ```bash
   composer install
   ```

3. **Start Redis** (required for tests):
   ```bash
   # Using Docker
   docker run -d -p 6379:6379 redis:7-alpine
   
   # Or using local Redis
   redis-server
   ```

4. **Run tests to verify setup**:
   ```bash
   vendor/bin/phpunit
   ```

## Commit Message Guidelines

We follow conventional commit messages:

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types:
- `feat`: A new feature
- `fix`: A bug fix
- `docs`: Documentation only changes
- `style`: Code style changes (formatting, missing semi colons, etc)
- `refactor`: Code refactoring
- `perf`: Performance improvements
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

### Examples:
```bash
feat(cache): add cache warmup command

Implements a new artisan command to pre-warm the cache
with commonly accessed routes.

Closes #123
```

```bash
fix(redis): resolve connection timeout issue

Fixed Redis connection pooling that was causing timeout
errors under high load.

Fixes #456
```

## Review Process

1. All pull requests require at least one approval from a maintainer
2. CI/CD checks must pass (tests, code style, static analysis)
3. Documentation must be updated for user-facing changes
4. Breaking changes require discussion and approval

## Getting Help

- 📖 Check the [README](README.md) and [documentation](docs/)
- 💬 Open a [Discussion](../../discussions) for questions
- 🐛 Open an [Issue](../../issues) for bugs
- 📧 Email: contact@mojiburrahaman.dev

## Recognition

Contributors will be recognized in:
- The [CHANGELOG](CHANGELOG.md) for each release
- The repository's contributors list
- Special thanks in release notes for significant contributions

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

Thank you for contributing! 🎉

