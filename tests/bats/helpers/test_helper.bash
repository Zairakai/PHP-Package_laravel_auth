#!/usr/bin/env bash
#
# BATS Test Helpers
#

setup_test_env() {
    export TEST_TEMP_DIR="${BATS_TEST_TMPDIR}/php-package-test-$$"
    mkdir -p "$TEST_TEMP_DIR"

    export PROJECT_ROOT
    PROJECT_ROOT="$(cd "${BATS_TEST_DIRNAME}/../../.." && pwd)"
}

teardown_test_env() {
    if [[ -n "${TEST_TEMP_DIR:-}" ]] && [[ -d "$TEST_TEMP_DIR" ]]; then
        rm -rf "$TEST_TEMP_DIR"
    fi
}

assert_file_exists() {
    local file="$1"

    if [[ ! -f "$file" ]]; then
        echo "ASSERTION FAILED: File does not exist: $file" >&2
        return 1
    fi
}

assert_dir_exists() {
    local dir="$1"

    if [[ ! -d "$dir" ]]; then
        echo "ASSERTION FAILED: Directory does not exist: $dir" >&2
        return 1
    fi
}

assert_file_contains() {
    local file="$1"
    local needle="$2"

    if ! grep -q "$needle" "$file"; then
        echo "ASSERTION FAILED: '$file' does not contain: $needle" >&2
        return 1
    fi
}

assert_output_contains() {
    local needle="$1"

    if [[ ! "$output" =~ $needle ]]; then
        echo "ASSERTION FAILED: Output does not contain: $needle" >&2
        echo "Actual output: $output" >&2
        return 1
    fi
}
