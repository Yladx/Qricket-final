@props([
	'label' => '',
	'name' => '',
	'value' => 0,
])

<fieldset>
	<legend class="block text-sm text-gray-700 dark:text-gray-300 mb-1">{{ $label }}</legend>
	<div class="flex items-center gap-2">
		@for($i = 1; $i <= 5; $i++)
			<label class="inline-flex items-center gap-1 text-sm">
				<input type="radio" name="{{ $name }}" value="{{ $i }}" id="{{ $name }}_{{ $i }}" @checked((int)$value === $i) class="text-qricket-600 focus:ring-qricket-500">
				<span class="text-gray-600 dark:text-gray-400">{{ $i }}</span>
			</label>
		@endfor
	</div>
</fieldset>


