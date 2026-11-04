# First release and package verification

This guide assumes the package source is in a local directory named `laravel-api-response` and that you have created an empty GitHub repository at `https://github.com/ITMOSOFT/laravel-api-response`.

## 1. Run local checks before committing

From the package root:

```bash
composer validate --no-check-publish
composer install --prefer-dist --no-interaction
composer test
```

The package currently contains 14 PHPUnit test methods. The test suite uses SQLite in-memory through Orchestra Testbench.

## 2. Test both supported Laravel versions locally

The CI matrix maps Laravel 12 to Testbench 10 and Laravel 13 to Testbench 11. Run each combination:

```bash
# Laravel 12
composer update "orchestra/testbench:10.*" --with-all-dependencies --prefer-dist --no-interaction
composer show laravel/framework
composer show orchestra/testbench
composer test

# Laravel 13
composer update "orchestra/testbench:11.*" --with-all-dependencies --prefer-dist --no-interaction
composer show laravel/framework
composer show orchestra/testbench
composer test
```

Confirm the displayed framework major matches the section being tested. These commands resolve the appropriate framework version through Testbench; they may update `composer.lock` between runs.

## 3. Initialize Git and push the first commit

If this directory is not already a Git repository:

```bash
git init
git branch -M main
git add .
git commit -m "feat: initial Laravel API response package"
git remote add origin git@github.com:ITMOSOFT/laravel-api-response.git
git push -u origin main
```

If the repository is already initialized and has the correct remote, use:

```bash
git status --short
git diff --check
git add .
git commit -m "feat: prepare v1.0.0 release"
git push origin main
```

Do not add a second `origin` remote if one already exists. Check it with `git remote -v` first.

## 4. Verify GitHub Actions on main

Open:

`https://github.com/ITMOSOFT/laravel-api-response/actions`

The `Tests` workflow should pass both jobs:

- Laravel 12 / PHP 8.3 / Testbench 10
- Laravel 13 / PHP 8.3 / Testbench 11

Do not create the release tag until both jobs pass. If a job fails, fix the issue, commit, push, and wait for the new run.

## 5. Create and push the first version tag

After the main-branch CI is green:

```bash
git checkout main
git pull --ff-only origin main
git tag -a v1.0.0 -m "Release v1.0.0"
git push origin v1.0.0
```

The `Release` workflow runs the same Laravel 12/13 test matrix against the tagged commit. Once both jobs pass, it creates the GitHub Release and generates release notes.

Monitor the workflow at:

`https://github.com/ITMOSOFT/laravel-api-response/actions`

Verify the release at:

`https://github.com/ITMOSOFT/laravel-api-response/releases`

If the tag was pushed but release tests fail, fix the code on `main`, rerun CI, and create a new patch tag (for example `v1.0.1`) rather than moving an already-published tag.

## 6. Publish on Packagist

A GitHub Release does not by itself guarantee Packagist registration. If the package is not already registered:

1. Sign in to https://packagist.org/.
2. Submit `https://github.com/ITMOSOFT/laravel-api-response`.
3. Configure the Packagist GitHub webhook/integration so future tags trigger updates.
4. Confirm Packagist lists version `v1.0.0`.

## 7. Test installation from a Laravel application

In a separate Laravel 12 application, run:

```bash
composer require itmosoft/laravel-api-response:^1.0
php artisan about
php artisan vendor:publish --tag=api-response-config
```

Then add a temporary route and exercise a response:

```php
use Illuminate\Support\Facades\Route;
use ItmoSoft\ApiResponse\Facades\ApiResponse;

Route::get('/api/package-smoke-test', function () {
    return ApiResponse::success(['package' => 'laravel-api-response']);
});
```

Request `/api/package-smoke-test` and confirm HTTP 200 with the documented JSON envelope. Repeat the installation smoke test in a Laravel 13 application. Remove the temporary route after verification.

## Release checklist

- [ ] Composer validation passes.
- [ ] PHPUnit suite passes locally.
- [ ] Laravel 12 / Testbench 10 passes.
- [ ] Laravel 13 / Testbench 11 passes.
- [ ] Main branch is pushed and both CI jobs pass.
- [ ] Annotated `v1.0.0` tag is pushed.
- [ ] Tag-triggered release workflow passes.
- [ ] GitHub Release is created.
- [ ] Packagist lists `v1.0.0`.
- [ ] Composer installation and API response smoke tests pass in Laravel 12 and 13 applications.
