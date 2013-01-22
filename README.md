# Options

## Get value

- `:key` - Option id key
- `:default` (optional) - Accepts string, integer, bool and array. If the option is not set in the DB, this value will be used

`$options->get(:key, :default)`

## Set value
	
- `:key` - Option id key
- `:value` - Accepts string, integer, bool and array

`$options->update(:key, :value)`


# Options Panel (extension)

## Add an option

	$options->add_option(array(
		'key' => 'text',
		'label' => 'Text',
		'type' => 'text',
		'default' => 'this is the default value'
	));
	
	// Stores a JSON array with the selected values ( ["one","three"] )
	$options->add_option(array(
		'key' => 'check',
		'label' => 'Check',
		'type' => 'check',
		'options' => array(
			'one' => 'Label One',
			'two' => 'Label Two',
			'three' => 'Label Three',
		),
		'default' => array(
			'one', // Will display "one" already checked
			'three' // Will display "three" already checked
		)
	));
	
	// Stores the selected value ( one )
	$options->add_option(array(
		'key' => 'radio',
		'label' => 'Check',
		'type' => 'radio',
		'options' => array(
			'one' => 'Label One',
			'two' => 'Label Two',
		),
		'default' => 'one' // Will display "one" already checked
	));


# To-do

- Better documentation
- More fields regarding the interface
- Allow for `update` to recieve an array for batch processing, making use of prepared statements