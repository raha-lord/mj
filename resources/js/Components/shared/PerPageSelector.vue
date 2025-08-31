<template>
  <div class="flex items-center space-x-2">
    <label 
      :for="id" 
      class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap"
    >
      {{ label }}
    </label>
    <FormSelect
      :id="id"
      :model-value="modelValue"
      :options="options"
      option-value="value"
      option-label="label"
      :placeholder="placeholder"
      class="w-20"
      @update:model-value="$emit('update:modelValue', $event)"
    />
  </div>
</template>

<script setup>
import FormSelect from './FormSelect.vue'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    required: true
  },
  id: {
    type: String,
    default: 'per-page-select'
  },
  label: {
    type: String,
    default: 'На странице:'
  },
  placeholder: {
    type: String,
    default: ''
  },
  options: {
    type: Array,
    default: () => [
      { value: 10, label: '10' },
      { value: 25, label: '25' },
      { value: 50, label: '50' },
      { value: 100, label: '100' }
    ]
  }
})

defineEmits(['update:modelValue'])
</script>