<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, router} from '@inertiajs/vue3';
import SeleniumDriversTable from "@/Components/Tables/SeleniumDriversTable.vue";
import {inject, reactive, ref, watch} from "vue";
import PrimaryButtonDark from "@/Components/Buttons/PrimaryButtonDark.vue";
import ButtonDanger from "@/Components/Buttons/ButtonDanger.vue";
import axios from "axios";
import LoadingSpin from "@/Components/Loadings/LoadingSpin.vue";

const props = defineProps(['drivers'])

const drivers = reactive(props.drivers);
const Swal = inject('$swal');
const loading = ref(false);

const showAlert = async () => {
    Swal.fire({
        title: "Are you sure?",
        text: "The drivers availability would be reset based on the selenium driver's status.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, reset it!"
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                loading.value = true;
                await axios.post(route('dashboard.reset-drivers'));

                Swal.fire({
                    title: "Reset Done!",
                    text: "The Drivers' status has been reset successfully.",
                    icon: "success"
                });

            } catch (error) {
                console.log(error)
            } finally {
                loading.value = false;
            }

        }
    });
}

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
                        <div class="mb-4 flex gap-2">
                            <primary-button-dark @click="router.get(route('dashboard.define-drivers'))">
                                Define New Driver
                            </primary-button-dark>
                            <button-danger @click="showAlert">
                                <div class="flex gap-2">
                                    <span>Reset Drivers</span>
                                <loading-spin v-if="loading" height="1rem" width="1rem" fill="#fff" />
                                </div>
                            </button-danger>
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
