# CLI
We have a few utilities to make it easier to create new CLI commands and one CLI command that has been incredibly useful on every project.

## Base class
The `RKV\Utilities\CLI\Base` class sets up CLI commands. When extending this class, the `public $assoc_args` variable must be extended. This is an array. Each string provided in the array will be registered as a CLI command.

The command invoked the `abstract public function callback( $args, $assoc_args )` method. This must also be extended in the child class. 

Use the `$this->set_args( $args, $assoc_args )` method at the top of the callback to set the `public $assoc_args` and `public $args` properties. Defaults for the `$assoc_args` can be set using the `$defaults` property. 

For `$args` to be set, these should be defined as named properties with default values in the `$args` property like:
```
	/**
	 * List of items used for the named args.
	 *
	 * @var array
	 */
	public $args = [
        'first-item'     => '',
        'second-item'    => '',
        'something-else' => false,
    ];
```

## Bulk Post Iterator

The `RKV\Utilities\CLI\Bulk_Post_Iterator` abstract class extends the `RKV\Utilities\CLI\Base` class. This class allows for easily iterating through posts in bulk with custom queries, processing, a progress bar, and error handling.

The `callback()` method is extended and sets the incoming args. The `$args` and `$defaults` method can be extended to add additional args. 

The `$dry_run` and `$verbose` properties are set by default from the incoming `$assoc_args`.

While iterating, a progress bar is created and after each query, the VIP memory clean up is called if available.

The `abstract protected function get_query_args( $page = 1 )` and `abstract protected function process_post( $post )` methods must be extended.

### get_query_args
This should return an array of query args. The current page is provided and should be used to paginate results. Incoming `$args` and `$assoc_args` can be used to modify the results.

### process_post
Each post in the query is handed over to this method. The `$post` object is passed to the method. Use this to build reports, modify posts, or any other action that should be taken.

### protected function output()
This is called automatically after the operation is complete. If there are errors, they will be output.

This method may be extended to generate reports or other custom output.

## rkv_meta_import
This `rkv_meta_import` CLI command allows importing meta to posts from a CLI command. 

This can be a good example of how to extend the `RKV\Utilities\CLI\Base` class.

## OPTIONS
[--file=<file>]
: The CSV file to import. This can be a local file path or a URL.

Paths are relative to the uploads directory. 
For example, if the file is located at `wp-content/uploads/posts.csv`, you can specify `--file=posts.csv`.

[--post-type=<post-type>]
: The post type to import. Defaults to 'post'.

[--overwrite]
: Whether to overwrite existing meta. Defaults to false.

[--dry-run]
: If set, the command will simulate the import process without making any changes to the database. This is useful for testing and verifying the CSV data before performing the actual import.

[--verbose]
: If set, the command will output detailed information about the import process.
