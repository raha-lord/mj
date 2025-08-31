<template>
  <div>
    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    <select
      :id="id"
      :value="modelValue"
      @input="$emit('update:modelValue', Array.from($event.target.selectedOptions, option => option.value))"
      :class="selectClasses"
      :required="required"
      multiple
    >
      <option 
        v-for="option in options" 
        :key="getOptionValue(option)" 
        :value="getOptionValue(option)"
      >
        <slot name="option" :option="option">
          {{ getOptionLabel(option) }}
        </slot>
      </option>
    </select>
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  id: String,
  label: String,
  modelValue: {
    type: Array,
    default: () => []
  },
  options: {
    type: Array,
    required: true
  },
  optionValue: {
    type: String,
    default: 'id'
  },
  optionLabel: {
    type: String,
    default: 'name'
  },
  required: Boolean,
  error: String
})

defineEmits(['update:modelValue'])

const selectClasses = computed(() => {
  const base = 'mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500'
  return props.error ? `${base} border-red-500` : base
})

const getOptionValue = (option) => {
  return option[props.optionValue]
}

const getOptionLabel = (option) => {
  return option[props.optionLabel]
}
</script>