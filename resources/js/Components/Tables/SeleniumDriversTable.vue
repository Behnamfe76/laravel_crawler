<script setup>
import {onBeforeMount, reactive} from "vue";
import {ArrowPathIcon} from "@heroicons/vue/24/solid/index.js";
import LoadingSpin from "@/Components/Loadings/LoadingSpin.vue";
import axios from "axios";

const props = defineProps(['drivers'])
const statuses = {active: 'text-green-400 bg-green-400/10', inactive: 'text-rose-400 bg-rose-400/10'}
const activityItems = [
    {
        user: {
            name: 'Michael Foster',
            imageUrl:
                'https://images.unsplash.com/photo-1519244703995-f4e0f30006d5?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
        },
        commit: '2d89f0c8',
        branch: 'main',
        status: 'Completed',
        duration: '25s',
        date: '45 minutes ago',
        dateTime: '2023-01-23T11:00',
    },
    {
        user: {
            name: 'Lindsay Walton',
            imageUrl:
                'https://images.unsplash.com/photo-1517841905240-472988babdf9?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
        },
        commit: '249df660',
        branch: 'main',
        status: 'Completed',
        duration: '1m 32s',
        date: '3 hours ago',
        dateTime: '2023-01-23T09:00',
    },
    {
        user: {
            name: 'Courtney Henry',
            imageUrl:
                'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
        },
        commit: '11464223',
        branch: 'main',
        status: 'Error',
        duration: '1m 4s',
        date: '12 hours ago',
        dateTime: '2023-01-23T00:00',
    },
    {
        user: {
            name: 'Courtney Henry',
            imageUrl:
                'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
        },
        commit: 'dad28e95',
        branch: 'main',
        status: 'Completed',
        duration: '2m 15s',
        date: '2 days ago',
        dateTime: '2023-01-21T13:00',
    },
    {
        user: {
            name: 'Michael Foster',
            imageUrl:
                'https://images.unsplash.com/photo-1519244703995-f4e0f30006d5?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
        },
        commit: '624bc94c',
        branch: 'main',
        status: 'Completed',
        duration: '1m 12s',
        date: '5 days ago',
        dateTime: '2023-01-18T12:34',
    },
    {
        user: {
            name: 'Courtney Henry',
            imageUrl:
                'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
        },
        commit: 'e111f80e',
        branch: 'main',
        status: 'Completed',
        duration: '1m 56s',
        date: '1 week ago',
        dateTime: '2023-01-16T15:54',
    },
    {
        user: {
            name: 'Michael Foster',
            imageUrl:
                'https://images.unsplash.com/photo-1519244703995-f4e0f30006d5?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
        },
        commit: '5e136005',
        branch: 'main',
        status: 'Completed',
        duration: '3m 45s',
        date: '1 week ago',
        dateTime: '2023-01-16T11:31',
    },
    {
        user: {
            name: 'Whitney Francis',
            imageUrl:
                'https://images.unsplash.com/photo-1517365830460-955ce3ccd263?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
        },
        commit: '5c1fd07f',
        branch: 'main',
        status: 'Completed',
        duration: '37s',
        date: '2 weeks ago',
        dateTime: '2023-01-09T08:45',
    },
]
const loadings = reactive({
    status: false,
});
const drivers = reactive([]);


async function checkWebDriverService() {
    loadings.status = true;
    for (const item of drivers) {
        try {
            const response = await axios.post(route('dashboard.check-driver-status'), {
                driverUrl: item.driverUrl
            });
            if (response.status === 200) {
                item.status = 'active';
            }
        } catch (error) {
            console.error(error.response.data);
            item.status = 'inactive';
        }
    }
    loadings.status = false;
}


onBeforeMount(() => {
    props.drivers.forEach(item => {
        drivers.push(
            {
                name: item.name,
                isWorking: false,
                working: 'categories',
                status: 'must check',
                duration: item.duration,
                lastUsed: item.last_used,
                driverUrl: item.driver_url,
            },
        );
    })
})
</script>
<template>
    <div class="bg-gray-900 py-10">
        <h2 class="px-4 text-base/7 font-semibold text-white sm:px-6 lg:px-8">Latest activity</h2>
        <table class="mt-6 w-full text-left whitespace-nowrap">
            <colgroup>
                <col class="w-full sm:w-4/12"/>
                <col class="lg:w-4/12"/>
                <col class="lg:w-2/12"/>
                <col class="lg:w-1/12"/>
                <col class="lg:w-1/12"/>
            </colgroup>
            <thead class="border-b border-white/10 text-sm/6 text-white">
            <tr>
                <th scope="col" class="py-2 pr-8 pl-4 font-semibold sm:pl-6 lg:pl-8">Driver</th>
                <th scope="col" class="hidden py-2 pr-8 pl-0 font-semibold sm:table-cell">Is Working</th>
                <th scope="col" class="py-2 pr-4 pl-0 text-right font-semibold sm:pr-8 sm:text-left lg:pr-20">
                    <div class="flex items-center gap-2">
                        <span>Status</span>
                        <span v-if="!loadings.status">
                            <ArrowPathIcon @click="checkWebDriverService"
                                           class="h-4 w-4 cursor-pointer hover:text-blue-600"/>
                        </span>
                        <loading-spin v-else height="1rem" width="1rem" fill="#818CF8"/>
                    </div>
                </th>
                <th scope="col" class="hidden py-2 pr-8 pl-0 font-semibold md:table-cell lg:pr-20">Duration</th>
                <th scope="col" class="hidden py-2 pr-4 pl-0 text-right font-semibold sm:table-cell sm:pr-6 lg:pr-8">
                    Used at
                </th>
            </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
            <tr v-for="item in drivers" :key="item.driverUrl">
                <td class="py-4 pr-8 pl-4 sm:pl-6 lg:pl-8">
                    <div class="flex items-center gap-x-4">
                        <div class="truncate text-sm/6 font-medium text-white">{{ item.name }}</div>
                    </div>
                </td>
                <td class="hidden py-4 pr-4 pl-0 sm:table-cell sm:pr-8">
                    <div class="flex gap-x-3">
                        <div class="font-mono text-sm/6 text-gray-400">{{ item.isWorking }}</div>
                        <div
                            v-if="item.isWorking"
                            class="rounded-md bg-gray-700/40 px-2 py-1 text-xs font-medium text-gray-400 ring-1 ring-white/10 ring-inset">
                            {{ item.working }}
                        </div>
                    </div>
                </td>
                <td class="py-4 pr-4 pl-0 text-sm/6 sm:pr-8 lg:pr-20">
                    <div class="flex items-center justify-end gap-x-2 sm:justify-start">
                        <time class="text-gray-400 sm:hidden" :datetime="item.dateTime">{{ item.date }}</time>
                        <div :class="[statuses[item.status], 'flex-none rounded-full p-1']">
                            <div class="size-1.5 rounded-full bg-current"/>
                        </div>
                        <div class="hidden text-white sm:block">{{ item.status }}</div>
                    </div>
                </td>
                <td class="hidden py-4 pr-8 pl-0 text-sm/6 text-gray-400 md:table-cell lg:pr-20">
                    <span v-if="item.duration">
                    {{
                            item.duration
                        }}
                    </span>
                    <span v-else>
                        0s
                    </span>
                </td>
                <td class="hidden py-4 pr-4 pl-0 text-right text-sm/6 text-gray-400 sm:table-cell sm:pr-6 lg:pr-8">
                    <time v-if="item.lastUsed" :datetime="item.lastUsed">{{ item.lastUsed }}</time>
                    <span v-else>
                        not used yet
                    </span>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

