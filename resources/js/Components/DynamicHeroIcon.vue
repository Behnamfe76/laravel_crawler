<script setup>
import * as HeroIcons from "@heroicons/vue/20/solid";
import { ref } from "vue";
const DynamicHeroIcon = ref(null);
const props = defineProps({
    icon: {
        type: String,
    },
    color: {
        type: String,
        default: "#312783",
    },
    class: {
        type: String,
        default:
            "px-14 text-sm font-light rounded-md py-3 flex flex-row gap-1 items-center justify-center w-full",
    },
});

for (let item in HeroIcons) {
    if (item === props.icon) {
        DynamicHeroIcon.value = HeroIcons[item];
    }
}
</script>

<template>
    <DynamicHeroIcon class="h-4 w-4" :style="{ color: `${props.color}` }" aria-hidden="true" />
</template>
