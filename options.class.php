<?php

class Options
{

	private $db;

	private $table_name;

	function __construct()
	{

		global $db;

		$this->db = $db;

		$this->table_name = 'options';

		$options_exist = $db->query('SHOW TABLES LIKE "'.$this->table_name.'"')->num_rows() > 0;

		if (!$options_exist) {
			$options_table_sql = "
				CREATE TABLE `{$this->table_name}` (
				  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				  `key` varchar(255) NOT NULL DEFAULT '',
				  `value` longtext,
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM;";

			// Create options table
			$this->db->query($options_table_sql);
		}
	}


	function get($key = null, $default = null)
	{
		if (!$key) return $this->all();

		$option = $this->db->single(array( 'key' => $key ), $this->table_name);

		if (!$option) return $default;

		// Try and decode json
		$option_json = json_decode($option->value, true);

		if ($option_json) return $option_json;

		return $option->value;
	}


	function update($key, $value)
	{
		$option = $this->db->single(array( 'key' => $key ), $this->table_name);

		if (is_array($value)) {
			$value = array_filter($value, function($v) {
				return !empty($v);
			});
			$value = json_encode($value);
		}

		$data = array(
			'key' => $key,
			'value' => $value
		);

		$do_option_command = '';
		if ($option) {
			$do_option_command = "UPDATE `{$this->table_name}` SET `value` = :value WHERE `key` = :key";
		} else {
			$do_option_command = "INSERT INTO `{$this->table_name}` (`key`, `value`) VALUES (:key, :value)";
		}

		$do_option = $this->db->pdo->prepare($do_option_command);

		return $do_option->execute($data);
	}


	function all($processed = true)
	{
		$options = $this->db->query('SELECT * FROM `'.$this->table_name.'`');

		if (!$processed) return $options->result_array();

		$options_output = array();
		foreach ($options->result_array() as $row) {

			// try json decode
			$option_json = json_decode($row['value'], true);
			if ($option_json) $row['value'] = $option_json;

			$options_output[$row['key']] = $row['value'];
		}
		return $options_output;
	}


}


class OptionsInterface extends Options {

	public $options;

	public $fields_group;

	public $action;

	function __construct()
	{

		parent::__construct();

		$this->options = array();

		$this->fields_group = 'options';

		$this->action = '';
	}


	function add_option($option)
	{
		$this->options[] = $option;
	}


	function prepare()
	{

		foreach ($this->options as $key => $option) {

			$default = (isset($option['default'])) ? $option['default'] : null;

			$this->options[$key]['value'] = $this->get($option['key'], $default);
			$this->options[$key]['id'] = preg_replace('/[\[\]]/', '', $this->fields_group.$option['key']);
			$this->options[$key]['name'] = $option['key'];
			if (!empty($this->fields_group))
				$this->options[$key]['name'] = $this->fields_group . '[' . $option['key'] . ']';

		}

	}


	// Fields

	function field_text($option)
	{
		extract($option);
		// key
		// label
		// type
		// options (array)

		$output = array();

		// Field label
		$output[] = '<label class="op-label op-label-'.$type.'" for="'.$id.'">';
			$output[] = '<span>'.$label.'</span>';
		$output[] = '</label>';

		$output[] = '<div class="op-field">';
			$output[] = '<input type="text" id="'.$id.'" name="'.$name.'" value="'.$value.'" placeholder="" />';
		$output[] = '</div>';

		return implode('', $output);
	}


	function field_textarea($option)
	{
		extract($option);
		// key
		// label
		// type
		// options (array)

		$output = array();

		// Field label
		$output[] = '<label class="op-label op-label-'.$type.'" for="'.$id.'">';
		$output[] = '<span>'.$label.'</span>';
		$output[] = '</label>';

		$output[] = '<div class="op-field">';
			$output[] = '<textarea id="'.$id.'" name="'.$name.'" placeholder="">'.$value.'</textarea>';
		$output[] = '</div>';

		return implode('', $output);
	}


	function field_check($option)
	{
		extract($option);
		// key
		// label
		// type
		// options (array)

		$output = array();

		// Field label
		$output[] = '<label class="op-label op-label-'.$type.'" for="'.$id.'">';
		$output[] = '<span>'.$label.'</span>';
		$output[] = '</label>';

		$output[] = '<div class="op-field">';

			foreach ($options as $opt_value => $opt_label) {

				$is_current = is_array($value) && in_array($opt_value, $value);
				if ($is_current === true) $is_current = 'checked="checked"';

				$output[] = '<input type="checkbox" id="'.$id.$opt_value.'" name="'.$name.'[]" value="'.$opt_value.'" '.$is_current.' /> <label for="'.$id.$opt_value.'">'.$opt_label.'</label>&nbsp;';
			}

		$output[] = '<input type="hidden" name="'.$name.'[]">';

		$output[] = '</div>';

		return implode('', $output);
	}

	function field_radio($option)
	{
		extract($option);
		// key
		// label
		// type
		// options (array)

		$output = array();

		// Field label
		$output[] = '<label class="op-label op-label-'.$type.'" for="'.$id.'">';
		$output[] = '<span>'.$label.'</span>';
		$output[] = '</label>';

		$output[] = '<div class="op-field">';

			foreach ($options as $opt_value => $opt_label) {

				$is_current = $opt_value === $value;
				if ($is_current === true) $is_current = 'checked="checked"';

				$output[] = '<input type="radio" id="'.$id.$opt_value.'" name="'.$name.'" value="'.$opt_value.'" '.$is_current.' /> <label for="'.$id.$opt_value.'">'.$opt_label.'</label>&nbsp;';
			}

		$output[] = '</div>';

		return implode('', $output);
	}

	function field_submit($option)
	{
		extract($option);
		// key
		// label
		// type
		// options (array)

		$output = array();

		$output[] = '<div class="op-field">';

			$output[] = '<button type="submit" class="op-button op-save" name="op-save" value="true">'.$label.'</button>';

		$output[] = '</div>';

		$output[] = '';
		return implode('', $output);
	}


	// Fields

	function show_options()
	{

		$output = array();

		$this->prepare();

		$output[] = '<form method="post" action="'.$this->action.'">';

		foreach ($this->options as $option) {

			$option_type = $option['type'];
			$option_type_method = 'field_'.$option_type;

			if ( method_exists($this, $option_type_method) ) {
				$output[] = '<div class="op-option op-type-'.$option_type.'">';
				$output[] = call_user_func(array($this, $option_type_method), $option);
				$output[] = '</div>';
			}

		}

		$output[] = '</form>';

		echo implode('', $output);
	}


}
