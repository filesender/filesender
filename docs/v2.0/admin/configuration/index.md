---
title: Configuration directives
---

This document is a work in progress. You can [contribute updates](patchdocs/) to the documentation or file [an issue](https://github.com/filesender/filesender/issues) to get the ball rolling on an update.

A note about colours;

* mandatory configuration settings are <span style="background-color:red">marked in red</span>
* sections <span style="background-color:orange">marked in orange</span> need to be double checked.

# Table of contents

---

## General settings

* [admin_email](#admin_email)
* [support_email](#support_email)
* [admin](#admin)
* [site_name](#site_name)
* [force_ssl](#force_ssl)
* [session_cookie_path](#session_cookie_path)
* [default_timezone](#default_timezone)
* [default_language](#default_language)
* [site_url](#site_url)
* [site_logouturl](#site_logouturl)
* [reports_show_ip_addr](#reports_show_ip_addr)
* [admin_can_view_user_transfers_page](#admin_can_view_user_transfers_page)
* [mime_type_regex](#mime_type_regex)
* [mime_type_default](#mime_type_default)
* [service_aup_min_required_version](#service_aup_min_required_version)
* [tmp_path](#tmp_path)
* [site_css](#site_css)
* [site_logo](#site_logo)
* [download_verification_code_enabled](#download_verification_code_enabled)
* [download_verification_code_valid_duration](#download_verification_code_valid_duration)
* [download_verification_code_random_bytes_used](#download_verification_code_random_bytes_used)
* [download_show_download_links](#download_show_download_links)


## Security settings
* [use_strict_csp](#use_strict_csp)
* [header_x_frame_options](#header_x_frame_options)
* [header_add_hsts_duration](#header_add_hsts_duration)
* [owasp_csrf_protector_enabled](#owasp_csrf_protector_enabled)
* [avprogram_list](#avprogram_list)
* [avprogram_max_size_to_scan](#avprogram_max_size_to_scan)
* [crypto_iv_len](#crypto_iv_len)
* [crypto_gcm_max_file_size](#crypto_gcm_max_file_size)
* [crypto_gcm_max_chunk_size](#crypto_gcm_max_chunk_size)
* [crypto_gcm_max_chunk_count](#crypto_gcm_max_chunk_count)
* [crypto_crypt_name](#crypto_crypt_name)
* [upload_crypted_chunk_padding_size](#upload_crypted_chunk_padding_size)
* [upload_crypted_chunk_size](#upload_crypted_chunk_size)
* [cookie_domain](#cookie_domain)
* [rate_limits](#rate_limits) (rate limits for some actions)
* [valid_filename_regex](#valid_filename_regex)


## Backend storage

* [storage_type](#storage_type)
* [storage_filesystem_path](#storage_filesystem_path)
* [storage_filesystem_df_command](#storage_filesystem_df_command)
* [storage_filesystem_file_deletion_command](#storage_filesystem_file_deletion_command)
* [storage_filesystem_tree_deletion_command](#storage_filesystem_tree_deletion_command)
* [storage_usage_warning](#storage_usage_warning)
* [storage_filesystem_hashing](#storage_filesystem_hashing)
* [storage_filesystem_per_day_buckets](#storage_filesystem_per_day_buckets)
* [storage_filesystem_per_hour_buckets](#storage_filesystem_per_hour_buckets)
* [storage_filesystem_per_day_max_age_to_create_directory](#storage_filesystem_per_day_min_age_to_create_directory)
* [storage_filesystem_per_day_min_days_to_clean_empty_directories](#storage_filesystem_per_day_min_days_to_clean_empty_directories)
* [storage_filesystem_per_day_max_days_to_clean_empty_directories](#storage_filesystem_per_day_max_days_to_clean_empty_directories)
* [storage_filesystem_ignore_disk_full_check](#storage_filesystem_ignore_disk_full_check)
* [storage_filesystem_external_script](#storage_filesystem_external_script)
* [cloud_s3_region](#cloud_s3_region)
* [cloud_s3_version](#cloud_s3_version)
* [cloud_s3_endpoint](#cloud_s3_endpoint)
* [cloud_s3_key](#cloud_s3_key)
* [cloud_s3_secret](#cloud_s3_secret)
* [cloud_s3_use_path_style_endpoint](#cloud_s3_use_path_style_endpoint)
* [cloud_s3_bucket](#cloud_s3_bucket)
* [cloud_s3_use_daily_bucket](#cloud_s3_use_daily_bucket)
* [cloud_s3_bucket_prefix](#cloud_s3_bucket_prefix)
* [cloud_s3_bulk_delete](#cloud_s3_bulk_delete)
* [cloud_s3_bulk_size](#cloud_s3_bulk_size)

## Shredding

* [storage_filesystem_shred_path](#storage_filesystem_shred_path)
* [storage_filesystem_file_shred_command](#storage_filesystem_file_shred_command)


## Database

* [db_type](#db_type)
* [db_host](#db_host)
* [db_port](#db_port)
* [db_username](#db_username)
* [db_password](#db_password)
* [db_database](#db_database)
* [db_table_prefix](#db_table_prefix)
* [db_driver_options](#db_driver_options)

## Language and internationalisation

* [lang_browser_enabled](#lang_browser_enabled)
* [lang_url_enabled](#lang_url_enabled)
* [lang_userpref_enabled](#lang_userpref_enabled)
* [lang_selector_enabled](#lang_selector_enabled)
* [lang_save_url_switch_in_userpref](#lang_save_url_switch_in_userpref)

## Email

* [email_from](#email_from)
* [email_from_name](#email_from_name)
* [email_reply_to](#email_reply_to)
* [email_reply_to_name](#email_reply_to_name)
* [email_return_path](#email_return_path)
* [email_use_html](#email_use_html)
* [email_newline](#email_newline)
* [email_headers](#email_headers)
* [email_send_with_minus_r_option](#email_send_with_minus_r_option)
* [relay_unknown_feedbacks](#relay_unknown_feedbacks)
* [translatable_emails_lifetime](#translatable_emails_lifetime)

## General UI

* [theme](#theme)
* [autocomplete](#autocomplete)
* [autocomplete_max_pool](#autocomplete_max_pool)
* [autocomplete_min_characters](#autocomplete_min_characters)
* [upload_show_play_pause](#upload_show_play_pause)
* [upload_display_bits_per_sec](#upload_display_bits_per_sec)
* [upload_display_per_file_stats](#upload_display_per_file_stats)
* [upload_force_transfer_resume_forget_if_encrypted](#upload_force_transfer_resume_forget_if_encrypted)
* [upload_considered_too_slow_if_no_progress_for_seconds](#upload_considered_too_slow_if_no_progress_for_seconds)
* [crypto_pbkdf2_dialog_enabled](#crypto_pbkdf2_dialog_enabled)
* [crypto_pbkdf2_delay_to_show_dialog](#crypto_pbkdf2_delay_to_show_dialog)
* [crypto_pbkdf2_expected_secure_to_year](#crypto_pbkdf2_expected_secure_to_year)
* [crypto_pbkdf2_dialog_custom_webasm_delay](#crypto_pbkdf2_dialog_custom_webasm_delay)
* [upload_page_password_can_not_be_part_of_message_handling](#upload_page_password_can_not_be_part_of_message_handling)
* [user_page](#user_page)
* [allow_pages_core](#allow_pages_core)
* [allow_pages_add_for_guest](#allow_pages_add_for_guest)
* [allow_pages_add_for_user](#allow_pages_add_for_user)
* [allow_pages_add_for_admin](#allow_pages_add_for_admin)
* [can_view_statistics](#can_view_statistics)
* [can_view_aggregate_statistics](#can_view_aggregate_statistics)
* [auth_sp_saml_can_view_statistics_entitlement](#auth_sp_saml_can_view_statistics_entitlement)
* [auth_sp_saml_can_view_aggregate_statistics_entitlement](#auth_sp_saml_can_view_aggregate_statistics_entitlement)
* [read_only_mode](#read_only_mode)
* [date_format_style](#date_format_style)
* [time_format_style](#time_format_style)
* [make_download_links_clickable](#make_download_links_clickable)
* [valid_timezone_regex](#valid_timezone_regex)
* [client_send_current_timezone_to_server](#client_send_current_timezone_to_server)


## Transfers

* [aup_default](#aup_default)
* [aup_enabled](#aup_enabled)
* [api_secret_aup_enabled](#api_secret_aup_enabled)
* [ban_extension](#ban_extension)
* [chunk_upload_security](#chunk_upload_security)
* [default_transfer_days_valid](#default_transfer_days_valid)
* [max_transfer_days_valid](#max_transfer_days_valid)
* [allow_transfer_expiry_date_extension](#allow_transfer_expiry_date_extension)
* [allow_transfer_expiry_date_extension_admin](#allow_transfer_expiry_date_extension_admin)
* [force_legacy_mode](#force_legacy_mode)
* [legacy_upload_progress_refresh_period](#legacy_upload_progress_refresh_period)
* [max_legacy_file_size](#max_legacy_file_size)
* [max_transfer_size](#max_transfer_size)
* [max_transfer_files](#max_transfer_files)
* [max_transfer_recipients](#max_transfer_recipients)
* [transfer_options](#transfer_options) (email receipt control)
* [upload_chunk_size](#upload_chunk_size)
* [user_quota](#user_quota)
* [max_transfer_file_size](#max_transfer_file_size)
* [max_transfer_encrypted_file_size](#max_transfer_encrypted_file_size)
* [disable_directory_upload](#disable_directory_upload)
* [directory_upload_button_enabled](#directory_button_upload_enabled)
* [encryption_enabled](#encryption_enabled)
* [encryption_mandatory](#encryption_mandatory)
* [encryption_mandatory_with_generated_password](#encryption_mandatory_with_generated_password)
* [encryption_min_password_length](#encryption_min_password_length)
* [encryption_password_must_have_upper_and_lower_case](#encryption_password_must_have_upper_and_lower_case)
* [encryption_password_must_have_numbers](#encryption_password_must_have_numbers)
* [encryption_password_must_have_special_characters](#encryption_password_must_have_special_characters)
* [encryption_password_text_only_min_password_length](#encryption_password_text_only_min_password_length)
* [encryption_key_version_new_files](#encryption_key_version_new_files)
* [encryption_random_password_version_new_files](#encryption_random_password_version_new_files)
* [encryption_password_hash_iterations_new_files](#encryption_password_hash_iterations_new_files)
* [encryption_encode_encrypted_chunks_in_base64_during_upload](#encryption_encode_encrypted_chunks_in_base64_during_upload)
* [automatic_resume_number_of_retries](#automatic_resume_number_of_retries)
* [automatic_resume_delay_to_resume](#automatic_resume_delay_to_resume)
* [transfer_options_not_available_to_export_to_client](#transfer_options_not_available_to_export_to_client)
* [chunk_upload_roundtriptoken_check_enabled](#chunk_upload_roundtriptoken_check_enabled)
* [chunk_upload_roundtriptoken_check_accept_before](#chunk_upload_roundtriptoken_check_accept_before)
* [streamsaver_enabled](#streamsaver_enabled)
* [streamsaver_on_unknown_browser](#streamsaver_on_unknown_browser)
* [streamsaver_on_firefox](#streamsaver_on_firefox)
* [streamsaver_on_chrome](#streamsaver_on_chrome)
* [streamsaver_on_edge](#streamsaver_edge)
* [streamsaver_on_safari](#streamsaver_safari)
* [recipient_reminder_limit](#recipient_reminder_limit)
* [log_authenticated_user_download_by_ensure_user_as_recipient](#log_authenticated_user_download_by_ensure_user_as_recipient)
* [transfer_automatic_reminder](#transfer_automatic_reminder)
* [transfers_table_show_admin_full_path_to_each_file](#transfers_table_show_admin_full_path_to_each_file)

## Graphs

* [upload_graph_bulk_display](#upload_graph_bulk_display)
* [upload_graph_bulk_min_file_size_to_consider](#upload_graph_bulk_min_file_size_to_consider)

## TeraSender (high speed upload module)

* [terasender_enabled](#terasender_enabled)
* [terasender_advanced](#terasender_advanced)
* [terasender_worker_count](#terasender_worker_count)
* [terasender_worker_max_count](#terasender_worker_max_count)
* [terasender_start_mode](#terasender_start_mode)
* [terasender_worker_max_chunk_retries](#terasender_worker_max_chunk_retries)
* [stalling_detection](#stalling_detection)

## Download

* [download_chunk_size](#download_chunk_size)
* [mac_unzip_name](#mac_unzip_name)
* [mac_unzip_link](#mac_unzip_link)

## Guest use

* [guest_support_enabled](#guest_support_enabled)
* [guest_options](#guest_options)
* [default_guest_days_valid](#default_guest_days_valid)
* [min_guest_days_valid](#min_guest_days_valid)
* [max_guest_days_valid](#max_guest_days_valid)
* [max_guest_recipients](#max_guest_recipients)
* [guest_upload_page_hide_unchangable_options](#guest_upload_page_hide_unchangable_options)
* [user_can_only_view_guest_transfers_shared_with_them](#user_can_only_view_guest_transfers_shared_with_them)
* [guest_create_limit_per_day](#guest_create_limit_per_day)
* [guest_reminder_limit](#guest_reminder_limit)
* [guest_reminder_limit_per_day](#guest_reminder_limit_per_day)
* [allow_guest_expiry_date_extension](#allow_guest_expiry_date_extension)
* [allow_guest_expiry_date_extension_admin](#allow_guest_expiry_date_extension_admin)
* [guest_limit_per_user](#guest_limit_per_user)
* [guests_expired_lifetime](#guests_expired_lifetime)
* [guest_upload_page_hide_unchangable_options](#guest_upload_page_hide_unchangable_options)

## Authentication

* [auth_sp_type](#auth_sp_type)
* [auth_sp_force_session_start_first](#auth_sp_force_session_start_first)
* __SimpleSAMLphp__
	* [auth_sp_saml_authentication_source](#auth_sp_saml_authentication_source)
	* [auth_sp_saml_simplesamlphp_url](#auth_sp_saml_simplesamlphp_url)
	* [auth_sp_saml_simplesamlphp_location](#auth_sp_saml_simplesamlphp_location)
	* [auth_sp_saml_email_attribute](#auth_sp_saml_email_attribute)
	* [auth_sp_saml_name_attribute](#auth_sp_saml_name_attribute)
	* [auth_sp_saml_uid_attribute](#auth_sp_saml_uid_attribute)
	* [auth_sp_saml_entitlement_attribute](#auth_sp_saml_entitlement_attribute)
	* [auth_sp_saml_admin_entitlement](#auth_sp_saml_admin_entitlement)
        * [using_local_saml_dbauth](#using_local_saml_dbauth)
* __Shibboleth__
	* [auth_sp_shibboleth_uid_attribute](#auth_sp_shibboleth_uid_attribute)
	* [auth_sp_shibboleth_email_attribute](#auth_sp_shibboleth_email_attribute)
	* [auth_sp_shibboleth_name_attribute](#auth_sp_shibboleth_name_attribute)
	* [auth_sp_shibboleth_login_url](#auth_sp_shibboleth_login_url)
	* [auth_sp_shibboleth_logout_url](#auth_sp_shibboleth_logout_url)
* __SP_Fake__
	* [auth_sp_fake_authenticated](#auth_sp_fake_authenticated)!!
	* [auth_sp_fake_uid](#auth_sp_fake_uid)!!
	* [auth_sp_fake_email](#auth_sp_fake_email)!!
	* [auth_sp_fake_name](#auth_sp_fake_name)!!

## Maintenance and logging

* [failed_transfer_cleanup_days](#failed_transfer_cleanup_days)
* [log_facilities](#log_facilities)!!
* [maintenance](#maintenance)
* [statlog_lifetime](#statlog_lifetime)
* [auth_sp_additional_attributes](#auth_sp_additional_attributes)
* [auth_sp_save_user_additional_attributes](#auth_sp_save_user_additional_attributes)
* [statlog_log_user_additional_attributes](#statlog_log_user_additional_attributes)
* [auth_sp_fake_additional_attributes_values](#auth_sp_fake_additional_attributes_values)
* [auditlog_lifetime](#auditlog_lifetime)
* [ratelimithistory_lifetime](#ratelimithistory_lifetime)
* [report_format](#report_format)
* [exception_additional_logging_regex](#exception_additional_logging_regex)
* [clientlogs_stashsize](#clientlogs_stashsize)
* [clientlogs_lifetime](#clientlogs_lifetime)
* [logs_limit_messages_from_same_ip_address](#logs_limit_messages_from_same_ip_address)
* [trackingevents_lifetime](#trackingevents_lifetime)
* [client_ip_key](#client_ip_key)

## Webservices API

* [auth_remote_application_enabled](#auth_remote_application_enabled)
* [auth_remote_signature_algorithm](#auth_remote_signature_algorithm)
* [auth_remote_applications](#auth_remote_applications)
* [auth_remote_user_enabled](#auth_remote_user_enabled)
* [auth_remote_user_autogenerate_secret](#auth_remote_user_autogenerate_secret)
* [rest_allow_jsonp](#rest_allow_jsonp)

## Aggregate statistics

* [aggregate_statlog_lifetime](#aggregate_statlog_lifetime)
* [aggregate_statlog_send_report_days](#aggregate_statlog_send_report_days)
* [aggregate_statlog_send_report_email_address](#aggregate_statlog_send_report_email_address)


## Other

* [host_quota](#host_quota)
* [config_overrides](#config_overrides) (experimental feature, not tested)
* [auth_config_regex_files](#auth_config_regex_files)

## Data Protection

* [data_protection_user_frequent_email_address_disabled](#data_protection_user_frequent_email_address_disabled)
* [data_protection_user_transfer_preferences_disabled](#data_protection_user_transfer_preferences_disabled)

## Deprecated settings
* [encryption_generated_password_length](#encryption_generated_password_length)

---

# Configuration directives

---

## General settings

---

### admin_email

* __description:__ email address of FileSender administrator(s).  Separate multiple addresses with a comma (','). Emails regarding disk full etc. are sent here. You should use a role-address here.
* <span style="background-color:red">__mandatory:__ yes.  There must be at least one email address defined.</span>
* __type:__ string.
* __default:__ -
* __available:__ since version 1.0
* __1.x name:__ adminEmail

### support_email

* __description:__ email address of somebody handling support requests for the FileSender installation. This address may receive traffice from the relay_unknown_feedbacks option.
* __mandatory:__ no.
* __type:__ string.
* __default:__ 
* __available:__ 2.6

### admin

* __description:__ UIDs (as per the configured saml_uid_attribute) of FileSender administrators. Accounts with these UIDs can access the Admin page through the web UI.  <span style="background-color:orange">Separate multiple entries with a comma (',').</span>
* <span style="background-color:red">__mandatory:__ yes.  Can be empty but then no-one has access to the admin page.</span>
* __type:__ string
* __default:__ -
* __available:__ since version 1.0

### site_name

* __description:__ friendly name for your FileSender instance. Used in site header in browser and in email templates.
* __mandatory:__ no. Falls back to the default if not set.
* __type:__ string
* __default:__ FileSender
* __available:__ since version 1.0

### force_ssl

* __description:__ enforce use of SSL. Set this to true and FileSender won't work if the user doesn't have a SSL session. Useful to retain security in case of web server misconfigurations.
* __mandatory:__ no. <span style="background-color:orange">if you don't set it it will be evaluated to false?  What about the default of 'true'?)</span>
* __type:__ boolean
* __default:__ true
* __available:__ since version 1.0
* __1.x name:__ forceSSL

### session_cookie_path

* __description:__ Explicitly sets the session.cookie.path parameter for the authentication cookies.  You typically need this if you use SimpleSAMLphp for authentication and have multiple FileSender instances using the same SimpleSAMLphp installation.  Shibboleth has its own session identifier mechanism and you probably won't need to change the session_cookie_path when using Shibboleth.
* __mandatory:__ no
* __type:__ string
* __default:__ if(!$session_cookie_path) $session_cookie_path = $site_url_parts['path'];
* __available:__ since version 2.0
* __1.x name:__
* __comment:__ Testing, ticket #1198
* __comment:__ Be careful to include the entire URL path, like `https://example.org/`!
* __comment:__ When do you set this?  If you use SimpleSAMLphp for authentication there is one common scenario where you need to set this parameter: the URL space for your FileSender instance and your SimpleSAMLphp instance do not overlap.  This happens when you have multiple FileSender instances (one production, one staging) sharing the same SimpleSAMLphp installation. For example: `https://example.org/filesender-staging` and `https://example.org/simplesamlphp`.  Because SimpleSAMLphp and FileSender are both written in PHP they use the same mechanism for session identifiers.  They can share session identifiers but only if this is allowed by the session_cookie_path.  When you log on with SimpleSAMLphp a session identifier is created.  If this can not be shared with your FileSender instance you will notice a user can log on, only to be presented with the same logon form again.  A silent failure.  In this scenario you will either need to ensure your SimpleSAMLphp instance is available within the FileSender URL space, or you set the session cookie parameter to for example `https://example.org/`.  Another workaround is to use memcache for SimpleSAMLphp's session identifiers but that would mean an extra configuration work and an extra package and process to manage on your server.

### default_timezone

* __description:__ used to set default timezone of PHP. Used to convert dates. Dates are loaded from database and converted to PHP timestamps on the fly. Times in database are stored in GMT dates. Used to present localised time information. <span style="background-color:orange">Audit logs use time?  Also: include link to PHP timezone values</span>
* __mandatory:__ yes (<span style="background-color:orange">doublecheck</span>)
* __type:__ string
* __default:__ Europe/London
* __available:__ since version 1.0
* __1.x name:__ Default_TimeZone

### default_language

* __description:__ if there are no end-user overrides then this is the default language to use in the UI <span style="background-color:orange">(and email?).</span>  If the user picks a language that doesn't exist, or if a language directive isn't translated in the language served up to the user, the directive will in stead be taken from the language defined here as default_language. If all else fails, English (en) is a hard coded default.
* __mandatory:__ no. Hard-coded default of last resort: English ("en")
* __type:__ string
* __default:__ en
* __available:__ since version 1.6
* __1.x name:__ site_defaultlanguage
* __comment:__ if the default_language is not one of the available (configured) languages, the configuration validator will thrown an error.

### site_url

* __description:__ Site URL. Used in emails, to build URLs for logging in, logging out, build URL for upload endpoint for web workers, to include scripts etc.
* <span style="background-color:red">__mandatory:__ yes</span>
* __type:__ string
* __default:__ -
* __available:__ since version 1.0

### site_logouturl

* __description:__ $_GET parameters for the logout page;  this is where user gets redirected to after logout. Is given to the SP logout end-point.
* __mandatory:__ <span style="background-color:orange">?</span>
* __type:__ string
* __default:__ $config['site_url'].'?s=logout'
* __available:__ since version 1.6

### download_verification_code_enabled

* __description:__ Check that the user has access to their email address by sending a one time code to them and requiring them to enter that code before they can download files in a transfer. This is restricted to checking only users who have not logged in to the system.
* __mandatory:__ no.
* __type:__ bool
* __default:__ false
* __available:__ since version 2.41


### download_verification_code_valid_duration

* __description:__ how long a verify by email code should be valid for (in seconds).
* __mandatory:__ no.
* __type:__ int
* __default:__ 60*15
* __available:__ since version 2.41
* __comment:__ Default should be ok.

### download_verification_code_random_bytes_used

* __description:__ how many random bytes to use in the download verification code
* __mandatory:__ no.
* __type:__ int
* __default:__ 8
* __available:__ since version 2.41
* __comment:__ Default should be ok.

### download_show_download_links

* __description:__ show direct download urls on download page
* __mandatory:__ no.
* __type:__ bool
* __default:__ false
* __available:__ since version 2.42
* __comment:__ Default should be ok.



### reports_show_ip_addr

* __description:__ Show the IP addresses used in reports
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.0
* __comment:__ If you want to hide IP addresses from reports set it to false


### admin_can_view_user_transfers_page

* __description:__ Allow admin to view transfers page for users
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.18
* __comment:__ This allows an admin to find a user with admin/users and click to see the "my transfers" page that the specific user would see. ie, the admin sees the user's transfers instead of seeing their own. The menu becomes red in this mode and "my transfers" is changed to "user transfers" to attempt to caution the administrator that they are dealing with user data rather than their own.

### mime_type_regex

* __description:__ A regular expression to match mime types against.
* __mandatory:__ no
* __type:__ string
* __default:__ ^[-a-zA-Z0-9/; ]*$
* __available:__ since version 2.29
* __comment:__ This regular expression should describe "good" mime types. Note that optional parameters as shown in "Syntax of the Content-Type Header Field" of rfc2045 will have already been removed from the mime type before matching with this expression.The action taken if a string does not validate against this setting may be to refuse an action or to convert the mime type into application/octet-stream in order to ensure a known good mimetype rather than something unexpected. Failing mime types may be converted to mime_type_default instead of causing a halting error.


### mime_type_default

* __description:__ Default mime type to use if an invalid mimetype was sent by the client.
* __mandatory:__ no
* __type:__ string
* __default:__ application/octet-stream
* __available:__ since version 2.29
* __comment:__ Some failures are worse than others. This is the default mimetype to use if a client presents an invalid value.


### service_aup_min_required_version

* __description:__ If the site uses a service level AUP this is the current minimum version a user must have accepted to continue to upload files.
* __mandatory:__ no
* __type:__ int
* __default:__ 0
* __available:__ since version 2.30
* __comment:__ A setting of 0 disables the site wide AUP. Setting to 1 will enable AUP and force the user to accept the text from the language translation service_aup_text_version_1. If you were on level 1 and change service_aup_min_required_version=2 anyone who has not accepted or has only accepted service_aup_text_version_1 will be prompted to accept service_aup_text_version_2 after loading the next page. This continues onward allowing new AUP text and terms to be introduced and explicitly seeking user acceptance before they can continue to upload to the service.

### tmp_path

* __description:__ The location to store temporary scratch files
* __mandatory:__ no
* __type:__ string
* __default:__ FILESENDER_BASE.'/tmp/',
* __available:__ since before version 2.30
* __comment:__ Only some code has been migrated to using this configuration setting. It is intended to be a location that files might be temporarily stored while processing is happening.


### site_css

* __description:__ An additional css file to load after system ones to allow css updates by the admin. One might consider using this with the auth_config_regex_files option to change the look of a site depending on its use.
* __mandatory:__ no
* __type:__ string
* __default:__ ''
* __available:__ since version 2.40
* __comment:__  This will be taken relative to the css/ directory automatically.

### site_logo

* __description:__ An additional logo image to use. One might consider using this with the auth_config_regex_files option to change the look of a site depending on its use.
* __mandatory:__ no
* __type:__ string
* __default:__ ''
* __available:__ since version 2.40
* __comment:__  Note that this is relative to the www directory. So you might want to add the prefix images/ or skin/ depending on how your system is set up to find the image.







### use_strict_csp

* __description:__ Include a strict Content-Security-Policy (CSP) header in web page responses.
* __mandatory:__ no
* __type:__ boolean
* __default:__ true
* __available:__ since version 2.26
* __comment:__ Default should be ok. This adds a rather strict Content-Security-Policy (CSP) header to pages to avoid inline and eval and loading resources from other sites.

### header_x_frame_options

* __description:__ How to handle the X-Frame-Options HTTP header
* __mandatory:__ no
* __type:__ string
* __default:__ sameorigin
* __available:__ since version 2.7
* __comment:__ Default should be ok. Can be 'deny' to disallow frames if you do not use them or 'none' to disable the feature (not recommended). Note that this setting will not override a setting that is already in place in your web server. This setting is mainly here as a second catch and for sites that can not configure their web server to install a site wide nominated value for X-Frame-Options.

### header_add_hsts_duration

* __description:__ Add Strict-Transport-Security header with the desired max-age
* __mandatory:__ no
* __type:__ int
* __default:__ 63072000
* __available:__ since version 2.26
* __comment:__ Set to 0 to disable. Default is 63072000 which is two years in seconds.


### owasp_csrf_protector_enabled

* __description:__ Use the OWASP csrf protector as well as internal CSRF tokens
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __cookies:__ true
* __available:__ since version 2.7
* __comment:__ There is internal CSRF protection in FileSender. Turning this on by setting it to true enables usage of the
  [CSRF Protector php library](https://github.com/mebjas/CSRF-Protector-PHP/wiki) to also protect interactions from CSRF attack.
  Note that this option will definitely use cookies.

### avprogram_list

* __description:__ A list of anti virus and malware scanners to use.
* __mandatory:__ no
* __type:__ array (of array)
* __default:__ ()
* __available:__ since version 2.26
* __comment:__ This is a list of the classes to use to check for bad content. They can only run on non encrypted files as the
               server does not have access otherwise. The URL module accepts a parameter 'url' which is the url to send the
               file content to for scanning. It is expected that the reply is JSON with a passes, error, and reason property.
               The mime AV program takes an array of MIME types that the content MUST be in using the matchlist parameter.
               The mime AV program defaults to using the first 8k of content to determine the MIME type, use bytesToConsider
               to change this. Setting bytesToConsider to values below 8k will have no effect.

               Note that these programs are only executed when you run execute-av-program-on-files.php on the server. 
               The execute-av-program-on-files.php script needs permissions to access to the uploaded files and the database.
               The execute-av-program-on-files.php script will work on a small batch of files and sleep 10 seconds and then
               work on the next batch of files. It may be that the script needs to be improved for larger sites to allow many
               machines to access and perform these tasks as they can be time consuming depending on the scanner.

               The expected setup will use virus scanners over https passing the file stream to the scanner and retrieving the
               results of that scan to store in the database. An example of this is provided in
               classes/avprograms/AVProgramURLTest.php. The AVProgramURLTest.php can be installed on a web server and will
               fail for content that contains a bad word which in this case is literally "badword". The AVProgramURLTest is
               provided as an example that can be fleshed out to use other malware scanners as an installation desires.

               If you are using the 'url' method then FileSender will post file content to that URL and expect an JSON result
               indicating the result of the scan. For example for a success:
               { "passes": "1", "error": "0", "reason": "clean." }
               or something like the following or for an error:
               { "passes": "0", "error": "0", "reason": "contains a Trojan (56% certainty)." }
               or if the scanner itself encountered an error:
               { "passes": "0", "error": "1", "reason": "unable to use local database to compare data with." }.

               From an implementation perspective, results are recorded in the AVResults table and the download page will
               display the results of scans if they are available.

               



```
$config['avprogram_list'] = array( 'always_pass',
                                   'mime' => array(
                                       'name' => 'Check for valid MIME type',
                                       'bytesToConsider' => 8*1024,
                                       'matchlist' => array('image/jpeg', 'text/plain')
                                   ),
                                   'url' => array(
                                       'name' => 'Foo',
                                       'url' => 'http://localhost/foo/scanforfoo.php'
                                   ));
```

### avprogram_max_size_to_scan
* __description:__ Do not try to scan files larger than this with the avprogram_list
* __mandatory:__ no
* __type:__ int
* __default:__ 100*1024*1024
* __available:__ since version 2.26
* __comment:__ 


### crypto_iv_len
* __description:__ Internal use only
* __mandatory:__ no
* __type:__ int
* __default:__ 16
* __available:__ since before version 2.30
* __comment:__ This is an internal setting, please do not change it.


### crypto_gcm_max_file_size
* __description:__ This is the largest file that should be sent using GCM encryption with the default chunk size.
* __mandatory:__ no
* __type:__ int
* __default:__ 4294967296 * 5 * 1024 * 1024
* __available:__ since before version 2.30
* __comment:__ The default is roughly 16384 tb so is more of a double check than anything. This is to protect
     against wrap around of GCM cryptography using the same xor stream.
     It is recommended that you leave this setting as the default value and do not change the
     upload_chunk_size when using GCM cryptography. You may wish to lower this setting if you have to use
     smaller upload_chunk_size values and will lower this setting commensurate with the chunk size being
     smaller than the default.

### crypto_gcm_max_file_size
* __description:__ This is the largest file that should be sent using GCM encryption with the default chunk size.
* __mandatory:__ no
* __type:__ int
* __default:__ 4294967296 * 5 * 1024 * 1024
* __available:__ since before version 2.30
* __comment:__ It is recommended that you leave this setting as the default value and do not change the
     upload_chunk_size when using GCM cryptography. The default is roughly 16384 tb so is more of a double check than anything. This is to protect
     against wrap around of GCM cryptography using the same xor stream.
     You may wish to lower this setting if you have to use
     smaller upload_chunk_size values and will lower this setting commensurate with the chunk size being
     smaller than the default.

### crypto_gcm_max_chunk_size
* __description:__ This is the largest size of a single chunk that should be sent using GCM encryption.
* __mandatory:__ no
* __type:__ int
* __default:__ 4294967295 * 16
* __available:__ since before version 2.30
* __comment:__ It is recommended that you leave this setting as the default value and do not change the
     upload_chunk_size when using GCM cryptography. The default is 2^32-1 AES blocks of 16 bytes.


### crypto_gcm_max_chunk_count
* __description:__ This is the maximum total number of chunks that should be sent for a file when using GCM encryption.
* __mandatory:__ no
* __type:__ int
* __default:__ 4294967295
* __available:__ since before version 2.30
* __comment:__ It is recommended that you leave this setting as the default value and do not change the
     upload_chunk_size when using GCM cryptography. The default is 2^32-1.

### crypto_crypt_name
* __description:__ Internal use. The name of the cipher currently used. This is set from encryption_key_version_new_files for new transfers or the key version that was used for an existing transfer.
* __mandatory:__ no
* __type:__ string
* __default:__ calculated
* __available:__ since before version 2.30
* __comment:__ This is an internal setting. It will be overridden in crypto_app based on the key version to be used for a transfer. The key version in that was set in encryption_key_version_new_files
is stored as part of the metadata for each transfer when it is created. When a transfer is to be downloaded the key version used for that transfer will be used to set the crypto_crypt_name.
This way the encryption_key_version_new_files can be updated and existing uploads will continue to be able to be downloaded.

### upload_crypted_chunk_size
* __description:__ Internal only setting. This is the entire size of an encrypted chunk, including any padding for per chunk IV
* __mandatory:__ no
* __type:__ int
* __default:__ 5 * 1024 * 1024 + 16 + 16
* __available:__ since before version 2.30
* __comment:__ It is highly recommended that you leave this setting as the default value. This is the size, including any IV and padding
           needed for an encrypted chunk that is uploaded.

### upload_crypted_chunk_padding_size
* __description:__ Internal only setting. This is the size of padding and IV for an encrypted chunk
* __mandatory:__ no
* __type:__ int
* __default:__ 16 + 16
* __available:__ since before version 2.30
* __comment:__ It is highly recommended that you leave this setting as the default value. This is the size of just the IV and padding
           needed for an encrypted chunk that is uploaded (not the encrypted content itself).


### cookie_domain
* __description:__ Optionally allow the cookie_domain to be set for new cookies.
* __mandatory:__ no
* __type:__ string
* __default:__ ''
* __available:__ since version 2.33
* __comment:__ It is highly recommended that you leave this setting as the default value which will be unset. The default will mean "If omitted, this attribute defaults to the host of the current document URL, not including subdomains.". This setting is here to allow a deployment to set a value like filesender.example.com to allow all subdomains from that domain to also see the cookie if desired.   https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie#domaindomain-value


### rate_limits
* __description:__ Some actions have hard or soft limits that can be applied. For example, actions that result in sending an email may have a soft limit that will perform the action but not send an email after the nominated number of actions is performed per time period. This allows for the system to prevent a nefarious guest from sending too many emails. The database table ratelimithistorys is used to track the information needed for this option. The time period for actions performed used for the initial implementation is 24 hours. For example, with the right setting you can not create more than 200 guests in a 24 hour block of time. There are two types of limits, hard and soft. A hard limit will be checked before performing an action and if the limit is already reached the action will not be performed. For example, creating a guest or sending a transfer_reminder is a hard limit. This is because creating a guest may require sending an email to be performed so it is not useful to try to perform the action if the rate limit is already reached. Some actions are soft limits such as guest_upload_start and will only effect the sending of an email. For the guest_upload_start example, if the limit is 30 per day then a guest may start an upload 1000 times and you will only receive emails for the first 30 attempts. This way the system remains functional but does not try to produce excessive emails while it is performing that function. In general a soft limit is to protect against excessive email but not to limit the use of the system itself.
* __mandatory:__ no
* __type:__ array
* __default:__ '$config['rate_limits'] = array(
            'email' => array(
            'guest_created'      => array( 'day' => 100 ),
            'report_inline'      => array( 'day' => 100 ),
            'transfer_reminder'  => array( 'day' => 100 ),
            'download_complete'  => array( 'day' => 500 ),
            'files_downloaded'   => array( 'day' => 500 ),
            'guest_upload_start' => array( 'day' => 100 ),
            'transfer_available' => array( 'day' => 500 ),
        ),
    );'
* __available:__ since version 2.33
* __1.x name:__
* __comment:__ For current defaults see https://github.com/search?q=repo%3Afilesender%2Ffilesender+rate_limits+path%3Aincludes
* __*Standard parameters for all options:*__
	* __day__(integer): The number of times this action can be performed per day.
ed off by the user.
* __*Available options:*__
	* __guest\_created:__ hard limit
	* __report\_inline:__ hard limit
	* __transfer\_reminder:__ hard limit
	* __download\_complete:__ soft limit
	* __files\_downloaded:__ soft limit
	* __guest\_upload\_start:__ soft limit
	* __transfer\_available:__ soft limit

* __*Configuration example:*__

$config['rate_limits'] = array(
        'email' => array(
            'guest_created'      => array( 'day' => 30 ),
            'report_inline'      => array( 'day' => 20 ),
            'transfer_reminder'  => array( 'day' => 15 ),
            'download_complete'  => array( 'day' => 300 ),
            'files_downloaded'   => array( 'day' => 100 ),
            'guest_upload_start' => array( 'day' => 50 ),
            'transfer_available' => array( 'day' => 200 ),
        ),
);


### valid_filename_regex
* __description:__ Regular exression that must match a file name in an upload for that file to be allowed
* __mandatory:__ no
* __type:__ string
* __default:__ '^[ \\/\\p{L}\\p{N}_\\.,;:!@#$%^&*)(\\]\\[_-]+'
* __available:__ since version 2.0
* __comment:__ You may wish to allow more characters to this
               expression to allow emoji in file names for example.
               One might like to consider potential cases such as the
               unicode U+2044 fraction slash which might be confused
               with a / and U+205F which is a math space and might be
               confused with a regular space. The ending '$' will be
               added to match against the end of string for you.
               
* __*Configuration example:*__
  //  adds '+' in ASCII
  //  adds special character areas, for example MIDDLE DOT U+30FB
$config['valid_filename_regex'] = '^['."\u{2010}-\u{2027}\u{2030}-\u{205F}\u{2070}-\u{FFEF}\u{10000}-\u{10FFFF}".' \\/\\p{L}\\p{N}_\\.,;:!@#$%^&*+)(\\]\\[_-]+';



---

## Backend storage

---

### storage_type

* __description:__  type of storage you used for storing files uploaded to FileSender.
* __mandatory:__ no
* __type:__ string
* __permissible values__ filesystem, filesystemChunked, CloudAzure, CloudS3, filesystemExternal
* __default:__ filesystem
* __available:__ since version 2.0
* __comment:__ each supported storage type will have a specific class defined in classes/storage.  Each is named Storage<Foo>.class.php, for example StorageFilesystem.class.php for the type filesystem.  The values for "Foo" are the permissible values for this directive. The primary choices for value are filesystem and filesystemChunked. Note that you need to respect the non leading capital letters in the class name such as the "C" in filesystemChunked. Future storage types could include e.g. **object**, **amazon_s3** and others.

   If you are using cloud backed storage please also see the [cloud configuration page](https://docs.filesender.org/filesender/v2.0/cloud/).

### storage_filesystem_path

* __description:__ when using storage type **filesystem** this is the absolute path to the file system where uploaded files are stored until they expire.  Your FileSender storage root.
* __mandatory:__ no
* __type:__ string
* __default:__ ['filesenderbase'].'/files'
* __available:__ since version 1.0
* __1.x name:__ site_filestore
* __comment:__

### storage_filesystem_df_command

* __description:__ Command used to determine available disk space on file system.  Used to perform per-transfer check for sufficient disk space and to trigger disk space usage warnings to the FileSender Admin
* __mandatory:__ <span style="background-color:orange">?</span>
* __type:__ string
* __default:__ <span style="background-color:orange">?</span>?
* __available:__ since version 2.0
* __1.x name:__
* __comment:__

### storage_filesystem_file_deletion_command

* __description:__ Command used to delete files, when they expire or are cleaned in routine cleaning of stale files.
* __mandatory:__ <span style="background-color:orange">?</span>
* __type:__ string
* __default:__ <span style="background-color:orange">?</span>
* __available:__ since version 1.1
* __1.x name:__ cron_shred_command
* __comment:__

### storage_filesystem_tree_deletion_command
* __description:__ Command used to delete whole directories and the contents, when they expire or are cleaned in routine cleaning of stale files.
* __mandatory:__ no.  If not set, default used
* __type:__ string
* __default:__ rm -rf
* __available:__ since version 2.0
* __comment:__

### storage_usage_warning

* __description:__ percentage of drive space left that will trigger an email warning to the admin.
* __mandatory:__ no.  If not set, evaluates to zero and you get no warnings.
* __type:__ int
* __default:__ 20
* __available:__ since version 1.0
* __1.x name:__ server_drivespace_warning
* __comment:__

### <span style="background-color:orange">storage_filesystem_hashing

* __description:__ Aggregate several directories into a virtual FileSender file store without using LVM.  Directories can be on different file systems which can be on different block devices and hard drives.  Allows you to pool several hard drives into one virtual FileSender file store without any external software.
* __mandatory:__ no
* __type:__ **int** or **callable**.  When integer indicates number of characters used in hash.  When callable "file que l'on veit stocker et doit retourner le chemin dans le stockage"
* __default:__ 0
* __available:__ since version 20
* __1.x name:__
* __comment:__ not tested
* __comment:__ basically integer. use fileUID (which is used to create name on hard drive) + as many characters as the hashing value (if you set hashing to 2 you take the 2 first letters of the fileUID (big random string) and use these two characters to create a directory structure under the storage path. This avoids having all files in the same directory. If you set this to 1 you have 16 possible different values for the directory structure under the storage root. You'll have 16 folders under your storage root under which you'll have the files. This allows you to spread files over different file systems / hard drives. You can aggregate storage space without using things like LVM. If you set this to two you have 2 levels of subdirectories. For directory naming: first level, directory names has one letter. Second level has two: letter from upper level + own level. Temporary chunks are stored directly in the final file. No temp folder (!!) Benchmarking between writing small file in potentially huge directory and opening big file and seeking in it was negligible. Can just open final file, seek to location of chunk offset and write data. Removes need to move file in the end.  It can also be "callable". We call the function giving it the file object which hold all properties of the file. Reference to the transfer as well. The function has to return a path under the storage root. This is a path related to storage root. For example: if you want to store small files in a small file directory and big files in big directory. F.ex. if file->size < 100 MB store on fast small disk, if > 100 MB store on big slow disk. Can also be used for functions to store new files on new storage while the existing files remain on existing storage. Note: we need contributions for useful functions here :)


### storage_filesystem_per_day_buckets

* __description:__ Store files in a subdirectory based on the day they were created.
* __mandatory:__ no
* __type:__ **bool** 
* __default:__ false
* __available:__ since version 2.45
* __comment:__ This requires version 7 UUIDs to be in use. The timestamp from the v7 uuid is taken and the seconds since midnight are removed and that is used to create a subdirectory for the stored files. See also storage_filesystem_per_hour_buckets. Note that this works with storage_filesystem_per_hour_buckets, if both are enabled then first a daily directory is made and then an hourly directory is created in the day directory and the files are stored in the hourly subdirectory. This will allow the number of entries in a directory to be controlled by a system administrator and the use of v7 uuid will also permit the kernel filesystem to better index entry lookup.



### storage_filesystem_per_hour_buckets

* __description:__ Store files in a subdirectory based on the hour they were created.
* __mandatory:__ no
* __type:__ **bool** 
* __default:__ false
* __available:__ since version 2.45
* __comment:__ This requires version 7 UUIDs to be in use. The timestamp from the v7 uuid is taken and the seconds since the start of the hour are removed and that is used to create a subdirectory for the stored files. See also storage_filesystem_per_day_buckets for an overview of this feature.

### storage_filesystem_per_day_max_age_to_create_directory

* __description:__ Mostly internal use. Bucket directories are created automatically. This is the maximum number of days ago to create these subdirectory buckets.
* __mandatory:__ no
* __type:__ int
* __default:__ 7
* __available:__ since version 2.47
* __comment:__ This is mostly for internal use and likely fine to leave at default. This prevents bucket subdirectories from being recreated if very old files are listed where the file content is already deleted by the cron job.

### storage_filesystem_per_day_min_days_to_clean_empty_directories

* __description:__ Mostly internal use. How many days ago the cron job starts to consider when looking for empty bucket directories to delete
* __mandatory:__ no
* __type:__ int
* __default:__ -1 which means this is set to max_transfer_days_valid
* __available:__ since version 2.47
* __comment:__ This is mostly for internal use and likely fine to leave at default. 


### storage_filesystem_per_day_max_days_to_clean_empty_directories

* __description:__ Mostly internal use. How far back from storage_filesystem_per_day_min_days_to_clean_empty_directories to consider when trying to delete empty bucket directories
* __mandatory:__ no
* __type:__ int
* __default:__ 150
* __available:__ since version 2.47
* __comment:__ This is mostly for internal use and likely fine to leave at default. 







### storage_filesystem_ignore_disk_full_check

* __description:__ Ignore tests to see if new files will fit onto the filesystem.
* __mandatory:__ no.  
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.0
* __comment:__ If you are using FUSE to interface with some other storage such as EOS then you might like to set this to true to avoid having to do a distributed search to find out of there is storage for each upload


### storage_filesystem_external_script

* __description:__ When using the storage_type of filesystemExternal this is the path to the script that can read/write data to external storage.
* __mandatory:__ no.  
* __type:__ string
* __default:__ FILESENDER_BASE.'/scripts/StorageFilesystemExternal/external.py'
* __available:__ since before version 2.30
* __comment:__ The script at the given path should perform similar read/write operations as the example external.py script to maintain the storage.

### cloud_s3_region

* __description:__ Optional name of the region configuration for the [s3 storage backend](https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_configuration.html#cfg-region)
* __mandatory:__ no.
* __type:__ string
* __default:__ 'us-east-1'
* __available:__ since version 2
* __comment:__ If you use a different s3 region from default, make sure to set this. Non-AWS implementations usually have this set to default.

### cloud_s3_version

* __description:__ Optional API version for the [s3 storage backend](https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_configuration.html#cfg-version)
* __mandatory:__ no.
* __type:__ string
* __default:__ 'latest' 
* __available:__ since version 2 
* __comment:__ If you use a different s3 API version from default, make sure to set this. AWS usually has this set to default.  

### cloud_s3_endpoint

* __description:__ Optional API endpoint for the [s3 storage backend](https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_configuration.html#cfg-endpoint)
* __mandatory:__ no.
* __type:__ string
* __default:__ 'http://localhost:8000'
* __available:__ since version 2
* __comment:__ The API endpoint that your S3 service can be reached at. For default AWS endpoints check [here](https://docs.aws.amazon.com/general/latest/gr/s3.html)

### cloud_s3_key

* __description:__ Authentication key ID for the [s3 storage backend](https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials_hardcoded.html)
* __mandatory:__ no.
* __type:__ string
* __default:__ 'accessKey1'
* __available:__ since version 2
* __comment:__ The key ID associated with the [cloud_s3_secret](#cloud_s3_secret)

### cloud_s3_secret

* __description:__ Authentication secret key ID for the [s3 storage backend](https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials_hardcoded.html)
* __mandatory:__ no.
* __type:__ string
* __default:__ 'verySecretKey1'
* __available:__ since version 2
* __comment:__ The secret key ID associated with the [cloud_s3_key](#cloud_s3_key)

### cloud_s3_use_path_style_endpoint

* __description:__ Choose to use a path style endpoint for the [s3 storage backend](https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.S3.S3Client.html#__construct)
* __mandatory:__ no.
* __type:__ bool
* __default:__ true
* __available:__ since version 2
* __comment:__ Set to true to send requests to an S3 path style endpoint. 

### cloud_s3_bucket

* __description:__ Optional name of a single bucket to use for storing all files in on S3.
* __mandatory:__ no.  
* __type:__ string
* __default:__ ''
* __available:__ since version 2.31
* __comment:__ If you wish to store all files in a single bucket set it's name in this configuration option.
Ensure that the named bucket already exists if you use this setting.

### cloud_s3_use_daily_bucket

* __description:__ Enable filesender to use daily buckets for storing files.
* __mandatory:__ no.  
* __type:__ bool
* __default:__ false
* __available:__ since version 2.40
* __comment:__ If you wish to store all files uploaded in a single day at one bucket, enable this option and
optionally also set cloud_s3_bucket_prefix to define the prefix for daily buckets. Bucket names are formed by
concatenating cloud_s3_bucket_prefix + YYYY-MM-DD, for example a prefix of "Test-" could create bucket "Test-2023-04-30".
If cloud_s3_bucket and cloud_s3_use_daily_bucket are both set, this option takes precedence.
Daily buckets are created/deleted by cron.php so ensure you have it configured in your crontab! If you wish
to manually create the buckets (for example when first turning this setting on), run
php scripts/task/S3bucketmaintenance.php --verbose

### cloud_s3_bucket_prefix

* __description:__ Optional prefix for S3 daily buckets.
* __mandatory:__ no.  
* __type:__ string
* __default:__ ''
* __available:__ since version 2.40
* __comment:__ If cloud_s3_use_daily_bucket has been set, you can define the prefix for daily buckets with
this option. Daily bucket names are formed by concatenating cloud_s3_bucket_prefix + YYYY-MM-DD,
for example a prefix of "Test-" could create bucket "Test-2023-04-30". An empty prefix would create "2023-04-30".

### cloud_s3_bulk_delete

* __description:__ Toggle bulk delete or serial chunk delete
* __mandatory:__ no.
* __type:__ bool
* __default:__ false
* __available:__ since version 2.45
* __comment:__ When deleting a file, this chooses between deleting one chunk per request, or sending a bulk request
deleting up to [cloud_s3_bulk_size](#cloud_s3_bulk_size) chunks per request.

### cloud_s3_bulk_size

* __description:__ Maximum number of chunks to delete per bulk delete request
* __mandatory:__ no.
* __type:__ integer
* __default:__ 1000
* __available:__ since version 2.45
* __comment:__ When [cloud_s3_bulk_delete](#cloud_s3_bulk_delete) is true, this is the maximum size of the delete request.
Default value to maintain AWS S3 compatibility is 1000. Other storage platforms may use different defaults. OpenStack Swift defaults to 10000, for instance


---

## Shredding

---

### storage_filesystem_shred_path

* __description:__ Path to store files that should be fed to shred.
* __mandatory:__ no.  
* __type:__ string
* __default:__ ['filesenderbase'].'/shredfiles'
* __available:__ since version 2.0 beta 4
* __comment:__ This should be on the same filesystem as storage_filesystem_path
       so that a 'mv' of a file between the two paths does not require new files
       to be made.

### storage_filesystem_file_shred_command

* __description:__ command to shred files
* __mandatory:__ no.  
* __type:__ string
* __default:__ nothing
* __available:__ since version 2.0 beta 4
* __comment:__ If this is set then file shredding will be enabled. See the [shredding page](https://docs.filesender.org/filesender/v2.0/shredding/) for more information.


---

## Database

---

### db_type

* __description:__ type of database
* __mandatory:__ <span style="background-color:red">yes</span>
* __type:__ string, keyword
* __permissible values__: mysql, pgsql, sqlite (<span style="background-color:orange">taken from PDO drivers documentation, need to check with Etienne)</span>
* __default:__ pgsql
* __available:__ since version 1.0
* __1.x name:__
* __comment:__

### db_host

* __description:__ database host address or name. Typically 127.0.0.1 or localhost.
* __mandatory:__ <span style="background-color:red">yes</span>
* __type:__ string
* __default:__ -
* __available:__ since version 1.0
* __1.x name:__
* __comment:__

### db_port

* __description:__ port used by database server
* __mandatory:__  <span style="background-color:red">yes</span>
* __type:__ string
* __default:__ -
* __available:__ since version 1.0
* __1.x name:__
* __comment:__

### db_username

* __description:__ database username
* __mandatory:__ <span style="background-color:red">yes</span>
* __type:__ string
* __default:__ -
* __available:__ since version 1.0
* __1.x name:__
* __comment:__

### db_password

* __description:__ database password
* __mandatory:__ <span style="background-color:red">yes</span>
* __type:__ string
* __default:__ -
* __available:__ since version 1.0
* __1.x name:__
* __comment:__

### db_database

* __description:__ database name
* __mandatory:__ <span style="background-color:red">yes</span>
* __type:__ string
* __default:__ -
* __available:__ since version 1.0
* __1.x name:__
* __comment:__

### db_table_prefix

* __description:__ This is known to have issues in 2.41 and is now deprecated. This feature will be removed in the 3.x release. table prefix to use.  Allows you to have several filesender instances in one database.  For example if you buy hosting with 1 database and still want multiple filesender instances.
* __mandatory:__ no</yes>
* __type:__ string
* __default:__ -
* __available:__ since version 2.0
* __1.x name:__
* __comment:__ 
  Deprecated. This feature will be removed in the 3.x release


### db_driver_options

* __description:__ Some options to pass to the constructor of DBI objects. This can be used to enable persistent connections.
* __mandatory:__ no
* __type:__ array
* __default:__ array()
* __available:__ since version 2.27
* __comment:__
  You might like to use the following in your config.php to enable persistent connections.

$config['db_driver_options'] = array( PDO::ATTR_PERSISTENT => true );
  
  See https://www.php.net/manual/en/pdo.construct.php



---

## Language and internationalisation

---
FileSender includes a translation engine which allows for flexible user language detection and customisation.  For more details check the [Translating FileSender 2.0 documentation](https://docs.filesender.org/v2.0/i18n/)

User language detection is done in the following order:

1. From the url (`lang` url parameter) : allows for user language switching (only if `lang_url_enabled` set to true in config), if `lang_save_url_switch_in_userpref` is enabled in config and a user session exists the new language is saved in the user preferences so that he doesn't need to switch it again the nex time. If no user session is found the new choice is saved in the PhP session.
2. From the browser's `Accept-Language` header : allows for automatic language detection base on the browser config (if `lang_browser_enabled` set to true in config)
3. From `default_language` config parameter
4. From the hard-coded absolute default `en`

### lang_browser_enabled

* __description:__ detect user's preferred language from browser's Accept-Language header if this header is provided.  If a language a user requests is not available, falls back to the default language.  If no default language is configured, falls back to English.  If a language directive is not available in the selected language, it is taken from the default language file.
* __mandatory:__ no
* __type:__ boolean
* __default:__ true
* __available:__ since version 2.0
* __1.x name:__
* __comment:__ requires lang_url_enabled to be true.

### lang_url_enabled

* __description:__ allow explicit language switching via URL (example: ?lang=en)
* __mandatory:__ no (required when using lang_browser_enabled)
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.0
* __1.x name:__
* __comment:__

### lang_userpref_enabled

* __description:__ take user's preferred language from user's stored preferences.  These preferences are stored in the FileSender database.
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.0
* __1.x name:__
* __comment:__

### lang_selector_enabled

* __description:__ display language selector in UI .  If your FileSender instance only supports 1 language no selector is displayed and no "translate this email" link is present in emails.
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.0
* __1.x name:__
* __comment:__ requires lang_url_enabled to be true.
* __comment:__ <span style="background-color:orange">if the lang_selector is disabled a user can still select different translations in the email translation page</span>
* __comment:__ <span style="background-color:orange">how is determined which language the lang selector defaults to when a user enters a page?  Browser setting?  Order in locale.php? </span>

### lang_save_url_switch_in_userpref

* __description:__ save language switching in user preferences on change (requires lang_url_enabled = true and lang_userpref_enabled = true)
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.0
* __1.x name:__
* __comment:__

---

## Email

---

### email_from

* __description:__ <span style="background-color:orange">sets the email From: header to either an explicit value or fills it with the sender's email address as received from the identity service provider in the "mail" attribute.  Is this the body From:?</span>
* __mandatory:__ no
* __type:__ string or keyword. Permissible value for keyword: "sender"
* __default:__ -
* __available:__ since version 2.0
* __1.x name:__
* __comment:__ To be SPF compliant set this to an address like "filesender-bounces@example.org" and use the bounce-handler script to deal with email bounces.

### email_from_name

* __description:__ pretty name for the email_from address.  Use when you explicitly set email_from to an email address like "no-reply@example.org".
* __mandatory:__ no
* __type:__ string
* __default:__ -
* __available:__ since version 2.0
* __1.x name:__
* __comment:__

### email_reply_to

* __description:__ <span style="background-color:orange">adds a reply-to: header to emails sent by FileSender.  When users reply to such an email usually the reply is then sent to the reply_to address.  A user would typically reply to an email to ask a question about a file transfer which should go directly to the sender as the sender is the only one who knows.</span>
* __mandatory:__ no
* __type:__ string or keyword.  Permissible values for keyword: "sender"
* __default:__ -
* __available:__ since version 2.0
* __1.x name:__
* __comment:__ To be SPF compliant set this to "sender"

### email_reply_to_name

* __description:__  pretty name for the email_reply_to address.  Use when you explicitly set email_reply_to to an email address like "no-reply@example.org".
* __mandatory:__ no
* __type:__ string
* __default:__ -
* __available:__ since version 2.0
* __1.x name:__
* __comment:__

### email_return_path

* __description:__ <span style="background-color:orange">sets the return_path email header to either an explicit value or fills it with the sender's email address as received from the identity service provider in the "mail" attribute. Is this the envelope from??</span>
* __mandatory:__ <span style="background-color:orange">no</span>
* __type:__ string or keyword. Permissible value for keyword: "sender"
* __default:__ -
* __available:__ since version 2.0
* __1.x name:__
* __comment:__ To be SPF compliant set this to an address like "filesender-bounces@example.org" and use the bounce-handler script to deal with email bounces.

### email_subject_prefix

* __description:__ the string specified here will be prepended to the subject of all emails sent out.
* __mandatory:__ no
* __type:__: string
* __default:__ site_name config directive
* __available:__ since version 2.0
* __1.x name:__
* __comment:__ was equal to site_name in version 1.x

### email_use_html

* __description:__ if true all emails sent by FileSender will include both HTML and plaintext.  For most users this means they will see HTML emails. If false only plain-text emails are sent.
* __mandatory:__ no
* __type:__ boolean
* __default:__ true
* __available:__ <span style="background-color:orange">since version 2.0 (?)</span>
* __1.x name:__
* __comment:__

### email_newline

* __description:__ specify a different new line character for use in emails. If your FileSender emails look garbled (display raw MIME and HTML source) try setting this to \n as an alternative to reconfiguring your mail server.
* __mandatory:__ no
* __type:__ string
* __default:__ "\r\n" (as per RFC 2822)
* __available:__ since version 1.0
* __1.x name:__ crlf
* __comment:__ the default value in version 1.x was "\n".
* __comment:__ Make sure you use double quotes to configure this value in the config file.  If you use single quotes the \r and \n will NOT be interpreted!

### email_headers

* __description:__ specify additional RFC 822 (today RFC 5322) headers to be added to outgoing emails sent by FileSender.
* __mandatory:__ no
* __type:__ array of 2-tuples (header name, header value)
* __default:__ false
* __available:__ since version 2.x
* __comment:__ E.g. add to your `$config['email_headers'] = array('Auto-Submitted' => 'auto-generated', 'X-Auto-Response-Suppress' => 'All');` to add these 2 headers with their respective values to all outgoing emails.


### email_send_with_minus_r_option

* __description:__ Use the -r option to mail() if return_path is set. This was the default behavior in all 2.x series released but the FileSender 2.41 release.
* __mandatory:__ no
* __type:__ boolean
* __default:__ true
* __available:__ since version 2.42
* __comment:__ This may allow for FileSender in container installations to work where the -r option is not desired and can be turned off for that.


### relay_unknown_feedbacks

* __description:__ tells the bounce handler where to forward those messages it can not identify as email bounces but can be related to a specific target (recipient, guest). The received message is forwarded as message/rfc822 attachment. Updated in 2.6.
* __mandatory:__ no
* __type:__ string or keyword
* __permissible values:__ "sender": relay to recipient's transfer owner or guest owner. "admin": relay to admin email address. "support": relay to support_email (setting to this means that support_email must also be set), or "someaddress@example.org": an explicit email address to forward these types of mails to.
* __default:__ "sender"
* __available:__ since version 2.0
* __1.x name:__
* __comment:__ <span style="background-color:orange">this parameter will get a different name</span>


### translatable_emails_lifetime

* __description:__ This is the number of days to retain translatable emails in the database.
* __mandatory:__ no
* __type:__ int
* __default:__ 30
* __available:__ since before version 2.30


### trackingevents_lifetime

* __description:__ This is the number of days to retain tracking events in the database. 
* __mandatory:__ no
* __type:__ int
* __default:__ 90
* __available:__ since before version 2.30
* __comment:__ See classes/constants/TrackingEventTypes.class.php for types of tracking events. As of 2.30 they are limited to email bounces.

## General UI

### theme

* __description:__ This allows you to select a custom theme by creating a subdirectory inside the normal template directory and naming it here.
* __mandatory:__ no
* __type:__ string
* __default:__ ''
* __available:__ since version 2.8
* __comment:__ You can not select absolute or relative paths using this parameter. Your theme directory must exist inside the existing template directory.



### autocomplete

* __description:__ provide autocomplete for email input fields.  If set to a positive integer autocomplete is enabled and the value dictates how many results are returned to a user in the autocomplete popup.  The result list is limited to recipients this particular user has used.
* __mandatory:__ no
* __type:__ integer/boolean
* __default:__ false
* __available:__ since version 1.6
* __1.x name:__
* __comment:__ Checks the frequent recipient field (array) in the user preference table. Holds different recipients the user did use. The first one is the last used one. Every time the user sends a file or guest voucher we take recipiients and add them at the top of the array. If they already exist in the array the address is put to the top. We limit array to max length defined in config.

### autocomplete_max_pool

* __description:__ how many of the user's recipients are stored in the user's preferences in the database.  Should be between 2 and ca. 15 times the "autocomplete" value.
* __mandatory:__ no
* __type:__ int
* __default:__ 5 times the value set for autocomplete
* __available:__ since version 1.6
* __1.x name:__ autocompleteHistoryMax
* __comment:__ the higher this number the larger the number of email recipients you will store over time.  This increases your privacy footprint.

### autocomplete_min_characters

* __description:__ how many characters the user needs to type in an email address field to trigger the autocomplete popup.
* __mandatory:__ no
* __type:__ int
* __default:__ 3 <span style="background-color:orange">(might 2 be better?)</span>
* __available:__ since version 2.0
* __1.x name:__
* __comment:__

### upload_show_play_pause

* __description:__ Show buttons to allow an upload to pause, resume, and stop
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 3.0
* __comment:__ 

### upload_display_bits_per_sec

* __description:__ <span style="background-color:orange">if true display upload speed in MBps (megabytes/second).  If false display upload speeds as Mbps (megabits/second) Need to test, reality seems different from documentation</span>
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.0
* __1.x name:__
* __comment:__ does this actually work?

### upload_display_per_file_stats
* __description:__ show the duration of the current chunk for each worker to the user during uploads
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.0
* __1.x name:__



### upload_force_transfer_resume_forget_if_encrypted
* __description:__ forget partial transfers when upload page is revisited if they were encrypted.
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.0


### upload_considered_too_slow_if_no_progress_for_seconds
* __description:__ If 0 this is disabled. If an uploading chunk has not reported any progress in this number of seconds then it is considered in trouble and some action may be taken (eg. force stop and resend of chunk) to try to recover. Note that this relies on the browser to report progress messages for ongoing uploads which might only happen every few seconds if a single request is active and maybe for terasender_worker_count=5 you might like to set this to 20 or 30 to avoid thinking a chunk is stalled when it is not. The implementation currently relies on the [XMLHttpRequest onprogress event](https://xhr.spec.whatwg.org/#event-xhr-progress).
* __mandatory:__ no
* __type:__ int
* __default:__ 30
* __available:__ since version 2.0



### crypto_pbkdf2_dialog_enabled
* __description:__ If set to true then a dialog is displayed with a key is being generated from a user supplied password. Such an action can be quite computationally expensive and may take half a minute to many minutes depending on your expectation of security for password based keys. See encryption_password_hash_iterations_new_files for an explanation of the delay expectation.
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.12


### crypto_pbkdf2_delay_to_show_dialog
* __description:__ If crypto_pbkdf2_dialog_enabled is true then this is a delay that must pass before the dialog is shown. If PBKDF2 completes before this delay then no dialog is shown. Note that you should be seeing the dialog for password hashing iteration counts that offer a reasonable expectation of security.
* __mandatory:__ no
* __type:__ integer
* __default:__ 300
* __available:__ since version 2.12


### crypto_pbkdf2_expected_secure_to_year
* __description:__ PBKDF2 is a method to convert a user supplied password into a cryptographic key. See https://en.wikipedia.org/wiki/PBKDF2. One parameter of PBKDF2 is the number of iterations to be performed (encryption_password_hash_iterations_new_files). This parameter will override encryption_password_hash_iterations_new_files based on how many iterations are expected to be needed to resist a brute force attack until the given year. See the admin/testing page to calculate how long it will take to perform PBKDF2 with various year settings on your computer. At upload time this setting is stored in the database for an upload. This allows the file(s) to be downloaded again using the correct number of iterations while the system administrator can alter this value to change the setting for new uploads. Note that setting this to false will allow you to directly set encryption_password_hash_iterations_new_files if that is what you prefer.
* __mandatory:__ no
* __type:__ integer
* __default:__ 2027
* __available:__ since version 2.12


### crypto_pbkdf2_dialog_custom_webasm_delay
* __description:__ The custom webasm PBKDF2 code can stop the PBKDF2 dialog from appearing because the webasm code takes control until key generation is complete. This delay allows the dialog to appear so the user does not think things are frozen. Note that this delay is not used for native WebCrypto PBKDF2, only for the custom webasm that is used when the browser does not support PBKDF2.
* __mandatory:__ no
* __type:__ integer
* __default:__ 1000
* __available:__ since version 2.14


### upload_page_password_can_not_be_part_of_message_handling
* __description:__ Can be one of 'none', 'warning', or 'error'. If the string does not match a known valid value it is reset to 'warning'. If this is 'warning' and the user is performing an encrypted upload and types their password into the message field then a warning message is displayed until the user removes the password from the message. If this is 'error' then a more stern message is displayed informing the user that they will not be allowed to continue until the password is removed from the message text. If this is 'none' then no checks are performed and the test is effectively disabled.
* __mandatory:__ no
* __type:__ string
* __default:__ 'warning'
* __available:__ since version 2.22


### user_page
* __description:__ This is an array describing which features should be offered on the user "my profile" page.
* __mandatory:__ no
* __type:__ array
* __default:__ array('lang'=>true,'auth_secret'=>true,'id'=>true,'created'=>true)
* __available:__ since before version 2.30
* __comment:__ To show an item set the value for the name of the item to true.
     For more possible values to include in the array see the second level keys in $infos on the templates/user_page.php file.


### allow_pages_core
* __description:__ The pages that should be available to all visitors before logging in. Note that if you include some pages such as transfers
                   and the system requires the user to be logged in to view the page they will be redirected to login to view the page. The default
                   value should be acceptable to most sites.
* __mandatory:__ no
* __type:__ array of values from GUIPages constants
* __default:__ array( GUIPages::DOWNLOAD, GUIPages::TRANSLATE_EMAIL, GUIPages::LOGOUT, GUIPages::EXCEPTION, GUIPages::HELP, GUIPages::ABOUT, GUIPages::PRIVACY )
* __available:__ since version 2.33
* __comment:__ See also allow_pages_add_for_guest and allow_pages_add_for_user


### allow_pages_add_for_guest
* __description:__ These values will be added to the allow_pages_core pages if the principal is a guest
* __mandatory:__ no
* __type:__ array of values from GUIPages constants
* __default:__ array( GUIPages::HOME, GUIPages::UPLOAD, GUIPages::APISECRETAUP )
* __available:__ since version 2.33
* __comment:__ See also allow_pages_core

### allow_pages_add_for_user
* __description:__ These values will be added to the allow_pages_core pages if the principal is an authenticated user (normal or admin). Note that GUIPages::USER will be removed if
                you have set $config['user_page'] = null.
* __mandatory:__ no
* __type:__ array of values from GUIPages constants
* __default:__ array( GUIPages::HOME, GUIPages::USER, GUIPages::UPLOAD, GUIPages::TRANSFERS, GUIPages::GUESTS, GUIPages::DOWNLOAD, GUIPages::APISECRETAUP )
* __available:__ since version 2.33
* __comment:__ See also allow_pages_core


### allow_pages_add_for_admin
* __description:__ These values will be added to the allow_pages_core pages if the principal is an authenticated admin. This will be in addition to the values from allow_pages_add_for_user.
* __mandatory:__ no
* __type:__ array of values from GUIPages constants
* __default:__ array( GUIPages::ADMIN )
* __available:__ since version 2.33
* __comment:__ See also allow_pages_core


### can_view_statistics
* __description:__ Access to the statistics tab is governed by this setting which has similar format to the admin setting. This is a comma separated list of uids which will be matched against the saml_user_identification_uid column in the authentications table of the database. Normally this is the full email of the user who is logging in. You can also use the auth_sp_saml_can_view_statistics_entitlement and auth_sp_saml_can_view_aggregate_statistics_entitlement settings to enabled these features using attributes from SAML.
* __mandatory:__ no
* __type:__ string
* __default:__ ""
* __available:__ since version 2.8
* __comment:__ See also can_view_aggregate_statistics, admin

### can_view_aggregate_statistics
* __description:__ Access to the statistics tab is governed by this setting which has similar format to the admin setting. This is a comma separated list of uids which will be matched against the saml_user_identification_uid column in the authentications table of the database. Normally this is the full email of the user who is logging in. You can also use the auth_sp_saml_can_view_statistics_entitlement and auth_sp_saml_can_view_aggregate_statistics_entitlement settings to enabled these features using attributes from SAML.
* __mandatory:__ no
* __type:__ string
* __default:__ ""
* __available:__ since version 2.8
* __comment:__ See also can_view_statistics

### auth_sp_saml_can_view_statistics_entitlement
* __description:__  This is the name of an entitlement_attribute from the authentication session that will grant an authenticated user access to the statistics page. For full details see the Auth::isPrivilegeAllowed() method.
* __mandatory:__ no
* __type:__ string
* __default:__ ""
* __available:__ since version 2.8
* __comment:__ See also can_view_statistics

### auth_sp_saml_can_view_aggregate_statistics_entitlement
* __description:__  This is the name of an entitlement_attribute from the authentication session that will grant an authenticated user access to the aggregate statistics page. For full details see the Auth::isPrivilegeAllowed() method.
* __mandatory:__ no
* __type:__ string
* __default:__ ""
* __available:__ since version 2.8
* __comment:__ See also can_view_statistics


### read_only_mode
* __description:__  Do not allow new transfers and guests to be created.
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.48
* __comment:__ If you are performing a major upgrade you might like to retain an original FileSender installation in read only mode so users can continue to download existing files and redirect visitors to a new site for new uploads. This may be useful for upgrading between major FileSender releases such as the 2.x series to the 3.x series and also for change in infrastructure such as moving to different disk pools or storage back ends.


### date_format_style
* __description:__  High level selection of the style to format a date with.
* __mandatory:__ no
* __type:__ string
* __default:__ medium
* __available:__ since version 3.0beta7
* __comment:__ This can be one of full, long, medium, or short. This will be used to format dates and times with the locale according to IntlDateFormatter. The local is taken from the user profile, and then from the http accepted languages sent from the browser so it should match which language and locale the user is most confortable with. See for example https://www.php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants This replaces the use of the date_format translation string in the 2.x series of FileSender.


### time_format_style
* __description:__  High level selection of the style to format a date with a time component with.
* __mandatory:__ no
* __type:__ string
* __default:__ medium
* __available:__ since version 3.0beta7
* __comment:__ This can be one of full, long, medium, or short. This will be used to format dates and times with the locale according to IntlDateFormatter. The local is taken from the user profile, and then from the http accepted languages sent from the browser so it should match which language and locale the user is most confortable with. See for example https://www.php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants This replaces the use of the datetime_format translation string in the 2.x series of FileSender.


### make_download_links_clickable
* __description:__  Allow the user to click on links to downloads instead of needing to copy and paste them to navigate to the transfer.
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 3.0beta7
* __comment:__ The transfer link can be clicked on when get a link is used and an upload is compete.


### valid_timezone_regex
* __description:__  A full php regex expression including the leading and trailing //i type characters to match a valid timezone string sent from the browser
* __mandatory:__ no
* __type:__ string (php regex including the leading and trailing //i characters)
* __default:__ '@^[_/a-z]+$@i'
* __available:__ since version 3.0beta7
* __comment:__ This regex is used to match timezone data passed from the browser. If the regex does not match the timezone is considered invalid and ignored. Set this to '' to explicitly disable this feature.


### client_send_current_timezone_to_server
* __description:__  If enabled the client will send the current timezone to the server. This could be a privacy issue so it is off by default.
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 3.0beta7
* __comment:__ If enabled the client will share the current timezone setting to the server so it can format dates as the client expects.


---

## Transfers

---

### aup_enabled

* __description:__ If set to 'true' the AuP (terms of service) checkbox is visible AND mandatory for the user to tick.
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 1.0
* __1.x name:__ AuP
* __comment:__

### aup_default

* __description:__ if set to 'true' the AuP (terms of service) checkbox (if enabled) is already pre-ticked for the user
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 1.0
* __1.x name:__ AuP_default
* __comment:__

### api_secret_aup_enabled

* __description:__ If set to 'true' the AuP (terms of service) must be accepted before the api secret can be created. Note that if this setting is enabled then auth_remote_user_autogenerate_secret will be disabled.
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.15
* __comment:__


### ban_extension

* __description:__ disallow files with the extensions specified here.
* __mandatory:__ no
* __type:__ string
* __default:__ exe, bat
* __available:__ since version 1.0
* __1.x name:__
* __comment:__

### chunk_upload_security

* <span style="background-color:orange">this entire parameter needs to be checked with Etienne</span>
* __description:__ controls how FileSender behaves when an upload lasts longer than an authenticated user session.  If set to "key" the web client will use FileUID as a transfer session key.  This transfer session key is valid for as long as the upload lasts independent from the user's login session.  So if a user logs in at some identity provider and that session expires after e.g. 8 hours but the upload lasts for 10 hours, the upload will complete.  If set to "auth" the user will be required to re-logon if their logon session has expired before an upload completed.  __If set to "key" and the user's login session expires before the upload is completed, the user will need to be logged on before redirected to their "My Transfers" page.__
* __mandatory:__ no
* __type:__ string keyword
* __permissible values:__ "key" or "auth"
* __default:__ key
* __available:__ since version 2.0
* __1.x name:__
* __comment:__ When you upload a file it is uploaded in chunks.  For each chunk there is a check whether the chunk belongs to a valid session and it ensures the right chunks are appended to the right files.  You don't want others to be able to insert chunks in a user's file as it would lead to file corruption.  In version 1.x this check was done with the user's session (login) identifier which from a security point of view worked well.  The only problem is that sometimes uploads take a long time, depending on file size and upload speeds.  A user's login session can then expire before the upload is complete.  Most FileSender installations in national research networks use SAML-based authentication.  A user logs in to an Identity Provider (IdP), this IdP sends a SAML-token to the Service Provider (SP, your FileSender instance) containing information like the session authentication token.  This SAML-token also contains a timestamp which indicates exactly when the user's login session expires. FileSender CAN NOT change this session expiry time as the authentication libraries it uses honour this value.  It's the Identiy Provider that makes this choice.  In Norwegian higher education for example a login session with the national authentication infrastructure expires after 8 hours.  We have seen uploads that last longer than that.

To solve this we introduced a transfer key in FileSender 2.0. When you start an upload you use the FileUID as a unique transfer session key. If the user session times out before the upload is done, the upload will still continue.  The transfer session key expires immediately once the upload is done. The upload is secure: you need an authenticated session to start an upload, only the server and the uploading client have knowledge of the FileUID. Third parties can not inject chunks.

If you want to find out the expiry timer for your SAML Identity Provider install [the SAML tracer add-on in FireFox](https://addons.mozilla.org/en-us/firefox/addon/saml-tracer/) and log in to your FileSender install.  Click on the "SAML" message in SAML tracer.

### default_transfer_days_valid

* __description:__ specifies the default expiry date value in the "Expiry date" date picker in the Upload form.  If a user doesn't do anything this becomes the expiry date for the transfer.
* __mandatory:__ no
* __type:__ int
* __default:__ 10
* __available:__ since version 1.0
* __1.x name:__ default_daysvalid
* __comment:__ Be aware of the changed semantic from 1.6 to 2.0.

### max_transfer_days_valid

* __description:__ specifies the maximum expiry date for a transfer.  A user can not choose a larger value than this.
* __mandatory:__ no
* __type:__ int
* __default:__ 20
* __available:__ since version 1.0
* __1.x name:__ default_daysvalid
* __comment:__ experience shows the vast majority of users simply go with the default expiry time.  For some users having a maximum value a long time in the future makes sense, e.g. papers sent out to a research proposal evaluation committee that need to be evaluated by a certain date.  Downloads typically start not too long before the due date, but the actual due date can be over a month in the future.

### allow_transfer_expiry_date_extension

* __description:__ allows a user to extend the expiry date. See also allow_transfer_expiry_date_extension_admin to allow admins special extension ability.
* __mandatory:__
* __type:__ an array of integers containing possible extensions in days.
* __default:__ - (= not activated)
* __available:__ since version 2.0
* __1.x name:__
* __comment:__
* __Examples:__

	$config['allow_transfer_expiry_date_extension'] = array(5); // Allows a single extension of 5 days
	$config['allow_transfer_expiry_date_extension'] = 5; // Same as above
	$config['allow_transfer_expiry_date_extension'] = array(5, 3); // Allows 2 successive extensions, the first is by 5 days the second is by 3 days
	$config['allow_transfer_expiry_date_extension'] = array(5, 3, 1, true); // Allows infinite extensions, the first is by 5 days the second is by 3 days, the third and above are by 1 day

### allow_transfer_expiry_date_extension_admin

* __description:__ allows an admin to extend the expiry date. This is similiar to allow_transfer_expiry_date_extension but is only used if you are logged in as an admin on the system. If you are an admin this schedule will overwrite the allow_transfer_expiry_date_extension for you. So you can set both and this will be used in preference if you are logged in as admin, otherwise allow_transfer_expiry_date_extension will be used if it is set. As you might only like to use this option and not allow users to extend transfers this option may offer a second UI element to allow extension, where there are two ways to extend a transfer they will both perform the same action and follow the admin configuration if you are logged in as admin.
* __mandatory:__
* __type:__ an array of integers containing possible extensions in days.
* __default:__ - (= not activated)
* __available:__ since version 2.21
* __Examples:__

        // Allows infinite extensions, the first is by 30 days then 90 days 
	$config['allow_transfer_expiry_date_extension_admin'] = array(30, 90, true); 

## force_legacy_mode

* __description:__ Force FileSender into legacy non-HTML5 mode. Multi-file uploads are still possible, but each file is limited to max. 2GB.  The help file and certain text labels change as well. The max. number of files and total transfer size limit is the same as for HTML5 mode.  This function is available for testing purposes: FileSender will detect automatically if a user's browser supports the necessary HTML5 functionality or not.
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.0
* __1.x name:__
* __comment:__ for testing purposes.

### legacy_upload_progress_refresh_period

* __description:__ when uploading in legacy mode (non-HTML5 uploads) this indicates in seconds how often the client-side progress bar is refreshed.
* __mandatory:__ no
* __type:__ int (seconds)
* __default:__ 5.  Setting this to 0 is not a wise choice as it will make the timer refresh every millisecond (the min. value for a JavaScript timer)
* __available:__ since version 2.0
* __1.x name:__
* __comment:__ Normally FileSender will use the browser's HTML5 FileAPI functionality for uploading, splitting files in chunks and uploading these chunks.  This allows for uploads of any size.  Older browsers which you may find in a locked-down environment do not support the necessary HTML5 functionality.  For these browsers a legacy fallback upload method is provided.  This uses a native HTML upload with a limit of 2GB per file.  A user **can** select multiple files but in a less smooth way than with the HTML5 drag & drop box.  The upload progress for legacy uploads is polled from the server (via PHP) based on what has arrived (how many bytes) server side.  <span style="background-color:orange">This only became possible as of PHP version 5.x, released in x</span>

### max_legacy_file_size

* __description:__ maximum size per file for a legacy upload.  <span style="background-color:orange">With a legacy upload users can upload x files per transfer.</span>.
* __mandatory:__ no
* __type:__ int
* __default:__ 2147483648 (2GB)
* __available:__ since version 1.0
* __comment:__ Files are uploaded serially.  A hidden iframe and hidden form is created for each file, containing the required data (session key for upload etc.).  A single file element is cloned into each hidden form.  This form is submitted to the hidden iframe which then uploads the file.  At the end of the upload the server sends a bit of javascript which triggers the next upload in the queue.  Each file is an "entire file at once" upload rather then the chunked upload used to get over the 2GB limit of 32 bit browsers.

### max_transfer_size

* __description:__ maximum total size for any transfer (both html5 and legacy transfers)
* __mandatory:__ no
* __type:__ int
* __default:__ 107374182400 (100 GB)
* __available:__ since version 1.0
* __1.x name:__ max_html5_upload_size
* __comment:__

### max_transfer_files

* __description:__ maximum number of files that can be sent in one transfer
* __mandatory:__ no
* __type:__ int
* __default:__ 30
* __available:__ since version 2.0
* __1.x name:__
* __comment:__

### max_transfer_recipients

* __description:__ maximum number of recipients a transfer can have.
* __mandatory:__ no
* __type:__ int
* __default:__ 50
* __available:__ since version 1.0
* __1.x name:__ max_email_recipients
* __comment:__

### transfer_options

* __description:__ this parameter controls which transfer options are available to the user in the Upload form and how these options behave.  Options show up in the right hand side block in the Upload form.  Options appear in the order they are specified in the config file.  Most options control which email receipts are sent out when and to whom.  See below for details.
* __mandatory:__ no
* __type:__ array
* __default:__
* __available:__ since version 2.0
* __1.x name:__
* __comment:__ For current defaults see https://github.com/filesender/filesender/search?q=transfer_options+in%3Afile+path%3Aincludes
* __*Standard parameters for all options:*__
	* __available__(boolean): if set to true then this option shown in the upload form
	* __advanced__ (boolean): if set to true the option is hidden under an "Advanced options" click-out.  The user must click "Advanced" to make the option visible.
	* __default__ (boolean): if set to true then this option is ticked by default.  If set to true while __*available*__ is set to false the option is mandatory for all users, it can not be switched off by the user.
* __*Available options:*__
	* __email\_me\_copies:__ the sender receives copies (Cc:) of all emails concerning this transfer.  This is the "spam-me-plenty" option.
	* __email\_me\_on\_expire:__ the sender receives a message when the transfer expires.
	* __email\_upload\_complete:__ send the sender an email once the sender's upload is finished.  This allows a sender to start a long upload on a workstation when leaving work and check with a smartphone whether the upload was completed some hours afterwards.
	* __email\_download\_complete:__ notify the sender (owner) of a transfer that someone has downloaded it immediately after the download completes.
	* __email\_daily\_statistics:__ send the sender an overview of all activity on that sender's transfers.  Who downloaded what when.
	* __email\_report\_on\_closing:__ send the sender an overview of all activity on this particular transfer after that transfer is closed.  This is the audit report for that particular transfer.  When a sender receives this, the server's audit logs can (in principle) be purged for the records pertaining to this particular transfer thus reducing FileSender's privacy footprint.
	* __email\_recipient\_when\_transfer\_expires:__ As of release 2.21 this is a global default setting to email users when a transfer expires during cron execution. The default is true to maintain the previous functionality as it was. Setting this to false will not send out emails to intended recipients as transfers are expired by the cron job. This is set here because it may be able to be adjusted by a user in the UI in the future for each transfer.
	* __enable\_recipient\_email\_download\_complete:__ this gives the downloader a tick box in the download window which in turn lets the downloader indicate they would like to receive an email once the download is finished.  If you want this option available for all downloaders and do not want to bother the uploader with it, simply configure it with 'default' => false as the only parameter. __Warning:__ if the recipient of a file is a mailinglist and someone ticks the "send me a message on download complete" box, then all members of that mailinglist will receive that message.  That might be a reason why you don't want to make this option available to your users.        
	* __add\_me\_to\_recipients:__ include the sender as one of the recipients.
	* __get\_a\_link:__ if checked it will not send any emails, only present the uploader with a download link once the upload is complete.  This is useful when sending files to mailinglists, newsletters etc.  When ticked the message subject and message text box disappear from the UI.  Under the hood it creates an anonymous recipient with a token for download.  You can see the download count, but not who downloaded it (obviously, as there are no recipients defined).
	* __hide\_sender\_email:__ If checked it will hide the sender's email address on the download page. The option is only displayed if the __get\_a\_link__ option is checked. This is useful when sending download links to mailing lists, etc., and you do not want your personal email account to be displayed on the download page.
	* __redirect_url_on_complete:__ When the transfer upload completes, instead of showing a success message, redirect the user to a URL. This interferes with __get\_a\_link__ in that the uploader will not see the link after the upload completes. Additionally, if the uploader is a guest, there is no way straightforward way for the uploader to learn the download link, although this must not be used as a security feature.
	* __must_be_logged_in_to_download__ (boolean): To download the files the user must log in to the FileSender server. This allows people to send files to other people they know also use the same FileSender server.
	* __web_notification_when_upload_is_complete__: Added in release 2.32. Options include available, advanced, and default. If you wish to use this feature you should set available=true to allow the user to see the option. Some browsers such as Firefox require the user to explicitly click a link to start the acceptance dialog so being able to see the option (available=true) on the web page is very useful. Using notifications will require the user to accept them for the site. Currently as of release 2.32 a notification can be sent when the upload is complete.

* __*Configuration example:*__

		$config['transfer_options'] = array(
			'email_upload_complete' => array(
				'available' => true,
				'advanced' => false,
				'default' => false
			),
			'email_me_copies' => array(
				'available' => true,
				'advanced' => true,
				'default' => false
			)
		);

### upload_chunk_size

* __description:__ standard upload for FileSender is chunked upload.  This indicates how big each chunk is.  There is a certain optimal chunk size which depends largely on your bandwidth-delay product.  Usually you shouldn't have to touch this but if you're trying to serve special use cases you might want to experiment with this and see which value gives you the fastest upload times..
* __mandatory:__ no
* __type:__ int (bytes)
* __default:__ 5 \* 1024 \* 1024 (5242880 bytes (5MB))
* __available:__ since version 1.5
* __1.x name:__
* __comment:__ Please note that as of version 2.0 the terasender_chunksize and upload_chunk-size have been merged into one parameter.

### user_quota

* __description:__ set to 0 to disable.  If set to a positive value it sets the per-user maximum storage usage. A transfer requiring more space than remains in the user's quota are rejected with an error message in the web-UI.
* __mandatory:__ no
* __type:__ int (bytes) or function
* __default:__ 0
* __available:__ since version 2.0
* __1.x name:__
* __comment:__ user quote can be implemented in a much more flexible way as well.  As we're doing lazy loading of configuration parameters we can change this value (and max. file size) based on user profile.  In stead of defining this config parameter with a number you can give a function to it.  The value returned by this function is cached for a login session.  For example a function that uses eduPersonAffiliation can give a "student" 10 GB and "faculty" 1 TB.  You could also change max. days valid based on user profile.  The function can use the current application state and user session to compute the value for a logged in user, because the function would run after everything else.  <span style="background-color:orange">Calculated maximum values should have its own chapter to explain, with examples especially for using eduPersonAffiliation.</span>


### max_transfer_file_size
* __description:__ set to 0 to disable. If set to a positive value it sets the maximum file size for a not encrypted file that the user can upload. Attempts to upload a larger file is rejected with an error message in the web-UI.
* __mandatory:__ no 
* __type:__ int (bytes) or function
* __default:__ 0
* __available:__ since version 2.0
* __comment:__ 


### max_transfer_encrypted_file_size
* __description:__ set to 0 to disable. If set to a positive value it sets the maximum file size for an encrypted file that the user can upload. Attempts to upload a larger file is rejected with an error message in the web-UI.
* __mandatory:__ no 
* __type:__ int (bytes) or function
* __default:__ 0
* __available:__ since version 2.0
* __comment:__ 

### disable_directory_upload
* __description:__ Disables the functionality to upload entire directories from the UI
* __mandatory:__ no
* __type:__ bool
* __default:__ true
* __available:__ since version 2.0
* __comment:__ Set this to false to enable the directory upload functionality

### directory_upload_button_enabled]
* __description:__ Enables a button for directory upload on supported browsers
* __mandatory:__ no
* __type:__ bool
* __default:__ true
* __available:__ since version 2.6
* __comment:__ Only on Firefox and Chrome in default templates

### encryption_enabled
* __description:__ set to false to disable. If set to true an option to enable file encryption of a transfer becomes available in the web-UI.
* __mandatory:__ no
* __type:__ boolean
* __default:__ true
* __available:__ since version 2.0
* __comment:__

### encryption_mandatory
* __description:__ If set to true then every file uploaded must be encrypted.
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.23
* __comment:__

### encryption_mandatory_with_generated_password
* __description:__ If set to true then every file uploaded must be encrypted and use a generated password. This enables encryption_mandatory automatically.
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.40
* __comment:__




### encryption_min_password_length
* __description:__ set to 0 to disable. If set to a positive value it is the minimum number of characters needed in a password for encryption. Note that since the encryption is fully client side, this value could be ignored by a determined user, though they would do that at the loss of their own security not of others.
* __mandatory:__ no 
* __type:__ int
* __default:__ 0
* __available:__ since version 2.0
* __comment:__ 

### encryption_password_must_have_upper_and_lower_case
* __description:__ set to true to force a user entered password to contain uPPer and LoWer case characters.
* __mandatory:__ no 
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.23
* __comment:__ 

### encryption_password_must_have_numbers
* __description:__ set to true to force a user entered password to contain numbers 453543.
* __mandatory:__ no 
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.23
* __comment:__ 

### encryption_password_must_have_special_characters
* __description:__ set to true to force a user entered password to contain special characters (%$^@ etc).
* __mandatory:__ no 
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.23
* __comment:__ 


### encryption_password_text_only_min_password_length
* __description:__ If this is set then a password can avoid the password_must_have checks if it is at least this long.
* __mandatory:__ no 
* __type:__ int
* __default:__ 40
* __available:__ since version 2.26
* __comment:__ Set to 0 to disable. Using this setting allows a passphrase that might contain all human language words without numbers, special characters etc
     but which is still difficult enough to guess by brute force due to it's length and thus combination of words. If this setting is set to say 40 then
     a password that is 40+ characters long will be accepted even when the encryption_password_must_have directives are in use and the password does not have
     the must_have constraints met.




### encryption_generated_password_encoding
* __description:__ It is highly recommended to leave the default value (ie not set in your config.php). Which encoding to use to encode generated passwords. Since the random information obtained during password generation is completely random it is useful to encode that into text characters, for example in the range a,b,c etc. By doing this one single byte of random data (0 to 255 inclusive) will likely be encoded to more than one character of output. The base64 encoding turns x bytes of input into 1.33 times as long output. Because ascii85 uses more possible characters it turns each 4 bytes into 5 bytes. This means that for the same length of encoded string the ascii85 will have more entropy. Note that the ascii85 used is the Z85 from ZeroMQ to avoid the use of the quote character in output.
* __mandatory:__ no 
* __type:__ string
* __default:__ base64
* __available:__ since version 2.1
* __comment:__ either base64 or ascii85 



### encryption_key_version_new_files
* __description:__ Select which user password hashing is performed and which AES mode is used for encryption.
    Some mores have versions with and without key hashing because some browsers do not support the key hashing.
    The choices in order of newest to oldest are: 3 is v2019_gcm_importKey_deriveKey
     which is AES-GCM mode for encryption and using PBKDF2 to derive a key from user supplied passwords.
     A PBKDF2 related configuration setting is crypto_pbkdf2_expected_secure_to_year.
     The setting 3 is the recommended setting unless you have to support older browsers which can not
     work with this level of security.

     The setting 2 is v2019_gcm_digest_importKey which uses AES-GCM for encryption but almost directly imports the user password without any key hashing.
     The setting 1 is v2018_importKey_deriveKey which uses AES-CBC mode for encryption and PBKDF2 for hashing the password.
     The setting 0 is v2017_digest_importKey which uses AES-CBC mode for encryption and directly imports the password without hashing.
     Notice that version 0 is like 1 but without key hashing and version 2 is like 3 but without key hashing.

     It is expected that this configuration option may be ignored by a system administrator unless you wish to support older web browsers and thus force a specific older key version to be used for all files. You will want version 0 if you wish to support IE11 clients. From late 2018 through to 2021 the default is version 1. It is likely that the default will be version 3 for FileSender 3.x.
     
     The way encryption keys are derived from the supplied or generated password may change over time. Generally this is done to improve security, though it may also exclude certain older web browsers due to some features being missing in the older browser.

     This setting is the default key version to use for new files. The key version used to encrypt a file is stored in the database for each transfer and sent to allow anybody downloading the file to use the correct key version to properly decrypt the file. This way, new improved code can be issued and existing files which use older key versions can still be downloaded and decrypted. This allows migration to newer code as new FileSender releeases are made while allowing users to still download older encyrpted content. 

* __recommend_leaving_at_default:__ true
* __mandatory:__ no 
* __type:__ int
* __default:__ 3
* __available:__ updated in 3.0 beta7 an above to 3, was 1 since version 2.6
* __comment:__


### encryption_random_password_version_new_files
* __description:__ It is highly recommended that this value should be able to be left as the default (ie not set in your config.php). As new random passwords are created the version of code used for that transfer is stored in the database to allow this configuration setting to change and existing files to continue to be downloadable and decrypted. The config setting has been brought out in order to allow older code to still be used for a limited amount of time if desired. As of filesender 2.9 the default value of (2) means that when a random password is generated it will be 256 bits of random information which is then encoded to base64. Encoding to base64 allows the user to easily communicate the password. Note that while the user sees the base64 encoded password, the decoded binary data is used internally during encryption and decryption. This value sets which version of random password to use when making new passwords. It is highly recommended to leave the default value, ie. do not set anything in your config.php for this setting unless you need to force an older version for some reason.
* __recommend_leaving_at_default:__ true
* __mandatory:__ no 
* __type:__ int
* __default:__ latest version that the code supports.
* __available:__ since version 2.9
* __comment:__



### encryption_password_hash_iterations_new_files
* __description:__ This setting only has an effect when encryption_key_version_new_files is 1 or above (the default for that setting). What follows is an abstract overview of how hash iterations may be used. When a text password that the user has entered is used for encryption a number of hashing rounds is applied to that password. The hashing uses both the user password and a random salt value combined. The salt is used so that the same password does not lead to the same key each time. It also means vast tables of precomputed keys can not be used for all transfers as the salt changes per transfer. The number of hashing rounds might be set for example so that deriving a key might take a second to perform. While that is not a huge delay for the user of the system, a potential attacker would have to spend some time to hash each guessed password. The attacker might have better or much newer hardware or use a GPU so that the time to has might be less than the second a user has spent. See https://en.wikipedia.org/wiki/PBKDF2#Key_derivation_process. To get an idea of how this setting will impact system performance see the admin/testing page on filesender.

The realistic thing to do is consider the predictive increase in computing power,
which Moore's law states to double every 18 months. That is a factor of 1.6
every year (to be precise, it is 2.0^(12/18)). Our task is to increase the effort for
bulk attacks by that factor, which means adding one bit to the iteration count
every 18 months, starting with an acceptable iteration count for the year 2010
(and setting it at 2009).

So, to protect until the year Y and with 2010-level security from N(2009)
iterations, the computation would be
N(Y) = N(2009) * 2.0^((Y-2009)*2/3)
which is an exponential series! A few outcomes when N(2009) = 1000 are:

N(2010) = 1587
N(2015) = 16000
N(2020) = 161270
N(2025) = 1625499
N(2030) = 16384000

The one logic here is that the length of the numbers consistently grows for these
5-year intervals. As can be seen, the algorithm leans towards being somewhat
painful in order to protect the intended use of running it only once.

Visiting admin/testing on your filesender install will allow you to see how long
these iteration counts take to perform on your local machine.

* __mandatory:__ no 
* __type:__ int
* __default:__ 150000
* __available:__ since version 2.9
* __comment:__



### encryption_encode_encrypted_chunks_in_base64_during_upload
* __description:__ This allows fallback to the older base64 PUT that was used in version 2.22. The encoding is quite costly and if there are no issues this parameter together with the fallback to using base64 on the PUT contents will be removed in a future version. 
* __mandatory:__ no 
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.23
* __comment:__ This is to allow fallbacks to older code. The default should be left unless you experience issues. If this fallback is not needed it will be removed in a future release and the __default__ will become the only choice in the code.




### automatic_resume_number_of_retries
* __description:__ Number of times to automatically resume an upload if a major error has happened. Set this to 0 to disable automatic resume.
* __mandatory:__ no 
* __type:__ int
* __default:__ 50
* __available:__ since version 2.1
* __comment:__ 


### automatic_resume_delay_to_resume
* __description:__ Delay in seconds to wait after a major failure before an automatic resume is performed.
* __mandatory:__ no 
* __type:__ int
* __default:__ 10
* __available:__ since version 2.1
* __comment:__ 



### transfer_options_not_available_to_export_to_client
* __description:__ Options that can be exposed to the client when available=false. For example, if you want to force the get_a_link option it will be default=true and available=false (available to be changed, not unavailable as a used option). You might like to leave this option unset so that it can change as filesender evolves to allow mandatory options to be exposed to the browser.
* __mandatory:__ no 
* __recommend_leaving_at_default:__ true
* __type:__ array of strings
* __default:__ see ConfigDefaults.php
* __available:__ since version 2.6
* __comment:__ 


### chunk_upload_roundtriptoken_check_enabled
* __description:__ Check that a random token handed out during transfer creation is always passed back exactly as expected from the client. This parameter was created to disable the check in case some edge case is discovered and a site wishes to turn off this security feature temporarily.
* __mandatory:__ no 
* __recommend_leaving_at_default:__ true
* __type:__ boolean
* __default:__ true
* __available:__ since version 2.16
* __comment:__ 


### chunk_upload_roundtriptoken_accept_empty_before
* __description:__ As of FileSender 2.16 all newly created transfers will have a
roundtriptoken created and stored on the server. The roundtriptoken is
only sent to client when a tranfer is being created. The aim is that
knowledge of the roundtriptoken means that a particular client is the
one that created the transfer. The roundtriptoken is a large random
value. The roundtriptoken is sent back by the clients during chunk
uploads to be able to verify that they made the transfer. Creating and
sending the roundtriptoken will happen regardless of the
chunk_upload_roundtriptoken_check_enabled setting.
If chunk_upload_roundtriptoken_check_enabled is set to true then the
transfer on the server must have a roundtriptoken recorded and the
roundtriptoken supplied by the client must match the one from the
database on the server in order for the chunk upload to be accepted.
Though without another option one might see a migration issue when a
client tries to load a failed transfer and resume it. This will happen
for example if a user revists the upload page and the dialog offers to
reload a failed transfer. This failed transfer state will have no
roundtriptoken on the client and as the transfer was created with
older server code there will be no roundtriptoken stored in the
database either.
To allow easier migration of existing transfers this configuration
setting can be used. If
chunk_upload_roundtriptoken_accept_empty_before is non zero then
transfers with an empty roundtriptoken which were created before the
value of chunk_upload_roundtriptoken_accept_empty_before will be
accepted. This allows transfers created before deployment of FileSender 2.16 to continue
as they would have. It may be tempting to just allow transfers that
have no roundtriptoken in the database to pass, but if you have set
chunk_upload_roundtriptoken_check_enabled to true you cerainly want
to enforce that all new transfers have a token in the database to ensure that this
test is active.
Setting this to the deployment time plus one week for example should
allow existing uploads to complete. The value for the current time can
be found using "date +%s" on a Linux machine for example. Though that
will not have any wiggle room added.  Note that if a transfer has a roundtriptoken
set then this setting will not change if the client must present the roundtriptoken again.
This is only for old, existing transfers which have no roundtriptoken set.
* __mandatory:__ no 
* __recommend_leaving_at_default:__ false
* __type:__ int
* __default:__ 0
* __available:__ since version 2.16
* __comment:__ 



### streamsaver_enabled
* __description:__ Allow the use of StreamSaver to perform streaming download of encrypted files on supported browsers.
* __mandatory:__ no 
* __recommend_leaving_at_default:__ true
* __type:__ boolean
* __default:__ true
* __available:__ since version 2.19
* __comment:__ 

### streamsaver_on_unknown_browser
* __description:__ If streamsaver_enabled and the browser does not match any known browser with explicit configuration (eg streamsaver_on_firefox) then this is the default if the site should try to use streamsaver on that unknown environment.
* __mandatory:__ no 
* __recommend_leaving_at_default:__ true
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.19
* __comment:__ 

### streamsaver_on_firefox
* __description:__ If streamsaver_enabled is true then this controls if streamsaver is used on Firefox.
* __mandatory:__ no 
* __recommend_leaving_at_default:__ false
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.19
* __comment:__ 


### streamsaver_on_chrome
* __description:__ If streamsaver_enabled is true then this controls if streamsaver is used on Google Chrome.
* __mandatory:__ no 
* __recommend_leaving_at_default:__ true
* __type:__ boolean
* __default:__ true
* __available:__ since version 2.19
* __comment:__ 

### streamsaver_on_edge
* __description:__ If streamsaver_enabled is true then this controls if streamsaver is used on Microsoft Edge.
* __mandatory:__ no 
* __recommend_leaving_at_default:__ true
* __type:__ boolean
* __default:__ true
* __available:__ since version 2.19
* __comment:__ 

### streamsaver_on_safari
* __description:__ If streamsaver_enabled is true then this controls if streamsaver is used on Safari.
* __mandatory:__ no 
* __recommend_leaving_at_default:__ true
* __type:__ boolean
* __default:__ true
* __available:__ since version 2.19
* __comment:__


### fileSystemWritableFileStream_enabled
* __description:__ Allow the use of the FileSystemWritableFileStream API to perform streaming download of encrypted files on supported browsers.
* __mandatory:__ no 
* __recommend_leaving_at_default:__ true
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.41
* __comment:__
    This feature is currently only available when you have streamsaver_enabled=true set. This will prefer to use the
    FileSystemWritableFileStream API when available to stream data to disk. As at mid 2023 Edge and Chrome support
    this feature, Firefox supports most but not all (so can not be used) and Safari does not support the feature.
    In the future this may be separated from the streamsaver_enabled option so it can be enabled independently.

### recipient_reminder_limit

* __description:__ The number of reminders that a user can send to a recipient
* __mandatory:__ no
* __type:__ int
* __default:__ 50
* __available:__ since before version 2.30
* __comment:__ Each time a user sends a reminder to a recipient they use up one of these reminders for that recipient.
    This option stops a user from flooding a recipient with an infinite supply of reminders.

    Note: Before version 2.30 this was limited by guest_reminder_limit
    instead of being limited by recipient_reminder_limit. Both
    defaulted to 50 so the default configuration will effectively
    remain the same as before 2.30 but now these settings can be
    changed independently.


### log_authenticated_user_download_by_ensure_user_as_recipient

* __description:__ Log the saml Identifiant for downloads performed by authenticated users
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since before version 2.39
* __comment:__ This option allows a user to see which authenticated users have
     downloaded their transfers. This option is most effective when
     "User must login to FileSender to download file" is enabled for a transfer.

     This is mainly useful when the transfer is created with "get a
     link" and that link is shared by the user with other users
     outside of the system. If another user logs into the FileSender
     server and downloads a file from a transfer then the
     authenticated downloader is logged against each file that they
     download. This logging is not done when the user has admin
     privileges.
     
     This option will ensure that there is an entry in the recipients
     table for the transfer for the saml Identifiant (normally email
     address) of an authenticated user who is downloading a file. This
     has privacy implications as the person who created a transfer may
     be able to see the email address of each authenticated user who
     has downloaded each file. One should be aware of and accept this
     arrangement before enabling this feature for a FileSender
     installation.

     Note that if a regular user visits a download link and they are
     allowed to download because "User must login to FileSender to
     download file" is not selected then the user can download without
     log in and thus the exact user is not known for the download log.
     



### transfer_automatic_reminder

* __description:__ The number of reminders that a user can send to a recipient
* __mandatory:__ no
* __type:__ int, array of int, or false
* __default:__ false
* __available:__ since before version 2.40
* __comment:__ This is used in the cron job to allow notifications to be sent to users
  who have not downloaded files and the expire time for those files is coming up. The integer
  is the number of days that remain before the transfer expires. Note that a user will only be notified
  if they have not already downloaded the file.
  
  In the below configurations the first will notify people who have not downloaded a transfer a week from
  it expiring. The second will result to two potential notifications, one 10 days out and one a week from
  expiring. Note that if the user receives the first notification and downloads the file they will not receive
  the notification a week out because they have already downloaded the file.
  
* __*Configuration example:*__
   $config['transfer_automatic_reminder'] = 7;
   $config['transfer_automatic_reminder'] = array(7,10);



### transfers_table_show_admin_full_path_to_each_file

* __description:__ In the transfers table show the local path for the data for each file
* __mandatory:__ no
* __type:__ bool
* __default:__ false
* __available:__ since before version 2.46
* __comment:__ A debugging option to allow an admin to see where the file content is stored for each
               file in every transfer. This allows direct inspection of the disk without having to
               work out the transfer id and uuid for a file in the case that an admin wishes to inspect 
               the disk. This can be useful when storage_filesystem_per_day_buckets is enabled as there
               will be subdirectories that are calculated from the timestamp in the uuid which may not
               be immediately obvious to a human.





---

## Graphs

---

### upload_graph_bulk_display

* __description:__ Enable or disable bulk upload speed graphs on the uploads page.
* __mandatory:__ no
* __type:__ boolean
* __default:__ true
* __available:__ since version 2.0
* __comment:__ Note: 

### upload_graph_bulk_min_file_size_to_consider

* __description:__ only consider files above this size in bulk transfer speed calculation.
* __mandatory:__ no
* __type:__ boolean
* __default:__ 1024 * 1024 * 1024
* __available:__ since version 2.0
* __comment:__ only useful when you enable upload_graph_bulk_display





---

## TeraSender (high speed upload module)

---

### terasender_enabled

* __description:__ if set to true, enables TeraSender high speed upload module.  This leverages client-side webworkers to parallelise uploads; each chunk is sent by a webworker allowing us to send many chunks in parallel.
* __mandatory:__ no
* __type:__ boolean
* __default:__ true
* __available:__ since version 1.6
* __1.x name:__ terasender
* __comment:__ the default value in version 1.6 was false

### terasender_advanced

* __description:__ if set to yes the advanced terasender settings (worker count, chunk size) become available for a user in the UI.  Use this to easily test which workercount vs. chunk size settings work best for a very specific very demanding user/use case.
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 1.6
* __1.x name:__ terasenderadvanced
* __comment:__

### terasender_worker_count

* __description:__ how many client-side workers FileSender fires up when starting a terasender upload.  Note that different browsers have different maximum webworker settings which also change over time.  As CPU power increases your users will typically be able to support higher number.
* __mandatory:__ no
* __type:__ int
* __default:__ 6
* __available:__ since version 1.6
* __1.x name:__ terasender_workerCount
* __comment:__ <span style="background-color:orange">we need to check maximum webworker counts for standard browsers and possibly increase the default number</span>

### terasender_worker_max_count

* __description:__ Max value that terasender_worker_count can ever have if user set.
* __mandatory:__ no
* __type:__ int
* __default:__ 30
* __available:__ since version 2.23
* __comment:__ 

### terasender_start_mode

* __description:__ progress sequentially or parallel through the file list.
* __mandatory:__ no
* __type:__ string, keyword
* __permissible values:__ "single" or "multiple".  When single all workers will work on one single file and move sequentially through the file list.  When set to multiple all workers will be spread over all files.  The difference is in user experience; in the latter case a user sees progress on all files at once.  In reality the total upload time should remain the same.  So question is do you want the status to light up light a christmas tree or not.
* __default:__ multiple
* __available:__ since version 2.0
* __1.x name:__
* __comment:__ when looking for a file to put a worker on in multiple mode we look at file which has compbination of least worker and least progress.  Try to put available worker on file that is the slowest.  In multiple-mode we try to make all files progress at about the same speed.


### terasender_worker_max_chunk_retries

* __description:__ number of times a terasender worker retries to upload a chunk
* __mandatory:__ no
* __type:__ int
* __default:__ 20
* __available:__ since version 2.0 beta5
* __comment:__ The terasender worker will perform this many retries to upload a chunk before considering that it is a failure. Having 5 or more here will greatly improve the chances of an upload completing without user interaction. Note that this is the number of times a single chunk upload attempt can be retried, not the number of times a worker might try to retry in total. So if the value is 10 and the first chunk takes 8 attempts that is fine, the next chunk given to the worker can itself take up to the 10 times to upload. So a value of 20 would need *each* chunk to be retried up to 20 times and finally one chunk to push over that 20 in order to fail.


<span style="background-color:orange">when set to "single" uploads don't work?  Bug?</span>

### stalling_detection

* __description:__ detect whether an upload stalls
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.0
* __comment:__ Has effect on the JavaScript-variables given to the client-side of Terasender.

---

## Download

---

### download_chunk_size

* __description:__ the maximum amount of data that will be read into <span style="background-color:orange">(server or client side?)</span> memory at once during multi-file downloads (not single file?)
* __mandatory:__ <span style="background-color:orange">?<span style="background-color:orange">
* __type:__ int
* __default:__ 5242880 (5MB)
* __available:__ since version 2.0
* __1.x name:__
* __comment:__

### mac_unzip_name

* __description:__ <span style="background-color:orange">per oktober 2014 the default Mac built-in unzip client is still 32 bits. This leads to problems if the zip file that's downloaded when downloading multiple-files-as-an-archive is larger than 2 GB: a user can click on the zip file but it won't expand into a folder. To prevent help desk calls we alert a user to this problem and give them a place where they can go for the solution. (need double check for Yosemite)</span>
* __mandatory:__ <span style="background-color:orange">? Should be?)</span>
* __type:__ string
* __default:__ The Unarchiver
* __available:__ since version 2.0
* __1.x name:__
* __comment:__

### mac_unzip_link

* __description:__ link in download form where user can download a 64 bit unzip utility for Mac OS-X
* __mandatory:__ <span style="background-color:orange">? </span>
* __type:__ string
* __default:__ https://theunarchiver.com/
* __available:__ since version 2.0
* __1.x name:__
* __comment:__

---

## Guest use

---


### guest_support_enabled

* __description:__ Allow users to create guests.
* __mandatory:__ no
* __type:__ boolean
* __default:__ true
* __available:__ since version 2.30
* __1.x name:__
* __comment:__ Setting this to false will disable the guest system and fail on attempts to create a guest if they are directly attempted.



### guest_options

* __description:__ <span style="background-color:orange">are transfer options for guest invitations inherited from transfer_options?</span>this parameter controls which options a user has available in the Guest form to control the behaviour of guest invitations.  Options show up in the right hand side block in the Guest form. Options appear in the order they are specified in the config file. See below for details.
* __mandatory:__ no
* __type:__ array
* __default:__
* __available:__ since version 2.0
* __1.x name:__
* __comment:__
* __*Standard parameters for all options:*__
	* __available__(boolean): if set to true then this option shown in the Guest form
	* __advanced__ (boolean): if set to true the option is hidden under an "Advanced options" click-out.  The user must click "Advanced" to make the option visible.
	* __default__ (boolean): if set to true then this option is ticked by default.  If set to true while __*available*__ is set to false the option is mandatory for all users, it can not be switched off by the user.
* __*Available options:*__
	* __email\_upload\_started:__ send the guest invitation owner an email when the guest upload is complete.
	* __email\_upload\_page_access:__ send the guest invitation owner an email when the guest accesses the upload page.
	* __valid\_only\_one_time:__ the guest invitation can be used for one transfer only.
	* __does\_not\_expire:__ the guest invitation can be used until it is explicitly expired by the owner.  Combine with can_only_send_to_me to create a permanent file upload link that can be put in an email signature.
	* __can\_only\_send\_to_me:__ the recipient for this guest invitation is fixed, the guest can not choose their own recipients.
	* __email_guest_created:__ send the guest an email when the guest voucher is created.
	* __email_guest_created_receipt:__ send the guest invitation owner an email when the guest voucher is created.
	* __email_guest_expired:__ send the guest an email when the guest voucher is expired.
	* __guest\_upload\_expire\_is\_guest\_expire:__ [optional] Try to set the default transfer expire time to the guest expire time if it is close enough.
        * __guest\_upload\_expire\_read\_only:__ [optional] Guest can not change the expire time for a transfer.

* __*Configuration example:*__

		$config['guest_options'] = array(
			'email_upload_started' => array(
				'available' => true,
				'advanced' => false,
				'default' => false
			),
			'email_upload_page_access' => array(
				'available' => true,
				'advanced' => true,
				'default' => false
			)
		);

### default_guest_days_valid

* __description:__ specifies the default expiry date value in the "Expiry date" date picker in the Guest form.  If a user doesn't do anything this becomes the expiry date for the guest invitation.  If this value is not configured, it is set to default_transfer_days_valid
* __mandatory:__ no
* __type:__ int
* __default:__ same as default_transfer_days_valid
* __available:__ since version 2.0
* __1.x name:__
* __comment:__

### min_guest_days_valid

* __description:__ specifies the minimum expiry date for a guest invitation.  This is the number of days from today (0). The default of 1 will result in an effective minimum expire time of tomorrow. You might like to make this something like 5 or 7 to ensure guest vouchers are not accidentally created with very short life spans.
* __mandatory:__ no
* __type:__ int
* __default:__ 1
* __available:__ since version 2.32
* __1.x name:__
* __comment:__

### max_guest_days_valid

* __description:__ specifies the maximum expiry date for a guest invitation.  A user can not choose a larger value than this.
* __mandatory:__ no
* __type:__ int
* __default:__ 20
* __available:__ since version 2.0
* __1.x name:__
* __comment:__


### max_guest_recipients

* __description:__ specifies how many recipients a guest can specify
* __mandatory:__ no
* __type:__ int
* __default:__ 50
* __available:__ since version 2.0
* __1.x name:__
* __comment:__

### guest_upload_page_hide_unchangable_options

* __description:__ when true checkboxes that the guest can not interact with are hidden
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.0
* __1.x name:__
* __comment:__


### user_can_only_view_guest_transfers_shared_with_them

* __description:__ determine if a user can see all of the uploads of their guests
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.8
* __comment:__ if set to true a user will only see uploads for their guests where the can_only_send_to_me was set
  when the guest was invited or when the guest uploads the file and explicitly includes the user in the recipients.
  This may be updated in the future if we wish to force a 'must also send to me' option when inviting some guests.

### guest_create_limit_per_day

* __description:__ The number of guests a user can create per day
* __mandatory:__ no
* __type:__ int
* __default:__ 0
* __available:__ since version 2.18
* __comment:__ This setting is disabled when set to 0, no rate limit will be enforced.
  If the user tries to create more than this number of guests in any 24 hour window of time
  the action will be denied and logged. Note that this is an inclusive value, for example, a setting of 2
  will allow creation of 2 guests but not 3.

### guest_reminder_limit

* __description:__ The number of reminders that a user can send to a guest
* __mandatory:__ no
* __type:__ int
* __default:__ 50
* __available:__ since before version 2.0
* __comment:__ Each time a user sends a reminder to a guest they use up one of these reminders for that guest.
    This option stops a user from flooding a guest with an infinite supply of reminders.


### guest_reminder_limit_per_day

* __description:__ The number of reminders to each guest that user can send per day
* __mandatory:__ no
* __type:__ int
* __default:__ 0
* __available:__ since version 2.18
* __comment:__ This setting is disabled when set to 0, no rate limit will be enforced.
  If the user tries to send a reminder to a specific guest more than this number of times a day then
  the action will be denied and logged. Note that this is an inclusive value, for example, a setting of 5
  will allow 5 reminders to be sent to a guest but not 6.


### allow_guest_expiry_date_extension

* __description:__ Setting this option will allow normal users to extend their guest vouchers. This can be useful for example when a user has sent a guest voucher and receives an out of office reply email. The user might like to return to the guest page and click "extend" to make the the guest voucher valid for a longer period of time to be valid after the guest has returned to the office.
* __mandatory:__ no
* __type:__ an array of integers containing possible extensions in days.
* __default:__ 0 (= not activated)
* __available:__ since version 2.34
* __1.x name:__
* __comment:__
* __Examples:__

        // Allows infinite extensions, the first is by 30 days then 90 days 
        $config['allow_guest_expiry_date_extension'] = array(30, 90, true); 


### allow_guest_expiry_date_extension_admin

* __description:__ allows an admin to extend the expiry date of a guest. This is only used if you are logged in as an admin on the system. If you are an admin this schedule will overwrite the allow_guest_expiry_date_extension for you. 
* __mandatory:__
* __type:__ an array of integers containing possible extensions in days.
* __default:__ array(31, true)
* __available:__ since version 2.23
* __Examples:__

        // Allows infinite extensions, the first is by 30 days then 90 days 
	$config['allow_guest_expiry_date_extension_admin'] = array(30, 90, true); 


### guest_limit_per_user

* __description:__ The maximum number of active guests a user can have. Once a user has this many active guests they can not make a new guest until they delete an active guest.
* __mandatory:__ no
* __type:__ int
* __default:__ 50
* __available:__ since before version 2.0


### guests_expired_lifetime

* __description:__ For an expired guest, this is the number of days to retain information about the guest in the database before deleting it.
* __mandatory:__ no
* __type:__ int
* __default:__ 0
* __available:__ since before version 2.30


### guest_upload_page_hide_unchangable_options

* __description:__ When a guest is on the upload page any options that can not be changed will be hidden from view. This can reduce UI clutter.
* __mandatory:__ no
* __type:__ bool
* __default:__ false
* __available:__ since before version 2.30



---

## Authentication

---

### auth_sp_type

* __description:__ which authentication library to use.  saml=SimpleSAMLphp, shibboleth=shibboleth, fake uses a local file.  Do not use the fakesp in production!
* __mandatory:__ no
* __type:__ string, keyword
* __permissible values:__ "saml", "shibboleth", "fake"
* __default:__ saml
* __cookies:__ saml uses them by default
* __available:__ since version 2.0
* __1.x name:__
* __comment:__ <span style="background-color:orange">to use type "fake" you need ...</span>


### auth_sp_force_session_start_first

* __description:__ Call php session_start to setup the session cookie before attempting auth authentication with the auth_sp_type.
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __cookies:__ depending on php env this might set PHPSESSID cookie
* __available:__ since version 2.26
* __comment:__ Some of the auth_sp methods may use _SESSION or perform other actions that might alter how session_start() will work. If that is the case you can set this configuration to true and session_start() will be called before authentication is performed.


### auth_sp_set_idp_as_user_organization

* __description:__ saml_sp_idp (simplesaml), shib: (shib_identity_provider environment variable) takes sp identifier from sp if provided and save it in user preferences as organisation property.
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.0
* __1.x name:__
* __comment:__ <span style="background-color:orange">is this still in use?  There is no code associated with it as far as I can tell</span>

## Authentication: SimpleSAMLphp

---

### auth_sp_saml_authentication_source

* __description:__ which authentication source service provider to use. In SimpleSAMLphp you configure these in the configuration file <simplesamlphp>/config/authsources.php.
* __mandatory:__ no
* __type:__ string
* __default:__ default-sp
* __available:__ since version 1.0
* __1.x name:__ site_authenticationSource
* __comment:__

### auth_sp_saml_simplesamlphp_url

* __description:__ which URL to find SimpleSAMLphp.
* __mandatory:__ yes, if auth_sp_type is set to 'saml'
* __type:__ string
* __default:__ -
* __available:__ since version 1.0
* __1.x name:__ site_simplesamlurl
* __comment:__ You will usually have something like `https://filesender.example.org/simplesaml` here where 'simplesaml' is an alias defined as `Alias /simplesaml /usr/local/simplesaml/www` in your web server config.

### auth_sp_saml_simplesamlphp_location

* __description:__ file system path to SimpleSAMLphp location
* __mandatory:__ yes, if auth_sp_type is set to 'saml'
* __type:__ string
* __default:__ -
* __available:__ since version 1.0
* __1.x name:__ site_simplesamllocation
* __comment:__

### auth_sp_saml_uid_attribute

* __description:__ attribute for user's unique user identifier to get from authentication service provider.  Usually you would use either *pairwise-id* or *subject-id* (watch the spelling!). 
* __mandatory:__ no explicit configuration is needed when the default is used.  However, this value MUST be received from the Identity Provider, otherwise a user can not log on.
* __type:__ string
* __default:__ pairwise-id
* __available:__ since version 1.0
* __1.x name:__ saml_uid_attribute
* __comment:__ Note that the default has changed from the deprecated eduPersonTargetedId to pairwise-id in version 2.48.

### auth_sp_saml_entitlement_attribute

* __description:__ Name of a multivalued attribute that contains the entitlements of a user. Usually eduPersonEntitlement, or isMemberOf.
* __mandatory:__ required if auth_sp_saml_admin_entitlement is set
* __type:__ string
* __default:__
* __available:__ since version 2.7
* __1.x name:__
* __comment:__

### auth_sp_saml_admin_entitlement

* __description:__ The value to be searched for in auth_sp_saml_entitlement_attribute. If found, this yields admin privileges.
* __mandatory:__ required if auth_sp_saml_entitlement_attribute is set
* __type:__ string
* __default:__
* __available:__ since version 2.7
* __1.x name:__
* __comment:__

### auth_sp_saml_email_attribute

* __description:__ attribute for user's mail address to get from authentication service provider
* __mandatory:__ no explicit configuration is needed when the default is used.  However, this value MUST be received from the Identity Provider, otherwise a user can not log on.
* __type:__ string
* __default:__ mail
* __available:__ since version 1.0
* __1.x name:__ saml_email_attribute
* __comment:__

### auth_sp_saml_name_attribute

* __description:__ attribute for user's name to get from authentication service provider
* __mandatory:__ no
* __type:__ string
* __default:__ cn
* __available:__ since version 1.0
* __1.x name:__ saml_name_attribute
* __comment:__


### using_local_saml_dbauth

* __description:__ enable web interface elements for managing passwords in the filesender database. See scripts/simplesamlphp/passwordverify in the release for details of how to setup your SimpleSAMLphp to authenticate against this information.
* __mandatory:__ no
* __type:__ boolean
* __default:__ 0
* __available:__ since version 2.16
* __comment:__ 





## Authentication: Shibboleth

---

### auth_sp_shibboleth_uid_attribute

* __description:__ attribute for user's unique user identifier to get from authentication service provider.  Usually you would use pairwise-id.
* __mandatory:__ no explicit configuration is needed when the default is used.  However, this value MUST be received from the Identity Provider, otherwise a user can not log on.
* __type:__ string
* __default:__
* __available:__ since version 2.0
* __1.x name:__
* __comment:__

### auth_sp_shibboleth_email_attribute

* __description:__ attribute for user's mail address to get from authentication service provider
* __mandatory:__ no explicit configuration is needed when the default is used.  However, this value MUST be received from the Identity Provider, otherwise a user can not log on.
* __type:__ string
* __default:__ mail
* __available:__ since version 2.0
* __1.x name:__
* __comment:__

### auth_sp_shibboleth_name_attribute

* __description:__ attribute for user's name to get from authentication service provider
* __mandatory:__ no
* __type:__ string
* __default:__ cn
* __available:__ since version 2.0
* __1.x name:__
* __comment:__

### auth_sp_shibboleth_login_url

* __description:__ where to find the Shibboleth login URL
* __mandatory:__ yes when using Shibboleth as authentication library
* __type:__ string
* __default:__ -
* __available:__ since version 2.0
* __1.x name:__
* __comment:__
* __example:__ $prot.$_SERVER['SERVER_NAME'].'/Shibboleth.sso/Login?target={target}';

### auth_sp_shibboleth_logout_url

* __description:__ where to find the Shibboleth logout URL
* __mandatory:__ yes when using Shibboleth as authentication library
* __type:__ string
* __default:__ -
* __available:__ since version 2.0
* __1.x name:__
* __comment:__
* __example:__ $prot.$_SERVER['SERVER_NAME'].'/Shibboleth.sso/Logout?return={target}';

## Authentication: SP_fake

### auth_sp_fake_authenticated

* __description:__
* __mandatory:__
* __type:__ boolean
* __default:__
* __available:__
* __1.x name:__
* __comment:__

### auth_sp_fake_uid

* __description:__ UID you want to have
* __mandatory:__
* __type:__ string
* __default:__
* __available:__
* __1.x name:__
* __comment:__

### auth_sp_fake_email

* __description:__
* __mandatory:__
* __type:__
* __default:__
* __available:__
* __1.x name:__
* __comment:__

### auth_sp_fake_name

* __description:__
* __mandatory:__
* __type:__
* __default:__
* __available:__
* __1.x name:__
* __comment:__

---

## Maintenance and logging

---

### failed_transfer_cleanup_days

* __description:__ number of days after which chunks belonging to failed or interrupted uploads will be deleted from disk on the server. If some transfer was created say 7 days ago and still not completed, the associated data is removed after 7 days.
* __mandatory:__ no
* __type:__ int (days)
* __default:__ 7
* __available:__ since version 1.5
* __1.x name:__ cron_cleanuptempdays
* __comment:__

### log_facilities

* __description:__ defines where FileSender logging is sent.  You can sent logging to a file, to syslog or to the default PHP log facility (as configured through your webserver's PHP module).  The directive takes an array of one or more logging targets. Logging can be sent to multiple targets simultaneously.  Each logging target is a list containing the name of the logging target and a number of attributes which vary per log target.  See below for the exact definiation of each log target.
* __mandatory:__ no
* __type:__ array of log targets.  Each target has a type and a number of parameters
* __default:__  array('type' => 'file', 'path' => FILESENDER_BASE.'/log/', 'rotate' => 'hourly'))
* __available:__ since version 2.0
* __1.x name:__
* __comment:__

<span style="background-color:orange">if you define your own log_facilities, you will overwrite the default setting.  Make sure to include all log targets you wish to log to.</span>

* __*General format of log target:*__ array(('type' => string, <attribute1 => <value>, <attribute2> => <value>
* __*Standard parameters for all options:*__
	* __'level'__ (optional): restricts loglevel of current facility.  Permissible values: debug, warning, info, error
	* __'output'__ (optional): sets the output mode of log messages.  Permissible values: text, json 
	* __'process'__ (optional): allows you to separate logs from different parts of FileSender into separate logfiles, for example the REST logfile gets huge.  Permissible values: CLI, GUI, REST, WEB, CRON, FEEDBACK, MISC, INSTALL, UPGRADE.  Comma-separated list.
* __*Available targets:*__
	* __'type' => 'file'__ logs to a file.  You must specify a path.  You can optionally specify log file rotation with 'rotate' => '<value>', where value can be hourly, daily, weekly, monthly, yearly.
	* __'type' => 'syslog'__ logs to syslog.
	* __'type' => 'errror_log'__ logs to the default PHP log facility as defined in your webserver's PHP module.

The general format is an array of arrays. If you only have a single
place you want to log to you only need to remember to have two array
words in there or the system should complain.

Being an array of array you can have multiple places take log information
and all of them process it.

<pre><code>
Array( array (
  type => String (errorlog,syslog,file,callable)
  path => String
  rotate => String ) )
</code></pre>

The default type is 'file'. If you define your own log_facilities it
will override the default configuration so if you want to include the
default you have to explicitly add it to your new setting.

The only top level mandatory parameter is 'type'. If you optionally set output
to json then logs will be structured in JSON format.

<pre><code>
array (
  'type' => 'file',     // possible = file, syslog, error_log, callable
  'output' => 'text',   // possible = text, json
  'path' => '&lt;something>/logs/',
  'rotate' => hourly,   // possible = hourly, daily, weekly, monthly, yearly
  'process' => REST,    // possible = MISC, WEB, CLI, GUI, REST, CRON, FEEDBACK, INSTALL, UPGRADE
</code></pre>

The type setting lets you choose where the log will be sent. The error
log setting error_log will log using default php facility which puts
logs in apache error logs. The callable allows you to set a php
function to call to log the data.

The process setting allows you to ask to only get logs from specific
parts of FileSender. This way you can separate your logs between
different components.

Note that REST process logs can be very large so you might like to
rotate every hour if you are keeping them.

Sometimes a setting does nothing for a type. For example the rotate
setting will have no meaning if you have a type=callable.

For a type of syslog you can also supply the indent and facility.
Facility sets the syslog facility used.  Standard PHP syslog function parameters

When using a type of callable (which is an advanced application): "I
give you something you can call to log". There is one mandatory
parameter "callback" which must be a php function. That will be called
every time you want to log something. Level and process can be set as
well. When it's called it will get the message to log and the current
process. 1st argument will be message, 2nd argument process type. Can
name them A and B. This can be useful if you're searching for a particular
error or for example use remote log facility. Search for particular
error: write specific function to catch specific errors and drop an
email when it happens.

<span style="background-color:orange">* __*Examples:*__</span>

This will log everything to a file in log that is rotated every hour
<pre><code>
$config['log_facilities'] =
        array(array(
            'type' => 'file',
            'path' => FILESENDER_BASE.'/log/',
            'rotate' => 'hourly'
        ));
</code></pre>

This will do no logging:
<pre><code>
$config['log_facilities'] =
        array(array(
            'type' => 'callable',
            'callback' => function() {},
        ));
</code></pre>



### maintenance

* __description:__ when true, switches the FileSender instance in maintenance mode.  This allows to interrupt the service for a database upgrade or webserver restart without breaking ongoing uploads.
	* all pages are replaced with the maintenance page
	* webservice returns specific exception to all requests
	* clients display a popup explaining what happens
	* clients pause uploads and put all requests they were about to make in a stack
	* clients starts to query the server on a regular basis to see if maintenance ended (server responding with no exception status)
	* when server exits maintenance mode clients restart uploading and run stacked requests and remove maintenance popup
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.0
* __1.x name:__
* __comment:__

### statlog_lifetime

* __description:__ The statlog is kept in the database and contains everything needed to produce usage statistics.  This directive defines maximum lifetime of statslog entries (in days) after which they get deleted.  <span style="background-color:orange">point to more text detailing what is actuallly logged in the statlog!</span>
* __mandatory:__ no
* __type:__ int (days)
* __default:__ 0
* __available:__ since version 2.0
* __1.x name:__
* __comment:__ The statlog is always enabled.  If you don't want anything logged, set this lifetime to 0.  Use this setting to control the privacy footprint of your FileSender service.

### auth_sp_additional_attributes

* __description:__ Allows to define additional user attributes that will be asked for, such as organisation, that can then be propagated to the statistic log table in the database for use in creating statistics.  This configuration parameter defines the additional attributes to get. definition of additional attributes to get, array of either attributes names or final name to raw attribute name pair or final name to callable getter pair
* __mandatory:__ no
* __type:__ array of attribute names or name to raw attribute pair or name to callable getter pair
* __default:__ - (which means do not get any additional attributes)
* __available:__ since version 2.0
* __1.x name:__
* __comment:__
* __example:__ <span style="background-color:orange">need an example here!</span>

### auth_sp_save_user_additional_attributes

* __description:__ if set to true, the additional user attributes are saved in the userpreferences table.
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.0
* __1.x name:__
* __comment:__ <span style="background-color:orange">what was the point of this again?</span>
* __example:__ ...

### statlog_log_user_additional_attributes

* __description:__ if set to yes, the additional attributes defined in auth_sp_additional_attributes are logged in the statlog table.  This allows you to do e.g. per organisation statistics or show the use for students, employees, researchers.
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.0
* __1.x name:__
* __comment:__
* __example:__ ...

### auth_sp_fake_additional_attributes_values

* __description:__ array of name to value pairs for fake sp authentication (testing only)
* __mandatory:__ no
* __type:__ array of name-value pairs
* __default:__ -
* __available:__ since version 2.0
* __1.x name:__
* __comment:__
* __example:__ <span style="background-color:orange">needs example!</span>

### auditlog_lifetime

* __description:__ The auditlog is kept in the database and contains all events for a transfer.  This information can be used to tell the user what happened to their transfer when.  This directive specifies the maximum lifetime of auditlog entries (in days).  If set to 0 we remove data when the transfer is closed, after sending reports (if user indicated they wanted).  As long as transfer is live you have this data, as soon as transfer expires the log disappears.  If you set it to "false" we don't log anything and a user can't even see the logs when a transfer is live.
* __mandatory:__ no
* __type:__ boolean/int (days).  Set to false to disable.
* __default:__ 31
* __available:__ since version 2.0
* __1.x name:__
* __comment:__ Use this setting to control the privacy footprint of your FileSender service.

### ratelimithistory_lifetime

* __description:__ The ratelimithistory entries are kept in the database for this long. Should be at least a day, default is a month.
* __mandatory:__ no
* __type:__ boolean/int (days).  Set to false to disable.
* __default:__ 31
* __available:__ since version 2.42
* __1.x name:__
* __comment:__ Use this setting to control the privacy footprint of your FileSender service.

### report_format

* __description:__ A user can ask for an audit report specifying what happened to a transfer when.  This can be done when initiating a transfer by ticking the checkbox or explicitly through MyTransfers (view audit log).  This setting specifies what type of report will be generated.
* __mandatory:__ no
* __type:__ keyword (string).
* __permissible values:__ inline, PDF.
* __default:__ inline
* __available:__ since version 2.0
* __1.x name:__
* __comment:__ The same information is sent regardless of format.  Inline sends an email in plain text and HTML, with all information inline.  If PDF is chosen, the report is sent as PDF attachment.  Building a PDF is somewhat heavier on the server but won't matter unless you would have a heavily used server.  The library used is "dom pdf", included in the code.


### exception_additional_logging_regex

* __description:__ Exception names that additional logging is desired for
* __mandatory:__ no
* __type:__ string regex
* __default:__ 
* __available:__ since version 2.0
* __comment:__ Sometimes a site might want to capture down extra logging for some exception types. This configuration is a regular expression to match the name of an exception against to see if you want this extra log info. This allows extra log info to be turned on and off fairly easily without having to edit code and possibly break something. Note that only some exceptions can give extra info.


### clientlogs_stashsize

* __description:__ Client log backfeed stash size
* __mandatory:__ no
* __type:__ positive integer
* __default:__ 100
* __available:__ since version 2.0
* __comment:__ Number of last client console entries that are to be back-fed to the server in case there is a client error.


### clientlogs_lifetime

* __description:__ Client log backfeed lifetime
* __mandatory:__ no
* __type:__ positive integer
* __default:__ 10
* __available:__ since version 2.0
* __comment:__ Number of days after which collected client logs are automatically deleted.

### client_ip_key

* __description:__ PHP key to use as client identifier
* __mandatory:__ no
* __type:__ string
* __default__: REMOTE_ADDR
* __available:__ v2.2
* __comment:__ Client identifier. Usually the default is fine, however when you have reverse proxy setups, you may need to change this to HTTP_CLIENT_IP, HTTP_X_REAL_IP, HTTP_X_FORWARDED_FOR, depending on your setup.


### logs_limit_messages_from_same_ip_address

* __description:__ An option to limit how frequently transfers from the same IP address are logged
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.30
* __comment:__ In version 2.30 the default action of not logging frequent items from the same IP address was turned off.
        This option allows that throttle limit to be turned back on again if not have it causes major problems. If this
        setting with the default of false works ok for people then the option may be removed and the default of not
        limiting logs will be the only option. So in short, you may not ever need to know about or set this option. It
        is here as a fallback if there are issues with it being turned off.


---

## Webservices API

---

### auth_remote_application_enabled

* __description:__ enable or disable remote application authentication.  Needed to let remote applications (API applications) authenticate
* __mandatory:__ no
* __type:__ boolean
* __default:__ false (not explicit)
* __available:__ since version 2.0
* __1.x name:__
* __comment:__ <span style="background-color:orange">needs to be elaborated more.  Consequences of setting to true</span>

### auth_remote_signature_algorithm

* __description:__ <span style="background-color:orange">which remote signature algorithm to use.  Which other permissible values?</span>
* __mandatory:__ no
* __type:__ string, permissible values: "sha1".
* __default:__ "sha1"
* __available:__ since version 2.0

### auth_remote_applications

* __description:__  list of remote applications.  This is an array where each entry includes an authentication secret, whether or not the application has admin rights and what methods the application is allowed to use:
* __mandatory:__ no
* __type:__ array
* __default:__
* __available:__ since version 2.0
* __1.x name:__
* __comment:__ 
Example:

`$config['auth_remote_applications'] = array (
    'appname' => array( 'secret' => 'appsecret', 'isAdmin' => true, 'acl' => array( 'get' => TRUE, 'post' => TRUE, 'info' => TRUE, 'transfer' => TRUE))
);`
The array above contains the remote_application name and all the information for that is in an array under the key. 
In this example, the application `appname` with secret `secret` has admin rights and can access the endpoint `/info` and `/transfer` by get and post. If you want it to access another endpoint it's necessary to put it in `acl` array. Without it the `info` ACL the test example would fail with permission denied.

### auth_remote_user_enabled

* __description:__ Enable API authentication of remote users. Users can authenticate if they have generated an API key in their user profile.
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.0
* __1.x name:__
* __comment:__

### auth_remote_user_autogenerate_secret

* __description:__ Automatically generate the user API key upon login, so they dont have to do it themselves
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.0
* __1.x name:__
* __comment:__

### disclosed

* __description:__ the webservice has an endpoint called "info" which discloses information about the FileSender instance.  By default it gives the URL of the FileSender instance.  This parameter allows you to add more info from the configuration file.  E.g. when using a remote client this client needs the chunk size.
* __mandatory:__ no
* __type:__ boolean/array of strings
* __default:__ - (disclose nothing)
* __available:__ since version 2
* __1.x name:__
* __comment:__ the parameter needs an array of strings.  The strings are configuration parameters you want to appear in the "info" webservice endpoint.  You can also give it static strings that have a specific meaning for you, like "version 2.0".
* __example:__ <span style="background-color:orange">$config['disclose'] = array( 'version' );</span>

### rest_allow_jsonp

* __description:__ Define additional REST-API end points JSONP can be called upon. JSONP is typically used when using FileSender in an iframe. We limit which API end points you can reach in such a scenario but give you the option of enlarging that set of API end points in case you need this.
* __mandatory:__ no
* __type:__ boolean/array of strings
* __default:__ Authorised by default are these api end points: /info, /lang, /file/[0-9]+/whole and /user/@me/remote_auth_config (if remote user authentication is enabled).
* __available:__ since version 2
* __1.x name:__
* __comment:__
* __example:__ Autorized by default are :

/info : public infos about the instance (name, login url ...)
/lang : UI translations getter
/file/[0-9]+/whole : legacy upload endpoint
/user/@me/remote_auth_config : enabled only if remote user authentication is enabled

Additional allowed endpoints can be added through the "rest_allow_jsonp"
configuration parameter (array of regexp to match the resource path
under rest.php), example :

$config['rest_allow_jsonp'] = array(
'/transfer/[0-9]+/auditlog'
);

---

## Aggregate statistics

### aggregate_statlog_lifetime

* __description:__ True to enable. This is left as a lifetime to allow extension for deleting really old aggregate event data from the database if desired.
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.5
* __1.x name:__
* __comment:__

### aggregate_statlog_send_report_days

* __description:__ The number of days between sends for a aggregate statistics report. Set this to 0 (the default) to disable.
* __mandatory:__ no
* __type:__ int
* __default:__ 0
* __available:__ since version 2.5
* __1.x name:__
* __comment:__

### aggregate_statlog_send_report_email_address

* __description:__ email address to send aggregate statistics report to.
* __mandatory:__ no
* __type:__ string
* __default:__ ''
* __available:__ since version 2.5
* __1.x name:__
* __comment:__


---

## Other

---

### host_quota

* __description:__ use this when your FileSender instance needs to share its storage with other applications.  If set to a positive value it defines the total amount of storage your FileSender instance can use for storing files.  New transfers that require more space than is available are rejected with an error message in the Web-UI.  Set to 0 to disable.
* __mandatory:__ no
* __type:__ int (in bytes)
* __default:__ 0
* __available:__ since version 2.0
* __1.x name:__
* __comment:__

### config_overrides

* experimental feature in 2.0, not tested
* __description:__ <span style="background-color:orange">In version 2.0 you can create virtual FileSender instances (see the administrator guide.  Todo: write how to do this in the admin guide!)</span>.  With the config_overrides directive you specify the list of parameters an admin for a virtual FileSender instance you can override from admin interface.  When you set this parameter a "Config" tab becomes visible in the Admin tab in your FileSender UI. If you have one instance you can use this to separate roles between system admin and filesender admin.  You can also use this to automate FileSender virtual instance deployment.
* __mandatory:__ no
* __type:__ array of key-value pairs
* __default:__ 0, null, empty string: you won't get the config tab in the admin interface.  Any previously done override will be ignored.  They're not lost but no longer applied.
* __available:__ since version 2.0
* __1.x name:__
* __comment:__ example:
	* $config['config_overrides'] = array( 'site_name_in_header' => 'bool', 'site_name' =&gt; array('type' =&gt; 'string', 'validator' =&gt; 'is_string'), 'terasender_start_mode' =&gt; array('single', 'multiple'), );

	In this example the "site_name_in_header" is a checkbox in the UI.  For the override "site_name", type string: displays a text field, and runs validator "is_string".  You can use existing validators or any other function. The override "terasender_start_mode" displays a dropdown in which you can choose from different predefined values.

Changes are saved in config_overrides.json in the config directory.  The config.php file is NOT modified.  This keeps overrides separated from the site config.  is_string, is_numeric (standard php validators) or a function of your own which returns a boolean indicating if the value is good or not.

	
### auth_config_regex_files
* __description:__ <span style="background-color:orange">In version 2.0 you can override settings based authenticated client attritbutes using regex</span>. With the auth_config_regex_files directive you specify an array of attributes + regex and resulting filename to load if the regex matches for the attribute value.
* __mandatory:__ no
* __type:__ array of key-value pairs
* __default:__ 0, null, empty string: no overrides loaded.
* __available:__ since version 2.0
* __1.x name:__
* __comment:__ example:
	<pre><code>
	$config['auth_config_regex_files'] = [
		'uid' => [
			'@mydomain.com$' => 'mydomainfile',
			'@myotherdomain.com$|@yetanotherdomain.com$' => 'myotherdomainfile',
		];
	</code></pre>

	In this examples, if the uid ends with "@mydomain.com", the config file config-mydomainfile.php in the config subdir will be loaded.
	If the uid ends with "@myotherdomain.com" or "@yetanotherdomain.com", the config file config-myotherdomainfile.php in the config subdir will be loaded.
	
###

---

## Data Protection

---

### data_protection_user_frequent_email_address_disabled

* __description:__ if set to true then frequent email addresses are not cached for a user. These are used for example when sending files to email addresses or inviting a guest to the system. Note that the user may delete the frequent email addresses on the my profile page at any time, but with this option set to true such email addresses will not be cached in the database at all.
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.22
* __1.x name:__
* __comment:__


### data_protection_user_transfer_preferences_disabled

* __description:__ if set to true then the options a user selects when creating an upload are not stored in the database to set the same options for the next upload.
* __mandatory:__ no
* __type:__ boolean
* __default:__ false
* __available:__ since version 2.23
* __1.x name:__
* __comment:__



###

* __description:__
* __mandatory:__
* __type:__
* __default:__
* __available:__
* __1.x name:__
* __comment:__

---

###

* __description:__
* __mandatory:__
* __type:__
* __default:__
* __available:__
* __1.x name:__
* __comment:__

---

### testing_terasender_worker_uploadRequestChange_function_name

* __description:__ the name of a javascript method to call to mutilate the state for testing.
* __mandatory:__ no
* __type:__ string
* __default:__ 
* __available:__ since version 2.0 beta5
* __comment:__ Putting these in the config allows testing to be optionally turned on for select cases without the risk
     of leaving those testing code paths turned on during a git commit. Usable values for this are methods that start with
     testing_uploadRequestChange_ in the terasender worker object. These will selectively enable failure states on the client
     to test that it recovers from those if it is intended to do so.


<span style="background-color:orange">

# Available in 1.x, not in 2.0

cron_shred: consolidated by having a parameter to specify which delete command to use.
debug: use log_facilities to set a log level.
max_email_recipients: replaced with max_transfer_recipients and max_guest_recipeints

terasender_chunksize: chunksize is now consolidated in 1 parameter for all uploads?
terasender_jobsPerWorker: didn't have any practical meaning (doublecheck with Etienne)

webWorkersLimit: renamed to terasender_worker_count.  before you could launch several workers and each worker would request jobs.  There were # jobs per worker.  Testing showed having more than 1 job per worker gained nothing.  When you have browser process (tab in chrome) and doing async stuff (launch ajax request) get time to do other things.  This was not way workers were thought to behave.  Worker is not efficient when doing async stuff.  Several jobs per worker = async.  Theory: several jobs per worker can mean that when one job sends blob, other job can fetch data.  No significant gain observed.  Code was more complex so simplified.

crlf: now have a constant for that.  This parameter was important when windows was not respecting line delimiters in emails.  Had to make this configurable in the past when some old Windows clients (Outlook) used different newline format.  Really long time since this was a problem.

voucherRegEx: now hardcoded in utilities.  app was generating unique Ids with own algorithm that you can't change from the config.  Why does the checking regexp be configurable.  Changing it you can change the way the unique id looks which is a Bad Idea.  You could only really simplify it (make it less strict) thus reducing security.

openSSLKeyLength: generated in utility.  Method "generate_uid".  don't realy on openssl to generate unique IDs.   OpenSSL was used to be sure we had something unique.  Added dependency on OpenSSL.  Needs to be unique, non-guessable and properly random.  Using random_uid_generation (6 calls to mt_rand , build X-string, put dashes.  Solved by when generating unique ID.  Wwas used to generate random unique ids.  Adding dependency on openssl.  Was not that much more secure than generating unique IDs.  Unique IDs were generated before without collision checking.  Now we check for that until we get a real unique one.  Removing it removed a dependency.  Note: need to double-check how properly random the resulting UIDs are.

emailRegEx: now using PHP built-in facility for checking email address validity which these days works well.  Basic function is filter_var. Give it a variable and a filter to use.  Using filter FILTER_VALIDATE_EMAIL.

# Changed defaults from 1.x to 2.0

email_newline is now "\r\n", before \n
terasender_enabled is now "true", before false

# Relevant for security audits

library included, dom pdf.
