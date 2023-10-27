# Language and translations

The FileSender project uses [POEditor](https://www.poeditor.com) to manage the different
languages FileSender supports. The translations on poeditor.com are
considered the definite master copy. Please see the documentation on
the Web for information about how to contribute to the translations
for FileSender. https://docs.filesender.org/v2.0/i18n/

## Terms, translations and reference language

For translating the UI and email, there is a difference between terms, translations 
and reference language:

### Term

A term is a unique identifier for something that needs to be translated. This 
term is used in FileSender where we want to replace the term with a translated 
version

An example is:
`my_transfers`

### Translation

A translation is the representation of a term in a language. 
The example translation of the term `my_transfers` in the language Dutch is 
`Mijn overdrachten`

### Reference language

The reference language is the language that a term is *always* translated in.
This language is also shown as a hint in POEditor

# Automations

Whenever a change is done to the `development3` branch, automated language 
checks are performed:

- Did the change introduce new terms? These terms are then automatically 
added to POEditor and tagged as `new`
- Did the change remove terms? These terms are then automatically marked in 
POEditor as 'obsolete', and will be removed after 3 releases.

# Scripts

There is also a method to bring new language terms from files in the
language directory of a pull request into poeditor. Please see the
scripts/language/README.md file for more information about that
process. One must be careful doing that because you do not want to clobber
existing translations.

This directory contains the distribution files for the various language
definitions. Definitions and mappings in these files can be over-ruled
by a corresponding file (with the same name) in the main ./config directory. 
Local customisations should be done in the ./config files. 

The naming scheme of the language files in this directory and mapping of
browser tags to language files is based on Best Current Practices as defined
by BCP 47 and related ICU/ISO guidelines:

# File naming

<language>_<COUNTRY>.php

<language> is the shortest two/three letter ISO 639 language code in lowercase
<COUNTRY> is the ISO 3166-1 country/region/territory code in UPPERcase

Use of "_" (underscore) in filenames follows the convention for defining
'locales' on modern Linux/Unix systems and defined/allowed by the related
Unicode/ICU definitions, see 
http://www.unicode.org/reports/tr35/#Unicode_Language_and_Locale_Identifiers and
http://userguide.icu-project.org/locale).

## Reference language naming

The reference language is named `master` in the language tree. Since we are currently 
using en_GB as reference language, this is effectively a copy.

# Browser tags mapping

Mapping from browser language tags to language files is done in locale.php.
Note that browser tags for language preference use the "-" hyphen as defined
in BCP 47: http://tools.ietf.org/html/bcp47 Tags for Identifying Languages
Tags specified in locale.php should be all lowercase.
