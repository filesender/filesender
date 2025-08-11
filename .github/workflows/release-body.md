## Release Version {{ .to_tag }}

Release date: {{ .date }}

## Distribution
Source snapshots are attached to this announcement and the git tag `{{ .to_tag }}` contains the base that these snapshots were created from.

## Installation
Documentation is available at http://docs.filesender.org/v2.0/install/

## Major changes since {{ .from_tag }}
{{ .db_changed }}
{{ .templates_changed }}

{{ .changelog }}

## Configuration changes


These options are detailed in the docs/v2.0/admin/configuration/index.md file as usual.

## Deprecations


## Support and Feedback
Please lodge new github issues for things that might improve the next release!
See Support and Mailinglists and Feature requests.
