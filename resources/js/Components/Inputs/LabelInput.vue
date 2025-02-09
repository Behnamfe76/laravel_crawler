<script setup>
defineProps({
    label: {required: false, type: String, default: "label"},
    type: {required: false, type: String, default: "text"},
    id: {required: true, type: String},
    name: {required: true, type: String},
    placeholder: {required: false, type: String, default: "placeholder"},
    error: {required: false, type: String},
})
const model = defineModel();
</script>

<template>
        <div class="w-full">
            <label :for="name" class="block text-sm/6 font-medium text-gray-900 dark:text-slate-200">{{ label }}</label>
            <div class="mt-2">
                <input v-model="model" :type="type" :name="name" :id="id" class="block w-full rounded-md bg-white dark:bg-slate-700 dark:caret-white px-3 py-1.5 text-base text-gray-900 dark:text-gray-200 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" :placeholder="placeholder"/>
            </div>
            <div class="text-red-600 text-sm">
                {{error}}
            </div>
        </div>

</template>
