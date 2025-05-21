# Laravel-stapler

Laravel-Stapler is a Stapler-based file upload package for the Laravel framework.  It provides a full set of Laravel commands, a migration generator, and a cascading package config on top of the [Stapler](https://github.com/CodeSleeve/stapler) package.  It also bootstraps Stapler with very sensible defaults for use with Laravel.  If you are wanting to use [Stapler](https://github.com/CodeSleeve/stapler) with Laravel, it is strongly recommended that you use this package to do so.

Laravel-Stapler was created by [Travis Bennett](https://twitter.com/tandrewbennett).

* [Requirements](#requirements)
* [Installation](#installation)
* [Deprecations](#deprecations)
* [Migrating From Stapler v1.0.0-Beta4](#migrating-from-Stapler-v1.0.0-Beta4)
* [Quick Start](#quickstart)
* [Commands](#commands)
  * [Fasten](#fasten)
  * [Refresh](#refresh)
* [Troubleshooting](#troubleshooting)
* [Contributing](#contributing)

## Requirements
This package currently requires php >= 8 as well as Laravel >= 10.

If you're going to be performing image processing as part of your file upload, you'll also need GD, Gmagick, or Imagick (your preference) installed as part of your php environment.

## Installation
Laravel-Stapler is distributed as a composer package, which is how it should be used in your app.

Install the package using Composer.  Edit your project's `composer.json` file to require `codesleeve/laravel-stapler`.

```js
  "require": {
    "laravel/framework": "4.*",
    "codesleeve/laravel-stapler": "1.0.*"
  }
```

Once this operation completes, the final step is to add the service provider.

For Laravel 10, Open `config/app.php`, and add a new item to the providers array:
```php
    'Neko\LaravelStapler\Providers\L5ServiceProvider'
```

## Quickstart
In the document root of your application (most likely the public folder), create a folder named system and
grant your application write permissions to it.  For this, we're assuming the existence of an existing `User` model in which we're going to add an avatar image to.

In your model:

```php
use Neko\Stapler\ORM\StaplerableInterface;
use Neko\Stapler\ORM\EloquentTrait;

class User extends Eloquent implements StaplerableInterface {
	use EloquentTrait;

	// Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
	protected $fillable = ['avatar', 'first_name', 'last_name'];

	public function __construct(array $attributes = array()) {
		$this->hasAttachedFile('avatar', [
			'styles' => [
				'medium' => '300x300',
				'thumb' => '100x100'
			]
		]);

		parent::__construct($attributes);
	}
}
```

> Make sure that the `hasAttachedFile()` method is called right before `parent::__construct()` of your model.

From the command line, use the migration generator:

```php
php artisan stapler:fasten users avatar
php artisan migrate
```

In your new view:
```php
<?= Form::open(['url' => action('UsersController@store'), 'method' => 'POST', 'files' => true]) ?>
	<?= Form::input('first_name') ?>
	<?= Form::input('last_name') ?>
	<?= Form::file('avatar') ?>
    <?= Form::submit('save') ?>
<?= Form::close() ?>
```

In your controller:
```php
public function store()
{
	// Create and save a new user, mass assigning all of the input fields (including the 'avatar' file field).
    $user = User::create(Input::all());
}
```

In your show view:
```php
<img src="<?= $user->avatar->url() ?>" >
<img src="<?= $user->avatar->url('medium') ?>" >
<img src="<?= $user->avatar->url('thumb') ?>" >
```

To detach (reset) a file, simply assign the constant STAPLER_NULL to the attachment and the save):

```php
$user->avatar = STAPLER_NULL;
$user->save();
```

This will ensure the corresponding attachment fields in the database table record are cleared and the current file is removed from storage.  The database table record itself will not be destroyed and can be used normally (or even assigned a new file upload) as needed.

## Commands
### fasten
This package provides a `fasten` command that can be used to generate migrations for adding image file fields to existing tables.  The method signature for this command looks like this:
`php artisan stapler:fasten <tablename> <attachment>`

In the quickstart example above, calling
`php artisan stapler:fasten users avatar` followed by `php artisan migrate` added the following fields to the users table:

*   (string) avatar_file_name
*   (integer) avatar_file_size
*   (string) avatar_content_type
*   (timestamp) avatar_updated_at

### refresh
The `refresh` command can be used to reprocess uploaded images on a model's attachments.  It works by calling the reprocess() method on each of the model's attachments (or on specific attachments only).  This is very useful for adding new styles to an existing attachment when a file has already been uploaded for that attachment.

Reprocess all attachments for the ProfilePicture model:
`php artisan stapler:refresh ProfilePicture`

Reprocess only the photo attachment on the ProfilePicture model:
`php artisan stapler:refresh TestPhoto --attachments="photo"`

Reprocess a list of attachments on the ProfilePicture model:
`php artisan stapler:refresh TestPhoto --attachments="foo, bar, baz, etc"`
