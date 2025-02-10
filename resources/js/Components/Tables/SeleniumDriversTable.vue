<script setup>
import {onBeforeMount, reactive} from "vue";
import {ArrowPathIcon, PencilIcon} from "@heroicons/vue/24/solid/index.js";
import LoadingSpin from "@/Components/Loadings/LoadingSpin.vue";
import axios from "axios";
import GreenBadge from "@/Components/Badges/GreenBadge.vue";
import RedBadge from "@/Components/Badges/RedBadge.vue";
import { router } from "@inertiajs/vue3";
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
    isAlive: false,
    isWorking: false,
});
const drivers = reactive([]);

const checkWebDriverService = async () => {
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
const checkWebDriverWorking = async () => {
    loadings.isWorking = true;
    for (const item of drivers) {
        try {
            const response = await axios.post(route('dashboard.check-driver-working'), {
                driverPort: item.port,
                driverHost: item.host
            });
            if (response.status === 200) {
                item.isWorking = true;
            }
        } catch (error) {
            console.error(error.response.data);
            item.isWorking = false;
        }
    }
    loadings.isWorking = false;
}
const checkWebDriverIsAlive = async () => {
    loadings.isAlive = true;
    for (const item of drivers) {
        try {
            const response = await axios.post(route('dashboard.check-driver-alive'), {
                driverPort: item.port,
                driverHost: item.host
            });
            if (response.status === 200) {
                item.isAlive = true;
            }
        } catch (error) {
            console.error(error.response.data);
            item.isAlive = false;
        }
    }
    loadings.isAlive = false;
}

onBeforeMount(() => {
    props.drivers.forEach(item => {
        drivers.push(
            {
                id: item.id,
                name: item.name,
                isWorking: item.is_working,
                isAlive: item.is_alive,
                working: item.working_subject,
                status: 'must check',
                duration: item.duration,
                lastUsed: item.last_used,
                driverUrl: item.driver_url,
                port: item.port,
                host: item.host,
            },
        );
    })
})

</script>
<template>
    <div class="bg-gray-900 py-10">
        <h2 class="px-4 text-base/7 font-semibold text-white sm:px-6 lg:px-8">Latest activity</h2>
        <table class="mt-6 w-full text-left whitespace-nowrap">

            <thead class="border-b border-white/10 text-sm/6 text-white">
                <tr>
                    <!-- name -->
                    <th scope="col" class="py-2 pr-8 pl-4 font-semibold sm:pl-6 lg:pl-8">Driver</th>
                    <!-- Working -->
                    <th scope="col" class="hidden py-2 pr-8 pl-0 font-semibold sm:table-cell">
                        <div class="flex items-center gap-2">
                            <span>Working</span>
                            <span v-if="!loadings.isWorking">
                                <ArrowPathIcon @click="checkWebDriverWorking"
                                    class="h-4 w-4 cursor-pointer hover:text-blue-600" />
                            </span>
                            <loading-spin v-else height="1rem" width="1rem" fill="#818CF8" />
                        </div>
                    </th>
                    <!-- Alive -->
                    <th scope="col" class="hidden py-2 pr-8 pl-0 font-semibold sm:table-cell">
                        <div class="flex items-center gap-2">
                            <span>Alive</span>
                            <span v-if="!loadings.isAlive">
                                <ArrowPathIcon @click="checkWebDriverIsAlive"
                                    class="h-4 w-4 cursor-pointer hover:text-blue-600" />
                            </span>
                            <loading-spin v-else height="1rem" width="1rem" fill="#818CF8" />
                        </div>
                    </th>
                    <!-- status -->
                    <th scope="col" class="py-2 pr-4 pl-0 text-right font-semibold sm:pr-8 sm:text-left lg:pr-20">
                        <div class="flex items-center gap-2">
                            <span>Status</span>
                            <span v-if="!loadings.status">
                                <ArrowPathIcon @click="checkWebDriverService"
                                    class="h-4 w-4 cursor-pointer hover:text-blue-600" />
                            </span>
                            <loading-spin v-else height="1rem" width="1rem" fill="#818CF8" />
                        </div>
                    </th>
                    <!-- Duration -->
                    <th scope="col" class="hidden py-2 pr-8 pl-0 font-semibold md:table-cell lg:pr-20">Duration</th>
                    <!-- User at -->
                    <th scope="col" class="hidden py-2 pr-4 pl-0 font-semibold sm:table-cell sm:pr-6 lg:pr-8">
                        Used at
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                <tr v-for="item in drivers" :key="item.driverUrl">
                    <!-- name -->
                    <td class="py-4 pr-8 pl-4 sm:pl-6 lg:pl-8">
                        <div class="flex items-center gap-x-4">
                            <div class="truncate text-sm/6 font-medium text-white">{{ item.name }}</div>
                        </div>
                    </td>
                    <!-- Working -->
                    <td class="hidden py-4 pr-4 pl-0 sm:table-cell sm:pr-8">
                        <div class="flex gap-x-3">
                            <div class="font-mono text-sm/6 text-gray-400">
                                <green-badge v-if="item.isWorking">up</green-badge>
                                <red-badge v-else>down</red-badge>
                            </div>
                            <div v-if="item.isWorking"
                                class="rounded-md bg-gray-700/40 px-2 py-1 text-xs font-medium text-gray-400 ring-1 ring-white/10 ring-inset">
                                {{ item.working }}
                            </div>
                        </div>
                    </td>
                    <!-- Alive -->
                    <td class="hidden py-4 pr-4 pl-0 sm:table-cell sm:pr-8">
                        <div class="flex gap-x-3">
                            <div class="font-mono text-sm/6 text-gray-400">
                                <green-badge v-if="item.isAlive">up</green-badge>
                                <red-badge v-else>down</red-badge>
                            </div>
                        </div>
                    </td>
                    <!-- status -->
                    <td class="py-4 pr-4 pl-0 text-sm/6 sm:pr-8 lg:pr-20">
                        <div class="flex items-center justify-end gap-x-2 sm:justify-start">
                            <time class="text-gray-400 sm:hidden" :datetime="item.dateTime">{{ item.date }}</time>
                            <div :class="[statuses[item.status], 'flex-none rounded-full p-1']">
                                <div class="size-1.5 rounded-full bg-current" />
                            </div>
                            <div class="hidden text-white sm:block">{{ item.status }}</div>
                        </div>
                    </td>
                    <!-- Duration -->
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
                    <!-- User at -->
                    <td class="hidden py-4 pr-4 pl-0 text-sm/6 text-gray-400 sm:table-cell sm:pr-6 lg:pr-8">
                        <time v-if="item.lastUsed" :datetime="item.lastUsed">{{ item.lastUsed }}</time>
                        <span v-else>
                            not used yet
                        </span>
                    </td>
                    <!-- Actions -->
                    <td class="hidden py-4 pr-4 pl-0 text-sm/6 text-gray-400 sm:table-cell sm:pr-6 lg:pr-8">
                        <PencilIcon @click="router.get(route('dashboard.selenium-drivers.edit', { selenium_driver: item.id}))" class="h-4 w-4 cursor-pointer  hover:text-indigo-500/50 transition-all" />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

