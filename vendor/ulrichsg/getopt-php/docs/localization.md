---
layout: default
title: Localization
permalink: /localization.html
---
# {{ page.title }}

Texts shown to a user are localized using the `Translator` which is a simple key-value translation class using php files
for a translation map. These files can be found in [resources/localization](https://github.com/getopt-php/getopt-php/tree/master/resources/localization)
and contributions are highly appreciated.

By default the Translator is using the English translations. You can change to a predefined translation by calling
`GetOpt::setLang($language)` where `$language` is a two-letter iso code of the language.

```php
<?php
\GetOpt\GetOpt::setLang('de');
```

## Custom language files

You can also use custom language files by passing a path to your language file. The script must return an array in the
same format as the bundled language files.

```php
<?php
\GetOpt\GetOpt::setLang(__DIR__ . '/path/to/cn.php');
```

Translations missing in a language file are translated with the English translation table.

## Errors messages

The messages of exceptions thrown because of user input are also translated using this translator. 
