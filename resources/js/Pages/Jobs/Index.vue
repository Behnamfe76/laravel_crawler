<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import SeleniumDriversTable from "@/Components/Tables/SeleniumDriversTable.vue";
import { inject, onBeforeMount, reactive, ref, watch } from "vue";
import PrimaryButtonDark from "@/Components/Buttons/PrimaryButtonDark.vue";
import ButtonDanger from "@/Components/Buttons/ButtonDanger.vue";
import axios from "axios";
import LoadingSpin from "@/Components/Loadings/LoadingSpin.vue";
import Dialog from 'primevue/dialog';
import Table from "@/Components/Tables/Table.vue";
import OptionalTable from "@/Components/Tables/OptionalTable.vue";
import Pagination from "@/Components/Pagination.vue";
import { ChevronDownIcon } from '@heroicons/vue/16/solid';
const visible = ref(false);
import { split } from 'postcss/lib/list';
import { data } from 'autoprefixer';
const props = defineProps(['jobs', 'failedJobs']);
const exceptionData = reactive({
    uuid: null,
    data: null,
});

const jobsTableData = reactive({
    thead: [
        { title: "id", label: "Id" },
        { title: "queue", label: "Queue" },
        { title: "attempts", label: "Attempts" },
        { title: "job", label: "Job" },
        { title: "merchant", label: "Merchant" },
        { title: "merchant_url", label: "Merchant Url" },
        { title: "generated_slug", label: "Generated Slug" },
    ],
    tbody: [],
});
const failedJobsTableData = reactive({
    thead: [
        { title: "id", label: "Id" },
        { title: "queue", label: "Queue" },
        { title: "job", label: "Job" },
        { title: "merchant", label: "Merchant" },
        { title: "merchant_url", label: "Merchant Url" },
        { title: "generated_slug", label: "Generated Slug" },
    ],
    tbody: [],
});
const tabs = reactive([
    { name: 'Jobs', href: 'jobs', current: true },
    { name: 'Failed Jobs', href: 'failed-jobs', current: false },
]);
const changeTab = (tab) => {
    tabs.forEach(item => {
        item.current = item.href === tab.href;
    })
}
const prepareJobsTableData = () => {
    jobsTableData.tbody = [];
    props.jobs.data.forEach((job) => {
        jobsTableData.tbody.push({
            id: job.id,
            queue: job.queue,
            attempts: job.attempts,
            job: job.job,
            merchant: job.data.merchant_id,
            merchant_url: job.data.merchant_url,
            generated_slug: job.data.generated_slug,
        });
    });
}
const prepareFailedJobsTableData = () => {
    failedJobsTableData.tbody = [];
    props.failedJobs.data.forEach((job) => {
        failedJobsTableData.tbody.push({
            id: job.id,
            queue: job.queue,
            job: job.job,
            merchant: job.data.merchant_id,
            merchant_url: job.data.merchant_url,
            generated_slug: job.data.generated_slug,
            uuid: job.uuid,
            exception: job.exception,

            operations: [
                {
                    color: "#4085DC",
                    icon: "PencilIcon",
                    title: "more",
                    name: "more",
                },
                {
                    color: "#4085DC",
                    icon: "ArrowPathIcon",
                    title: "retry",
                    name: "retry",
                },
            ],
        });
    });
}
function handleTableOperation(event) {
    if (event[0] === "more") {
        exceptionData.uuid = event[1].uuid;
        exceptionData.data = event[1].exception;
        visible.value = true;
    }

    if (event[0] === "retry") {
        const jobId = event[1].id;
        router.post(route('dashboard.jobs.retry', { job: jobId }), {
            onSuccess: () => {
                router.reload();
            }
        });
    }
}

onBeforeMount(() => {
    prepareJobsTableData();
    prepareFailedJobsTableData();
});

</script>

<template>

    <Head title="Selenium Drivers" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Selenium Drivers
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">

                    <div class="p-6 text-gray-900 dark:text-gray-100">

                        <!-- tab navigation -->
                        <div>
                            <div class="grid grid-cols-1 sm:hidden">
                                <!-- Use an "onChange" listener to redirect the user to the selected tab URL. -->
                                <select aria-label="Select a tab"
                                    class="col-start-1 row-start-1 w-full appearance-none rounded-md bg-white py-2 pr-8 pl-3 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600">
                                    <option v-for="tab in tabs" :key="tab.name" :selected="tab.current">
                                        {{ tab.name }}
                                    </option>
                                </select>
                                <ChevronDownIcon
                                    class="pointer-events-none col-start-1 row-start-1 mr-2 size-5 self-center justify-self-end fill-gray-500"
                                    aria-hidden="true" />
                            </div>
                            <div class="hidden sm:block">
                                <div class="border-b border-gray-200">
                                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                        <span v-for="tab in tabs" :key="tab.name" @click="changeTab(tab)"
                                            :class="[tab.current ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700', 'border-b-2 px-1 py-4 text-sm font-medium whitespace-nowrap cursor-pointer']"
                                            :aria-current="tab.current ? 'page' : undefined">{{ tab.name }}</span>
                                    </nav>
                                </div>
                            </div>
                        </div>

                        <!-- tab data -->
                        <div>
                            <template v-if="tabs[0].current">
                                <!-- jobs table -->
                                <LoadingSpin v-if="loading" />
                                <div v-else>
                                    <div class="py-4 text-gray-900 dark:text-gray-100">
                                        <Table tableTitle="Jobs" tableDescription="Here pending jobs are listed"
                                            :tableData="jobsTableData" />
                                        <pagination :links="props.jobs.links" />
                                    </div>
                                </div>
                            </template>
                            <template v-else-if="tabs[1].current">
                                <!-- failed jobs table -->
                                <LoadingSpin v-if="loading" />
                                <div v-else>
                                    <div class="py-4 text-gray-900 dark:text-gray-100">
                                        <OptionalTable tableTitle="Jobs"
                                            tableDescription="Here pending failed jobs are listed"
                                            :tableData="failedJobsTableData" @table-operations="handleTableOperation" />
                                        <pagination :links="props.failedJobs.links" />
                                    </div>

                                    <Dialog v-model:visible="visible" modal header="Header" :style="{ width: '50rem' }"
                                        :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
                                        <div>
                                            <h2>
                                                <div class="text-lg">UUID : </div>
                                                {{ exceptionData.uuid }}
                                            </h2>
                                            <br />
                                            <h2>
                                                <div class="text-lg">Exception : </div>
                                            </h2>
                                            <p class="mb-8">
                                               {{ exceptionData.data }}
                                            </p>

                                        </div>
                                    </Dialog>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
