#!/usr/bin/env bash
set -euo pipefail

tests=(
  tests/admin_button_system_test.php
  tests/admin_view_booking_page_test.php
  tests/admin_shipping_permission_helper_test.php
  tests/booking_bar_soap_notice_test.php
  tests/booking_support_helper_test.php
  tests/booking_sensitive_action_permission_test.php
  tests/cancel_parcel_workflow_test.php
  tests/migration_013_idempotence_test.php
  tests/move_parcel_workflow_test.php
  tests/parcel_workflow_integration_test.php
  tests/phase1_parcel_support_test.php
  tests/phase2_shipping_permission_foundation_test.php
  tests/phase3_arrivals_shipping_workflow_test.php
  tests/arrivals_lifecycle_test.php
  tests/phase5_passwordless_auth_test.php
  tests/passwordless_otp_input_test.php
  tests/passwordless_resend_code_test.php
  tests/phone_helpers_test.php
  tests/phone_input_partial_test.php
  tests/shipping_status_workflow_helper_test.php
)

for test_file in "${tests[@]}"; do
  php "$test_file"
done

node --check assets/admin/custom/js/admin_script.js
node --check assets/users/js/booking.js
node --check assets/users/js/profile_auth.js
node --check assets/website/js/home.js

php -l application/controllers/Admin.php >/dev/null
php -l application/controllers/Profile.php >/dev/null
php -l application/controllers/Shipping.php >/dev/null
php -l application/controllers/User_login.php >/dev/null
php -l application/helpers/app_helper.php >/dev/null
php -l application/helpers/email_helper.php >/dev/null
php -l application/models/Auth_challenge_model.php >/dev/null
php -l application/models/Booking_action_log_model.php >/dev/null
php -l application/models/Bookings_model.php >/dev/null
php -l application/models/ajax/travellers/Arrivals_ajax.php >/dev/null
php -l application/views/admin/bookings/modal/cancel_parcel.php >/dev/null
php -l application/views/admin/bookings/modal/move_parcel.php >/dev/null
php -l application/views/admin/travellers/arrival_profile.php >/dev/null

echo "Phase update verification passed."
