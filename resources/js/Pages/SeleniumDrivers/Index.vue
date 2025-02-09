<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, router} from '@inertiajs/vue3';
import SeleniumDriversTable from "@/Components/Tables/SeleniumDriversTable.vue";
import {reactive} from "vue";
import PrimaryButtonDark from "@/Components/Buttons/PrimaryButtonDark.vue";
const props = defineProps(['drivers'])

const drivers = reactive(props.drivers);
</script>

<template>
    <Head title="Selenium Drivers" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Selenium Drivers
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800"
                >

                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="mb-4">
                            <primary-button-dark @click="router.get(route('dashboard.define-drivers'))">
                                Define New Driver
                            </primary-button-dark>
                        </div>
                        <selenium-drivers-table v-if="drivers.length" :drivers="drivers" />
                        <div class="flex justify-center my-8" v-else>
                            no drivers found
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
