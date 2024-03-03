---
title: RESTful API
---

# Endpoints

All data sent to the REST server (except if mentionned) must be valid JSON with a `Content-Type: application/json`.

All data returned by the REST server (except if mentionned) will be JSON with a `Content-Type: application/json`, except for `JSONP GET` requests, in which case a `Content-Type: text/javascript` is returned.

## Transfer

### GET /transfer

List of user available transfers (same as `GET /transfer/@me`).

Accessible to : [users](#user).

Returns an `array of Transfer`, see [Transfer](#transfer).

### GET /transfer/@all

List of all available transfers (admin only).

Accessible to : [admin](#admin).

Returns an `array of Transfer`, see [Transfer](#transfer).

### GET /transfer/{id}

Get info about a specific [Transfer](#transfer).

Accessible to : [owner](#owner).

Parameters :

* `id` (`integer`) transfer unique identifier

Returns an `array of Transfer`, see [Transfer](#transfer).

Request example :

	GET /transfer/42

Response example :

	{
		"id": 42,
		"user_id": "145879@idp.tld",
		"user_email": "foo@bar.tld",
		"subject": "Here is the promished files",
		"message": null,
		"created": {
			"raw": 1421328866,
			"formatted": "15/01/2015"
		},
		"expires": {
			"raw": 1422365666,
			"formatted": "27/01/2015"
		},
		"options": ["email_download_complete", "email_report_on_closing"],
		"files": [
			{
				"id": 25,
				"transfer_id": 42,
				"uid": "7ecab9c0-9abf-eee9-9ab4-00000cc39816",
				"name": "report.doc",
				"size": "102598",
				"sha1": null
			},
			{
				"id": 31,
				"transfer_id": 42,
				"uid": "179558c7-c5be-5428-0b2e-00007ce9c792",
				"name": "funny.ppt",
				"size": "23589654",
				"sha1": null
			}
		],
		"recipients": [
			{
				"id": 59,
				"transfer_id": 42,
				"token": "0c27af59-72d1-0349-aa59-00000a8076d9",
				"email": "globi@bar.tld",
				"created": {
					"raw": 1421328866,
					"formatted": "15/01/2015"
				}
				"last_activity": null,
				"options": null,
				"download_url": "https://filesender.org/?s=download&token=0c27af59-72d1-0349-aa59-00000a8076d9",
				"errors": []
			}
		]
	}

### GET /transfer/{id}/options

Get the options of a specific [Transfer](#transfer).

Accessible to : [owner](#owner).

Parameters :

* `id` (`integer`) transfer unique identifier

Returns an `array of string`, see [Transfer](#transfer).

### GET /transfer/{id}/auditlog

Get the [Audit logs](#audit-log) of a specific [Transfer](#transfer).

Accessible to : [owner](#owner).

Parameters :

* `id` (`integer`) transfer unique identifier

Returns an `array of Audit log`, see [Audit log](#audit-log).

Returned array will be empty if the audit logging is turned off.

### GET /transfer/{id}/auditlog/mail

Sends the [Audit logs](#audit-log) of a specific [Transfer](#transfer) to the current user by email.

Accessible to : [owner](#owner).

Parameters :

* `id` (`integer`) transfer unique identifier

Returns `true`.

### POST /transfer

Create a new [Transfer](#transfer) from request (including files and recipients).

Accessible to : [users](#user).

Request body fields :

* `files` : (`array of object`) files to be uploaded
	* `name` : (`string`) the file name
	* `size` : (`integer`) the file size in bytes
	* `mime_type` : (`string`) the file MIME type (optionnal), will be used to set the `Content-Type` header for downloaders so that their browsers handle the file better
	* `cid` : (`string`) an optionnal client side identifier which will be returned in the response as a property of the corresponding created [File](#file), it may help the client to match back without having to use name and size
* `recipients` : (`array of string`) recipients email addresses
* `options` : (`array of string`) options (values defined in `classes/constants/TransferOptions.class.php`), under [Guest](#guest) authentication it will be ignored and the [Guest](#guest) `transfer_options` will be used
* `expires` : (`integer`) wanted expiry date as a UNIX timestamp, MUST respect `max_transfer_days_valid` configuration parameter
* `from` : (`string`) choosen sender email address, under [Service Provider](#service-provider) authentication it MUST be one of the authenticated user email addresses, under [Guest](#guest) authentication it will be ignored and the [Guest](#guest) `email` will be used and under [Remote Application](#remote-application) or [Remote User](#remote-user) it will be taken as is with just a format check
* `subject` : (`string`) subject sent to recipients (may be empty)
* `message` : (`string`) message sent to recipients (may be empty)

Returns a [Transfer](#transfer) object.

Returns a `Location` HTTP header giving the path to the new [Transfer](#transfer).

Request example :

	POST /transfer

	{
		"files": [
			{
				"name": "report.doc",
				"size": "102598",
				"mime_type": "application/ms.word",
				"cid": "rnd_341546546545646"
			},
			{
				"name": "funny.ppt",
				"size": "23589654",
				"mime_type": "application/ms.powerpoint",
				"cid": "rnd_67765146157651"
			}
		],
		"recipients": ["globi@bar.tld"],
		"options": ["email_download_complete", "email_report_on_closing"],
		"expires": 1422365666,
		"from": "foo@bar.tld",
		"subject": "Here is the promished files"
	}

Response example :

	{
		"id": 42,
		"user_id": "145879@idp.tld",
		"user_email": "foo@bar.tld",
		"subject": "Here is the promished files",
		"message": null,
		"created": {
			"raw": 1421328866,
			"formatted": "15/01/2015"
		},
		"expires": {
			"raw": 1422365666,
			"formatted": "27/01/2015"
		},
                "expiry_date_extension": 1,
		"options": ["email_download_complete", "email_report_on_closing"],
		"files": [
			{
				"id": 25,
				"transfer_id": 42,
				"uid": "7ecab9c0-9abf-eee9-9ab4-00000cc39816",
				"name": "report.doc",
				"size": 102598,
				"sha1": null
			},
			{
				"id": 31,
				"transfer_id": 42,
				"uid": "179558c7-c5be-5428-0b2e-00007ce9c792",
				"name": "funny.ppt",
				"size": 23589654,
				"sha1": null
			}
		],
		"recipients": [
			{
				"id": 59,
				"transfer_id": 42,
				"token": "0c27af59-72d1-0349-aa59-00000a8076d9",
				"email": "globi@bar.tld",
				"created": {
					"raw": 1421328866,
					"formatted": "15/01/2015"
				}
				"last_activity": null,
				"options": null,
				"download_url": "https://filesender.org/?s=download&token=0c27af59-72d1-0349-aa59-00000a8076d9",
				"errors": []
			}
		]
	}

### POST /transfer/{id}/recipient

Add a recipient to a specific [Transfer](#transfer).

Accessible to : [owner](#owner).

Parameters :

* `id` (`integer`) transfer unique identifier

Request body fields :

* `recipient` : (`string`) recipient's email address

Returns a [Recipient](#recipient) object.

Returns a `Location` HTTP header giving the path to the new [Recipient](#recipient).

### PUT /transfer/{id}

Update a specific [Transfer](#transfer).

Accessible to : [owner](#owner), [chunk upload security](#chunk-upload-security) fallback accepted for `complete` property setting only.

Parameters :

* `id` (`integer`) transfer unique identifier

Request body fields :

* `complete` : (`boolean`) if set to `true` this signals the server that every file has been uploaded and that the [Transfer](#transfer) should be made available and sent to [Recipients](#recipient) (optionnal)
* `closed` : (`boolean`) if set to `true` this signals the server that the [Transfer](#transfer) should be closed (optionnal)
* `extend_expiry_date` : (`boolean`) if set to `true` this asks the server to extend the [Transfer](#transfer)'s expiry date as allowed by the configuration (optionnal)
* `remind` : (`boolean`) if set to `true` this asks the server to send a reminder of the [Transfer](#transfer) availability to its [Recipients](#recipient) (optionnal)

Returns the [Transfer](#transfer).

Request example :

	PUT /transfer/42

	{
		"complete": true
	}

Response example :

    {
        "id": 42,
        "user_id": "145879@idp.tld",
        "user_email": "foo@bar.tld",
        "subject": "Here is the promished files",
        "message": null,
        "created": {
            "raw": 1421328866,
            "formatted": "15/01/2015"
        },
        "expires": {
            "raw": 1422365666,
            "formatted": "27/01/2015"
        },
        "expiry_date_extension": 1,
        "options": ["email_download_complete", "email_report_on_closing"],
        "files": [
            {
                "id": 25,
                "transfer_id": 42,
                "uid": "7ecab9c0-9abf-eee9-9ab4-00000cc39816",
                "name": "report.doc",
                "size": 102598,
                "sha1": null
            },
            {
                "id": 31,
                "transfer_id": 42,
                "uid": "179558c7-c5be-5428-0b2e-00007ce9c792",
                "name": "funny.ppt",
                "size": 23589654,
                "sha1": null
            }
        ],
        "recipients": [
            {
                "id": 59,
                "transfer_id": 42,
                "token": "0c27af59-72d1-0349-aa59-00000a8076d9",
                "email": "globi@bar.tld",
                "created": {
                    "raw": 1421328866,
                    "formatted": "15/01/2015"
                }
                "last_activity": null,
                "options": null,
                "download_url": "https://filesender.org/?s=download&token=0c27af59-72d1-0349-aa59-00000a8076d9",
                "errors": []
            }
        ]
    }



### DELETE /transfer/{id}

Close a specific [Transfer](#transfer).

Accessible to : [owner](#owner), [chunk upload security](#chunk-upload-security) fallback accepted for early [Transfer](#transfer) statuses (`created`, `started` and `uploading`, but not `available`).

Parameters :

* `id` (`integer`) transfer unique identifier

Returns `true`.

## File

### GET /file/{id}

Get info about a specific [File](#file).

Accessible to : [owner](#owner).

Parameters :

* `id` (`integer`) file unique identifier

Returns a [File](#file).

### POST /file/{id}/whole

Upload file body as a whole (legacy mode).

Accessible to : [owner](#owner), [chunk upload security](#chunk-upload-security) fallback accepted.

Parameters :

* `id` (`integer`) file unique identifier

The request content type MUST be `multipart/form-data`, at least a `file` part is expected.

The request body can also contain a field whose name is the same as the server's `session.upload_progress.name` PHP configuration parameter and whose value is randomly choosen to be able to get upload progress informations (see `GET /legacyuploadprogress/{key}`).

Returns the [File](#file).

Returns a `Location` HTTP header giving the path to the [File](#file).

### PUT /file/{id}/chunk/{offset}

Add a chunk to a [File](#file)'s body at the given `offset`.

Accessible to : [owner](#owner), [chunk upload security](#chunk-upload-security) fallback accepted.

Parameters :

* `id` (`integer`) file unique identifier
* `offset` (`integer`) body offset in bytes

The request content type MUST be `application/octet-stream`.

Several request headers SHOULD be provided so that the server can check validity of sent data :

* `X-Filesender-File-Size` (`integer`) should be equal to the file size
* `X-Filesender-Chunk-Offset` (`integer`) should be equal to `offset`
* `X-Filesender-Chunk-Size` (`integer`) the sent chunk size

The sent chunk size MUST not be over `upload_chunk_size`.

Returns the [File](#file).

Request example :

	PUT /file/27/chunk/25000000
	X-Filesender-File-Size: 5987465211
	X-Filesender-Chunk-Offset: 25000000
	X-Filesender-Chunk-Size: 5000000

	<5000000 bytes of data from the file>

Response example :

	{
		"id": 27,
		"transfer_id": 42,
		"uid": "7ecab9c0-9abf-eee9-9ab4-00000cc39816",
		"name": "big_file.tgz",
		"size": 5987465211,
		"sha1": null
	}

### PUT /file/{id}

Update a [File](#file).

Accessible to : [owner](#owner), [chunk upload security](#chunk-upload-security) fallback accepted.

Parameters :

* `id` (`integer`) file unique identifier

Request body fields :

* `complete` : (`boolean`) if set to `true` this signals the server that all chunks for the file have been uploaded

Returns `true`.

### DELETE /file/{id}

Delete a [File](#file).

Accessible to : [owner](#owner).

Parameters :

* `id` (`integer`) file unique identifier

If the related [Transfer](#transfer) still has [Files](#file) the recipients will be notified of the [File](#file) removal.

If the [Transfer](#transfer) only had the current [File](#file) it will be closed.

Returns `true`.

## Recipient

### GET /recipient/{id}

Get info about a specific [Recipient](#recipient).

Accessible to : [owner](#owner).

Parameters :

* `id` (`integer`) recipient unique identifier

Returns a [Recipient](#recipient).


### PUT /recipient/{id}

Update a [File](#recipient).

Accessible to : [owner](#owner).

Parameters :

  * `id` (`integer`) recipient unique identifier

Request body fields :

  * `remind` : (`boolean`) if set to `true` this asks the server to send a reminder of the [Transfer](#transfer) availability to the [Recipient](#recipient) (optionnal)

Returns the [Recipient](#recipient).



### DELETE /recipient/{id}

Delete a specific [Recipient](#recipient).

Accessible to : [owner](#owner).

Parameters :

* `id` (`integer`) recipient unique identifier

If the related [Transfer](#transfer) still has [Recipients](#recipient) the recipient will be notified of its removal.

If the [Transfer](#transfer) only had the current [Recipient](#recipient) it will be closed.

Returns `true`.

## Guest

### GET /guest

List of user's [Guests](#guest) (same as `GET /guest/@me`).

Accessible to : [users](#user).

Returns an `array of Guest`, see [Guest](#guest).

### GET /guest/@all

List of all available [Guests](#guest).

Accessible to : [admin](#admin).

Returns an `array of Guest`, see [Guest](#guest).

### GET /guest/{id}

Get info about a specific [Guest](#guest).

Accessible to : [owner](#owner).

Parameters :

* `id` (`integer`) guest unique identifier

Returns a [Guest](#guest).

### POST /guest

Create a new [Guest](#guest) from request.

Accessible to : [users](#user).

Request body fields :

* `recipient` : (`string`) recipient email addresse
* `from`     : (`string`) choosen sender email address, under [Service Provider](#service-provider) authentication it MUST be one of the authenticated user email addresses and under [Remote Application](#remote-application) or [Remote User](#remote-user) it will be taken as is with just a format check
* `subject`  : (`string`) subject sent to recipient (may be empty)
* `message`  : (`string`) message sent to recipient (may be empty)
* `options`  : (`array`)
  * `guest`    : (`array of string`) guest options (values defined in `classes/constants/GuestOptions.class.php`)
  * `transfer` : (`array of string`) created [Transfer](#transfer) options (values defined in `classes/constants/TransferOptions.class.php`)
* `expires`  : (`integer`) wanted expiry date as a UNIX timestamp, MUST respect `max_guest_days_valid` configuration parameter

This will send an email to the created [Guest](#guest).

Returns a [Guest](#guest) object.

Returns a `Location` HTTP header giving the path to the new [Guest](#guest).

Request example :

	POST /guest

	{
		"recipient": "globi@bar.tld",
		"options": ["valid_only_one_time"],
		"transfer_options": ["email_download_complete", "email_report_on_closing"],
		"expires": 1422365666,
		"from": "foo@bar.tld",
		"subject": "Send me a file :)"
	}

Response example :

	{
		"id": 31,
		"user_id": "145879@idp.tld",
		"user_email": "foo@bar.tld",
		"email": "globi@bar.tld",
		"token": "7a48ff78-442f-9449-c292-00002399a22f",
		"transfer_count": 0,
		"subject": "Send me a file :)",
		"message": null,
		"options": ["valid_only_one_time"],
		"transfer_options": ["email_download_complete", "email_report_on_closing"],
		"created": {
			"raw": 1421328866,
			"formatted": "15/01/2015"
		},
		"expires": {
			"raw": 1422365666,
			"formatted": "27/01/2015"
		},
		"errors": []
	}

### PUT /guest/{id}

Update a specific [Guest](#guest).

Accessible to : [owner](#owner).

Parameters :

* `id` (`integer`) transfer unique identifier

Request body fields :

* `remind` : (`boolean`) if set to `true` this asks the server to send a reminder to the [Guest](#guest)

Returns `true`.

Request example :

	PUT /guest/42

	{
		"remind": true
	}

Response example :

	true

### DELETE /guest/{id}

Delete a [Guest](#guest).

Accessible to : [owner](#owner).

Parameters :

* `id` (`integer`) recipient unique identifier

The [Guest](#guest) will be notified.

Returns `true`.

## User

### GET /user

Same as `GET /user/@me`

Get preferences of the current user.

Accessible to : [users](#user).

Returns an `object`.

Response example :

    {
        "id": "foo@bar.org",
        "additional_attributes": { // Depends on configured additional attributes
            "idp": "https://idp.example.org/",
            "other_attribute": "value"
        },
        "aup_ticked": true,
        "transfer_preferences": ["email_download_complete", "email_report_on_closing"],
        "guest_preferences": ["valid_only_one_time"],
        "created": {
            "raw": 1421328866,
            "formatted": "15/01/2015"
        },
        "last_activity": {
            "raw": 1422365666,
            "formatted": "27/01/2015"
        },
        "lang": "en",
        "frequent_recipients": [],
        "remote_config": "https://filesender.example.org/|foo@bar.org|df6g17cs6g87df6g3cs7d3gvdf7cf0ds3v" // Set if remote user enabled, null otherwise
    }


### GET /user/{id}

Get preferences of a specific user.

Accessible to : [admin](#admin).

Returns an `object`.

Request example :

    GET /user/foo@bar.org

Response example :

    {
        "id": "foo@bar.org",
        "additional_attributes": { // Depends on configured additional attributes
            "idp": "https://idp.example.org/",
            "other_attribute": "value"
        },
        "aup_ticked": true,
        "transfer_preferences": ["email_download_complete", "email_report_on_closing"],
        "guest_preferences": ["valid_only_one_time"],
        "created": {
            "raw": 1421328866,
            "formatted": "15/01/2015"
        },
        "last_activity": {
            "raw": 1422365666,
            "formatted": "27/01/2015"
        },
        "lang": "en",
        "frequent_recipients": [],
        "remote_config": "https://filesender.example.org/|foo@bar.org|df6g17cs6g87df6g3cs7d3gvdf7cf0ds3v" // Set if remote user enabled, null otherwise
    }



### GET /user/@me/frequent_recipients

Get a list of frequent recipients of the current user.

Accessible to : [users](#user).

This endpoint can filter its response with the `filterOp[contains]` filter, example : `GET /user/@me/frequent_recipients?filterOp[contains]=foo`.

Returns an `array of string`.

### GET /user/{id}/frequent_recipients

Get a list of frequent recipients of a specific user.

Parameters :

* `id` (`string`) user unique identifier

Accessible to : user itself, [admin](#admin).

This endpoint can filter its response with the `filterOp[contains]` filter, example : `GET /user/user_uid/frequent_recipients?filterOp[contains]=foo`.

Returns an `array of string`.

### PUT /user

Set current user preferences.

Accessible to : [users](#user).

Request body fields :

* `lang` : (`string`) lang code of user's prefered language

Returns `true`.

### PUT /user/{id}

Set user preferences

Accessible to : user itself, [admin](#admin).

Parameters :

* `id` (`string`) user unique identifier

Request body fields :

* `lang` : (`string`) lang code of user's prefered language

Returns `true`.

## Misc endpoints

### GET /info

Get informations about the instance.

Accessible to : public.

Returns an `object` whose properties are informations about the FileSender instance.

The set of returned informations depends on the `disclose` configuration parameter.

At the very least the `url` property is set and contains the URL of the FileSender instance.

This enpoint may be queried in order to know useful configuration parameters for uploading like `upload_chunk_size`, `default_transfer_days_valid` or more when they are disclosed.

### PUT /config

### GET /legacyuploadprogress/{key}

Get information about a legacy (whole file) upload progress.

Parameters :

* `key` (`string`) the file's choosen upload tracking random (see `POST /file/{id}/whole`)

Returns an `object` :

* `error` (`integer`) not 0 if something bad happened
* `done` (`boolean`) is the upload complete
* `start_time` (`integer`) UNIX timestamp of when the server started receiving data
* `bytes_processed` (`integer`) number of bytes already received

### GET /lang

Get translations (merged between default language, configured language and maybe user preference or browser language).

Returns an `object` whose keys are string identifiers and values are the translation related to them.

### GET /echo

Echoes back info about your request, useful for testing.

Parameters : any

Returns `object` :

* `args` : (`array of string`) the decoded URL tokens
* `request` : (`mixed`) the decoded request body
* `user` : (`string`) authenticated user unique identifier
* `auth` : (`object`)
	* `remote` : (`boolean`) is the detected authentication a [Remote Application](#remote-application) or a [Remote User](#remote-user)
	* `attr` : (`object`) authentication attributes

# Errors

Errors (or exceptions) are returned in a structured format with the following fields :

* `message` : (`string`) translatable id telling what happened
* `uid` : (`string`) unique error identifier, is included in the logs
* `details` : (`array of string`) details about the error (may be empty, null or even not set)

All possible errors messages are not listed here but can be found in the files under `classes/exceptions/`.

Common errors to all endpoints are :

* `rest_authentication_required` : need an authenticated user
* `rest_ownership_required` : need ownership of target resource (admin owns all)
* `rest_admin_required` : need an authenticated admin
* `rest_missing_parameter` : an URL token is missing
* `rest_bad_parameter` : an URL token does not have the expected type

Example :

	{
		"message": "rest_authentication_required",
		"uid": "57gf523gsdfg3"
	}

# Objects fields definition

## Transfer

* `id` : (`integer`) transfer unique identifier
* `user_id` : (`string`) uploader / guest creator unique identifier
* `user_email` : (`string`) uploader (user or guest) email
* `subject` : (`string`) subject sent to recipients
* `message` : (`string`) message sent to recipients
* `created` : (`Date`) creation [Date](#date)
* `expires` : (`Date`) expiry [Date](#date)
* `expiry_date_extension` : (`integer`) number of expiry date extensions that remains
* `options` : (`array of string`) options (values defined in `classes/constants/TransferOptions.class.php`)
* `files` : (`array of File`) see [File](#file)
* `recipients` : (`array of Recipient`) see [Recipient](#recipient)

## File

* `id` : (`integer`) file unique identifier
* `transfer_id` : (`integer`) related [Transfer](#transfer) unique identifier
* `uid` : (`string`) random unique identifier
* `name` : (`string`) file original name
* `size` : (`integer`) file size in bytes
* `sha1` : (`string`) file hash, unused, unset @TODO should we remove it until we have a viable technical solution ?

## Recipient

* `id` : (`integer`) recipient unique identifier
* `transfer_id` : (`integer`) related [Transfer](#transfer) unique identifier
* `token` : (`string`) download unique random token, used for authentication
* `email` : (`string`) recipient email address
* `created` : (`Date`) creation [Date](#date)
* `last_activity` : (`Date`) last activity [Date](#date)
* `options` : (`null`) unused, unset @TODO what was it for ?
* `download_url` : (`string`) Download page URL
* `errors` : (`array of Tracking error`) see [Tracking error](#tracking-error)

## Guest

* `id` : (`integer`) guest unique identifier
* `user_id` : (`string`) guest creator unique identifier
* `user_email` : (`string`) guest creator email
* `email` : (`string`) guest email address
* `token` : (`string`) guest unique random token, used for authentication
* `transfer_count` : (`integer`) number of transfers uploaded
* `subject` : (`string`) subject sent to guest
* `message` : (`string`) message sent to guest
* `options` : (`array of string`) options (values defined in `classes/constants/GuestOptions.class.php`)
* `transfer_options` : (`array of string`) created transfers options (values defined in `classes/constants/TransferOptions.class.php`)
* `created` : (`Date`) creation [Date](#date)
* `expires` : (`Date`) expiry [Date](#date)
* `errors` : (`array of Tracking error`) see [Tracking error](#tracking-error)

## Tracking error

* `type` : (`string`) error type (values defined in `classes/constants/TrackingEventTypes.class.php`)
* `date` : (`Date`) error [Date](#date)
* `details` : (`string`) technical details about what hapenned

## Audit log

* `date` : (`Date`) event [Date](#date)
* `event` : (`string`) event type (values defined in `classes/constants/LogEventTypes.class.php`)
* `author` : (`object`) information about who did the action
	* `type` : (`string`) author type, can be `User`, `Recipient` or `Guest`
	* `id` : (`string`) author's unique identifier
	* `ip` : (`string`) author's IP address
	* `email` : (`string`) author's email address (may not be provided)
* `target` : (`object`) information about the target of the event
	* `type` : (`string`) target type, can be `Transfer`, `File` or `Recipient`
	* `id` : (`string`) target's unique identifier
	* `name` : (`string`) original name if `type` is `File` (not provided otherwise)
	* `size` : (`integer`) size in bytes if `type` is `File` (not provided otherwise)
	* `email` : (`string`) email address if `type` is `Recipient` (not provided otherwise)

## Date

* `raw` : (`integer`) unix timestamp
* `formatted` : (`string`) human readable version according to lang configuration

# Authentication

## Levels of authentication

### User

Anybody authenticated through a Service provider, a [Remote User](#remote-user) or a [Remote Application](#remote-application) providing the `remote_user` argument.

Authentication through using [Recipient](#recipient) token is not considered as beeing a user.

### Owner

An authenticated user who is owner of the current resource ([Admin](#admin) is considered as owner of everything).

### Admin

An authenticated user whose `id` is listed in the `admin` configuration parameter, or a [Remote Application](#remote-application) whose configuration include the `isAdmin` entry set to `true`.

## Authentication evaluation workflow

The servers look up for authentication in this order and stops on the first detected one :

1. [Guest token](#guest-token)
2. [Service provider](#service-provider)
3. [Remote application](#remote-application) (if enabled)
4. [Remote user](#remote-user) (if enabled)

## Guest token

This mode requires a `vid` URL argument corresponding to an existing [Guest](#guest).

This mode has restricted rights in most of the application.

## Service Provider

This mode requires the configured service provider to return a valid authentication.

## Remote application

This mode is intended towards giving access to applications FileSender can fully trust, like a big file generating script of yours that need to send its file to recipients.

This mode expects a [signed request](#signed-request) and a `remote_application` URL argument.

Additionally if a `remote_user` argument is provided all opérations will be conducted with this identity.

## Remote user

This mode is intended to give users to possibility to create a trust between another of their application and their "account" on FileSender, like a mail client beeing able to send big attachments through FileSender.

This mode expects a [signed request](#signed-request) and a `remote_user` URL argument.

## Signed request

A signed request if formed by adding several informations to a request to be made :

* a `timestamp` URL argument containing a UNIX timestamp of the current date and time, this ensure the request will not be replayed later as FileSender will reject any signed requests older than 15 seconds. In case of uploads with a slow connexion you may have to put this in the future.
* a `remote_application` URL argument in the case of a [Remote Application](#remote-application) authentication, this contains the name of the application, shared between FileSender and the application sending the request
* a `remote_user` URL argument in the case of a [Remote User](#remote-user) authentication, this contains the unique user identifier, shared between FileSender and the application sending the request
* a `signature` URL argument, this is a SHA1 HMAC signature of the prepared request (see below) with :
	* the shared application secret in case of a [Remote Application](#remote-application) authentication
	* the shared user secret in case of a [Remote User](#remote-user) authentication

### Preparing a request for signing

To prepare a request for signing one must concatenate following parts, in order, using `&` separator :

* lowercased HTTP method : `get`, `post`, `put` or `delete`
* URL with alphabetically ordered arguments, without scheme or signature argument, example `filesender.org/rest.php/transfer/@me?remote_application=foo&remote_user=user_id&timestamp=1234567890`
* request body as a string (if there is one)

#### Examples

Remote application request to get a user's transfers list : `get&filesender.org/rest.php/transfer/@me?remote_application=foo&remote_user=user_id&timestamp=1234567890`

Remote user request to upload the chunk at offset 2597874 in file with id 42 : `put&filesender.org/rest.php/file/42/chunk/2597874?remote_user=user_id&timestamp=1234567890&<chunk_data>`

Sample PHP code :

	$base = 'https://filesender.org/rest.php';

	$method = 'POST';

	$resource = '/guest';

	$data = array(
		'recipient' => 'foo@bar.tld',
		'from' => 'user_email@example.org',
		'subject' => 'For the project report',
		'message' => 'Come ! Send me the file !',
		'options' => array('can_only_send_to_me'),
		'transfer_options' => array(),
		'expires' => time() + 7 * 24 * 3600
	);

	$user = 'user_id';
	$secret = 'user_secret';

	$args = 'remote_user='.$user.'&timestamp='.time();
	$body = json_encode($data);

	$to_sign = strtolower($method).'&'.preg_replace('`https?://`', '', $base).$resource;
	$to_sign .= '?'.$args;
	$to_sign .= '&'.$body;

	$url = $base.$path.'?'.$args.'&signature='.hash_hmac('sha1', $to_sign, $secret);

	// Send request with curl with $method to $url and $body content

### More information

You will find an sample client under `scripts/client/FilesenderRestClient.class.php`.

## Chunk upload security

As when authenticated with a [Service Provider](#service-provider) the session can be broken during upload depending on the [Service Provider](#service-provider) configuration (absolute session lifetime ...) it is possible to enable the key based chunk upload security.

This mode is enabled by setting the `chunk_upload_security` configuration parameter to `key` @TODO { need default value }.

In this mode you may send the chunk's related [File](#file)'s `uid` property along with the request as the `key` URL argument.

In a [Transfer](#transfer) context any `uid` property from any [File](#file) related to the [Transfer](#transfer) will be accepted.

Note that this mecanism is a fallback in the case your client lost all other kind of authentication, this is not necessary under [Remote Application](#remote-application) or [Remote User](#remote-user) as they are always valid as long as the [signature](#signed-request) is right.

# The upload process

To upload files one must follow a set of operations in order.

1. Create the transfer using `POST /transfer`, this gives back data about the transfer, especially file identifiers to be used later
2. Send file data using `PUT /file/{file_id}/chunk/{chunk_offset}`, chunks don't need to be in order, it is possible to upload several chunks at the same time by making requests in parallel
3. Once a file data has been sent signal file completion using `PUT /file/{file_id}` with payload `{"complete":true}`
4. Once all file completions signals have been sent signal transfer completion using `PUT /transfer/{transfer_id}` with payload `{"complete":true}`
