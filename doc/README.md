# Data docs

There is some extra markdown available:

- `{{ include:path }}` Includes the schema file at path. Path is relative to the `app/resources/schema` directory.

The frontmater for each data type must contain:

    schema: schemaname.json
    format: yaml or csv

This tells the processor where to find the page name and summary. It is also used to generate links to the data path and
schema id.
