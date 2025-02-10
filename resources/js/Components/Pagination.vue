<script setup>
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/20/solid'

const props = defineProps({
    links: Array
})

function isNext(link) {
    return link.label && (link.label.includes('Next') || link.label.includes('next') || link.label.includes('بعدی'))
}

function isPrevious(link) {
    return link.label && (link.label.includes('Previous') || link.label.includes('previous') || link.label.includes('قبلی'))
}

</script>

<template>
    <nav v-if="links.length > 3"
        class="flex items-center justify-between border-t border-gray-200 px-4 sm:px-0 font-sans-fa mt-8">
        <template v-for="(link, p) in links" :key="'pre-' + p">
            <div v-if="isPrevious(link)" class="-mt-px flex w-0 flex-1">
                <div v-if="link.url === null"
                    class="inline-flex items-center border-t-2 border-transparent pl-4 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">
                    <ChevronRightIcon class="ml-3 h-5 w-5 text-gray-400" aria-hidden="true" />
                    قبلی
                </div>
                <Link preserve-scroll v-else class="inline-flex items-center border-t-2 pl-4 pt-4 text-sm font-medium"
                    :class="link.active ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    :href="link.url">
                <ChevronRightIcon class="ml-3 h-5 w-5 text-gray-400" aria-hidden="true" />
                قبلی
                </Link>
            </div>
        </template>
        <div class="hidden md:-mt-px md:flex">
            <template v-for="(link, p) in links" :key="'nex-' + p">
                <template v-if="!isNext(link) && !isPrevious(link)">
                    <div v-if="link.url === null"
                        class="inline-flex items-center border-t-2 border-transparent px-4 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
                        v-html="link.label" />
                    <Link preserve-scroll v-else
                        class="inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium"
                        :class="link.active ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        :href="link.url" v-html="link.label" />
                </template>
            </template>

        </div>
        <template v-for="(link, p) in links" :key="'nex-' + p">
            <div v-if="isNext(link)" class="-mt-px flex w-0 flex-1 justify-end">

                <div v-if="link.url === null"
                    class="inline-flex items-center border-t-2 border-transparent pr-4 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">
                    بعدی
                    <ChevronLeftIcon class="mr-3 h-5 w-5 text-gray-400" aria-hidden="true" />
                </div>
                <Link preserve-scroll v-else class="inline-flex items-center border-t-2 pr-4 pt-4 text-sm font-medium"
                    :class="link.active ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    :href="link.url">
                بعدی
                <ChevronLeftIcon class="mr-3 h-5 w-5 text-gray-400" aria-hidden="true" />
                </Link>
            </div>
        </template>
    </nav>
</template>

<style scoped></style>
