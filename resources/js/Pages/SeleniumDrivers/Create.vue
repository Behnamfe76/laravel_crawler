<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, useForm} from '@inertiajs/vue3';
import LabelInput from "@/Components/Inputs/LabelInput.vue";
import PrimaryButtonDark from "@/Components/Buttons/PrimaryButtonDark.vue";

const form = useForm({
    driverName: null,
    host: null,
    port: null,
});
const submitForm = () => {
    form.post(route('dashboard.store-drivers'), {
        onError: (error) => {
            console.log(error)
        }
    });
}

</script>

<template>
    <Head title="Selenium Drivers"/>

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Define Selenium Drivers
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800"
                >

                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <primary-button-dark @click="submitForm">Create</primary-button-dark>

                        <div class="flex gap-4 my-4">
                            <label-input v-model="form.driverName" :error="$page.props.errors.driverName" id="name" name="name" label="name"
                                         placeholder="selenium"
                            />

                        </div>
                        <div class="flex gap-4">
                            <label-input v-model="form.host" :error="$page.props.errors.host" id="host" name="host" label="host"
                                         placeholder="127.0.0.1"/>
                            <label-input v-model="form.port" :error="$page.props.errors.host" id="port" name="port" label="port" placeholder="4444"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
