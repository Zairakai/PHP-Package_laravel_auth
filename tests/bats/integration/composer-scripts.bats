#!/usr/bin/env bats
#
# Integration Tests — Composer Scripts
# Validates that the dev tooling environment is correctly installed.
#

load '../helpers/test_helper'

setup() {
    setup_test_env
}

teardown() {
    teardown_test_env
}

# ============================================================================
# Vendor / Dependencies
# ============================================================================

@test "vendor/ directory exists (composer install was run)" {
    assert_dir_exists "${PROJECT_ROOT}/vendor"
}

@test "vendor/zairakai/laravel-dev-tools is installed" {
    assert_dir_exists "${PROJECT_ROOT}/vendor/zairakai/laravel-dev-tools"
}

@test "vendor/phpunit/phpunit is installed" {
    assert_dir_exists "${PROJECT_ROOT}/vendor/phpunit/phpunit"
}

@test "vendor/phpstan/phpstan is installed" {
    assert_dir_exists "${PROJECT_ROOT}/vendor/phpstan/phpstan"
}

@test "vendor/laravel/pint is installed" {
    assert_dir_exists "${PROJECT_ROOT}/vendor/laravel/pint"
}

@test "vendor/laravel/fortify is installed" {
    assert_dir_exists "${PROJECT_ROOT}/vendor/laravel/fortify"
}

@test "vendor/laravel/sanctum is installed" {
    assert_dir_exists "${PROJECT_ROOT}/vendor/laravel/sanctum"
}

# ============================================================================
# laravel-dev-tools Scripts
# ============================================================================

@test "phpstan.sh script exists and is executable" {
    local script="${PROJECT_ROOT}/vendor/zairakai/laravel-dev-tools/scripts/phpstan.sh"

    assert_file_exists "$script"
    [ -x "$script" ]
}

@test "cs-check.sh script exists and is executable" {
    local script="${PROJECT_ROOT}/vendor/zairakai/laravel-dev-tools/scripts/cs-check.sh"

    assert_file_exists "$script"
    [ -x "$script" ]
}

@test "test.sh script exists and is executable" {
    local script="${PROJECT_ROOT}/vendor/zairakai/laravel-dev-tools/scripts/test.sh"

    assert_file_exists "$script"
    [ -x "$script" ]
}

# ============================================================================
# PHP Environment
# ============================================================================

@test "php binary is available" {
    run command -v php
    [ "$status" -eq 0 ]
}

@test "phpunit binary is available via vendor" {
    assert_file_exists "${PROJECT_ROOT}/vendor/bin/phpunit"
}

@test "phpstan binary is available via vendor" {
    assert_file_exists "${PROJECT_ROOT}/vendor/bin/phpstan"
}

@test "pint binary is available via vendor" {
    assert_file_exists "${PROJECT_ROOT}/vendor/bin/pint"
}

# ============================================================================
# Generated Config Files
# ============================================================================

@test "Makefile includes dev-tools targets" {
    assert_file_contains "${PROJECT_ROOT}/Makefile" "include"
}

@test "phpstan.neon includes a config source" {
    assert_file_contains "${PROJECT_ROOT}/phpstan.neon" "includes"
}
