# Remove Text From Files
Remove any text from all files under specified directory.

The script recursively inspects a folder. All files that are lesser than size threshold
and has allowed extension are inspected for a search string (not regexp).
If such string is found it is removed from the file.
The script does not process files started with "." (dot).

Note: the script reads whole file into the memory. Therefore be careful with size threshold.

At the end script prints out the number of files with search string found.

## Installation
Download remove_text.php to any location on your computer and run it according to Usage section.

You need php-cli installed to run this script.

## Usage:
```
php remove_text.php
     --dir=DIRECTORY_PATH
     --search=STRING | --searchFile=FILE_PATH
     [--extensions=COMMA_SEPARATED_EXTENSIONS]
     [--size=SIZE_THRESHOLD]
```

`dir` - path to the directory to process

`search` - string to search. May be omitted if searchFile is set

`searchFile` - path the file containing search text

`extensions` - (optional) list of allowed extensions separated with comma (without spaces)

`size` - (optional) size threshold for files. Bigger files won't be processed. Default is 5 Mb.
