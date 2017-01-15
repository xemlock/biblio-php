# RIS file format

## Mendeley Desktop 1.12.3

### Import

Multiple authors provided in a single `AU` tag are treated as a single
entry.

Keywords in separate `KW` tags are properly interpreted, if given in a
single tag, all lines are glued into one with a space character.

When title is in a multiple `TI` tags only the value of the first tag
is taken into account.

### Export

When exporting it saves title in `T1` tag, authors in multiple `A1` tags
and keywords in multiple `KW` tags.


## EndNote X7

### Import

Multiple authors provided in a single `AU` tag are treated as a single
entry.

Keywords in separate `KW` tags are properly interpreted. When given in a
single tag but on separate lines they are also interpreted as multiple
entries.

When title is in a multiple `TI` tags the resulting value will be glued with new line,
however only a first line will be presented in publications list.

### Export

Exported file starts with BOM.

Title is provided in both `ST` and `TI` tags.

Keywords are provided in a single `KW` tag, consecutive values are separated by
a new line.

Authors are provided in multiple `AU` tags, one per author.

Tags except for `TY`, `ID` and `ER` are sorted alphabetically.


