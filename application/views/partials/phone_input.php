<?php defined('BASEPATH') or exit('No direct script access allowed');

$escape = function ($value) {
	return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$flag_emoji = function ($flag_class) {
	$country = strtolower(str_replace('cf-', '', (string) $flag_class));
	if (!preg_match('/^[a-z]{2}$/', $country)) {
		return '';
	}

	$first = 0x1F1E6 + (ord($country[0]) - ord('a'));
	$second = 0x1F1E6 + (ord($country[1]) - ord('a'));

	return html_entity_decode('&#' . $first . ';&#' . $second . ';', ENT_QUOTES, 'UTF-8');
};

$field_name = isset($field_name) ? (string) $field_name : 'phone';
$country_code_name = isset($country_code_name) ? (string) $country_code_name : 'country_code';
$value = isset($value) ? (string) $value : '';
$default_country_code = isset($default_country_code) ? (string) $default_country_code : '+44';
$selected_country = isset($selected_country) ? (string) $selected_country : '';
$required = !empty($required);
$readonly = !empty($readonly);
$disabled = !empty($disabled);

$split_phone = split_phone_number($value, $default_country_code);
$selected_country_code = isset($country_code) ? phone_country_code_normalize($country_code) : $split_phone['country_code'];
$local_number = isset($local_number) ? phone_number_digits($local_number) : $split_phone['local_number'];

$wrapper_class = isset($wrapper_class) ? (string) $wrapper_class : 'mb-3';
$group_class = isset($group_class) ? (string) $group_class : 'smb-phone-input input-group';
$select_class = isset($select_class) ? (string) $select_class : 'form-control smb-phone-input__country';
$input_class = isset($input_class) ? (string) $input_class : 'form-control smb-phone-input__number';
$label = isset($label) ? (string) $label : 'Phone Number';
$placeholder = isset($placeholder) ? (string) $placeholder : '7911123456';
$help_text = isset($help_text) ? (string) $help_text : '';
$error = isset($error) ? (string) $error : '';
$autocomplete = isset($autocomplete) ? (string) $autocomplete : 'tel-national';

$base_id = isset($id) ? (string) $id : preg_replace('/[^A-Za-z0-9_-]+/', '_', $field_name);
$country_code_id = isset($country_code_id) ? (string) $country_code_id : $base_id . '_country_code';
$input_id = isset($input_id) ? (string) $input_id : $base_id . '_number';

$countries = isset($countries) && is_array($countries) ? $countries : phone_country_options();
if ($selected_country !== '' && isset($countries[$selected_country])) {
	$selected_country_code = phone_country_code_normalize($countries[$selected_country]['code']);
}

?>

<div class="<?php echo $escape($wrapper_class); ?>">
	<?php if ($label !== ''): ?>
		<label class="form-label" for="<?php echo $escape($input_id); ?>">
			<?php echo $escape($label); ?><?php if ($required): ?> <span class="text-danger">*</span><?php endif; ?>
		</label>
	<?php endif; ?>

	<div class="<?php echo $escape($group_class); ?>" data-smb-phone-input>
		<select
			id="<?php echo $escape($country_code_id); ?>"
			name="<?php echo $escape($country_code_name); ?>"
			class="<?php echo $escape($select_class); ?>"
			data-smb-phone-country
			<?php echo $required ? 'required' : ''; ?>
			<?php echo ($readonly || $disabled) ? 'disabled' : ''; ?>>
			<?php foreach ($countries as $country_name => $country_data): ?>
				<?php
				$option_code = phone_country_code_normalize($country_data['code'] ?? '');
				$option_flag = (string) ($country_data['flag'] ?? '');
				$is_selected = $option_code === $selected_country_code;
				?>
				<option
					value="<?php echo $escape($option_code); ?>"
					data-country="<?php echo $escape($country_name); ?>"
					data-flag="cf-16 <?php echo $escape($option_flag); ?>"
					<?php echo $is_selected ? 'selected' : ''; ?>>
					<?php echo $escape(trim($flag_emoji($option_flag) . ' ' . $option_code)); ?>
				</option>
			<?php endforeach; ?>
		</select>

		<?php if ($readonly && !$disabled): ?>
			<input type="hidden" name="<?php echo $escape($country_code_name); ?>" value="<?php echo $escape($selected_country_code); ?>">
		<?php endif; ?>

		<input
			id="<?php echo $escape($input_id); ?>"
			name="<?php echo $escape($field_name); ?>"
			type="tel"
			class="<?php echo $escape($input_class); ?>"
			value="<?php echo $escape($local_number); ?>"
			placeholder="<?php echo $escape($placeholder); ?>"
			inputmode="tel"
			autocomplete="<?php echo $escape($autocomplete); ?>"
			data-smb-phone-number
			<?php echo $required ? 'required' : ''; ?>
			<?php echo $readonly ? 'readonly' : ''; ?>
			<?php echo $disabled ? 'disabled' : ''; ?>>
	</div>

	<?php if ($help_text !== ''): ?>
		<small class="form-text text-muted"><?php echo $escape($help_text); ?></small>
	<?php endif; ?>

	<?php if ($error !== ''): ?>
		<div class="form-error text-danger"><?php echo $error; ?></div>
	<?php endif; ?>
</div>
