#!/usr/bin/env bats
#
# Unit Tests — Package Structure
# Verifies required files are present and package is correctly configured.
#

load '../helpers/test_helper'

setup() {
    setup_test_env
}

teardown() {
    teardown_test_env
}

# ============================================================================
# composer.json
# ============================================================================

@test "composer.json exists" {
    assert_file_exists "${PROJECT_ROOT}/composer.json"
}

@test "composer.json has library type" {
    assert_file_contains "${PROJECT_ROOT}/composer.json" '"type": "library"'
}

@test "composer.json requires php ^8.4" {
    run grep -q '"php":' "${PROJECT_ROOT}/composer.json"
    [ "$status" -eq 0 ]
}

@test "composer.json requires laravel-dev-tools" {
    assert_file_contains "${PROJECT_ROOT}/composer.json" '"zairakai/laravel-dev-tools"'
}

# ============================================================================
# Source
# ============================================================================

@test "src/ directory exists" {
    assert_dir_exists "${PROJECT_ROOT}/src"
}

@test "package service provider exists" {
    assert_file_exists "${PROJECT_ROOT}/src/LaravelAuthServiceProvider.php"
}

@test "package config exists" {
    assert_file_exists "${PROJECT_ROOT}/config/laravel-auth.php"
}

@test "package migration exists" {
    assert_dir_exists "${PROJECT_ROOT}/database/migrations"
}

# ============================================================================
# CI / Tooling
# ============================================================================

@test ".gitlab-ci.yml exists" {
    assert_file_exists "${PROJECT_ROOT}/.gitlab-ci.yml"
}

@test ".gitlab-ci.yml references laravel-dev-tools pipeline" {
    assert_file_contains "${PROJECT_ROOT}/.gitlab-ci.yml" "pipeline-php-package.yml"
}

@test "Makefile exists" {
    assert_file_exists "${PROJECT_ROOT}/Makefile"
}

@test "phpstan.neon exists" {
    assert_file_exists "${PROJECT_ROOT}/phpstan.neon"
}

@test "config/dev-tools/ directory exists" {
    assert_dir_exists "${PROJECT_ROOT}/config/dev-tools"
}

@test "config/dev-tools/insights.php exists" {
    assert_file_exists "${PROJECT_ROOT}/config/dev-tools/insights.php"
}

@test ".editorconfig exists" {
    assert_file_exists "${PROJECT_ROOT}/.editorconfig"
}
