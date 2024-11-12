<template>
  <div class="relative inline-block text-left">
    <button type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" @click="isOpen = !isOpen">
      {{ selectedOption.label }}
      <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
      </svg>
    </button>

    <div v-show="isOpen" class="origin-top-right absolute mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none">
      <div class="py-1">
        <a href="#" v-for="option in options" :key="option.value" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" @click="selectOption(option)">
          {{ option.label }}
        </a>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      isOpen: false,
      options: [
        { value: 'Admin', label: 'Admin' },
        { value: 'Human Resources', label: 'Human Resources' },
        { value: 'Payroll Office', label: 'Payroll Office' },
      ],
      selectedOption: { value: 'Select Role', label: 'Select Role' },
    };
  },
  methods: {
    selectOption(option) {
      this.selectedOption = option;
      this.isOpen = false;
    }
  }
};
</script>