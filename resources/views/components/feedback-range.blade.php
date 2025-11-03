@props([
	'label' => '',
	'name' => '',
	'value' => 0,
])

<label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">{{ $label }}</label>
<div class="flex items-center gap-2">
	@for($i = 1; $i <= 5; $i++)
		<label class="inline-flex items-center gap-1 text-sm">
			<input type="radio" name="{{ $name }}" value="{{ $i }}" @checked((int)$value === $i) class="text-qricket-600 focus:ring-qricket-500">
			<span class="text-gray-600 dark:text-gray-400">{{ $i }}</span>
		</label>
	@endfor
</div>


