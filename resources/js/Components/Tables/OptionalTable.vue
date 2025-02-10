<script setup>
import GhostButton from "../Buttons/GhostButton.vue";
import DynamicHeroIcon from "../DynamicHeroIcon.vue";
import LoadingSpin from "../Loadings/LoadingSpin.vue";
import Badge from "../Badges/Badge.vue";
const emit = defineEmits("tableOperations");
const props = defineProps({
    tableData: { required: true, type: Object },
    loading: {
        required: false, type: Object, default: {
            value: false,
            id: 0
        }
    }
});

function handleEmit(event) {
    emit("tableOperations", event);
}
</script>
<template>
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="mt-8 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead>
                            <tr>
                                <!-- table heads -->
                                <th v-for="(item, index) in props.tableData
                                    .thead" :key="index" scope="col"
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-slate-200 sm:pl-0">
                                    {{ item.label }}
                                </th>

                                <!-- table head optins -->
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0">
                                    <span class="sr-only">ویرایش</span>
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="(tbody, index) in props.tableData.tbody" :key="index">
                                <td v-for="(item, index) in props.tableData
                                    .thead" :key="index" :style="{
                                        width: `${(100 - 15) /
                                            props.tableData.thead.length
                                            }%`,
                                    }"
                                    class="whitespace-nowrap w-full py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-slate-200 sm:pl-0 font-sans-fa">
                                    <div class="text-left"
                                        v-if="!tbody[item.title].hasBadge">
                                        {{ tbody[item.title] }}
                                    </div>
                                </td>

                                <td
                                    class="relative flex gap-2 whitespace-nowrap py-4 pl-3 pr-4 text-left justify-end text-sm font-medium sm:pr-0">
                                    <GhostButton :disabled="loading.value" v-for="(
                                            button, index
                                        ) in tbody.operations" :key="index" :color="button.color" @click="
                                            handleEmit([button.name, tbody])
                                            ">
                                        <div>
                                            <DynamicHeroIcon v-if="!loading.value"
                                                :color="loading.value ? `${button.color}80` : button.color"
                                                :icon="button.icon" />
                                            <loading-spin v-if="loading.value && button.id === loading.id"
                                                height="0.5rem" width="0.5rem" :fill="button.color" />

                                        </div>
                                        <div>
                                            {{ button.title }}
                                        </div>
                                    </GhostButton>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
