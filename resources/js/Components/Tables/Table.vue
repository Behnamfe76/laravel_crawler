<script setup>
defineProps({
    tableTitle: { required: false, type: String, default: "title" },
    tableDescription: { required: false, type: String, default: "description" },
    tableData: { required: true, type: Object },
});

</script>
<template>
    <div class="bg-gray-900">
        <div class="mx-auto max-w-7xl">
            <div class="bg-gray-900 py-10">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="sm:flex sm:items-center">
                        <div class="sm:flex-auto">
                            <h1 class="text-base font-semibold text-white">{{ tableTitle }}</h1>
                            <p class="mt-2 text-sm text-gray-300">
                                {{ tableDescription }}
                            </p>
                        </div>

                    </div>
                    <div class="mt-8 flow-root" v-if="tableData.tbody.length">
                        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                <table class="min-w-full divide-y divide-gray-700">
                                    <thead>
                                        <tr>
                                            <!-- table heads -->
                                            <th v-for="(item, index) in tableData
                                                .thead" :key="index" scope="col"
                                                class="py-3.5 text-left text-sm font-semibold text-gray-900 sm:pl-0 dark:text-slate-200">
                                                {{ item.label }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-800">
                                        <tr v-for="(tbody, index) in tableData.tbody" :key="index">
                                            <td v-for="(item, index) in tableData.thead" :key="index" :style="{
                                                    width: `${(100 - 15) /
                                                        tableData.thead.length
                                                        }%`,
                                                }"
                                                class="whitespace-nowrap dark:text-slate-200 text-left w-full py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0 font-sans-fa">
                                                <div>
                                                    {{ tbody[item.title] }}
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex justify-center">
                         No data found
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
