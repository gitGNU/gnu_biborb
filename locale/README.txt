This file contains instruction to localize BibORB.

Gettext methods is used to provide the localization of biborb.

The 'locale' repository contains localized data used to display 
messages in a selected language. By default, english is used ('en'
directory) if data are missing for a given language.

If you want to add the support for a language:

    1) Copy the 'en' directory and rename it into the name of your
    locale.
    2) Translate each txt file
    3) Edit the biborb.po file and translate each string starting 
    with msgstr
    For instance:
        Original File:
            msgid "Update"
            msgstr ""
        Localized File:
            msgid "Update"
            msgstr "Mettre à jour"
    4) Compile the biborb.po file.
        msgfmt -o biborb.mo biborb.po
    
    5) Edit the config.php file and set the $language variable to 
    the name of your locale.
    
    6) If the localization doesn't show, restart the web server to
    force it reload the localization data.
    
For more information about gettext: http://www.gnu.org/software/gettext/

