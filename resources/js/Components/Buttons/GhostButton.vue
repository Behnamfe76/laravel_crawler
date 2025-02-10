<script setup>
const props = defineProps({
    type: {
        type: String,
        default: "button",
    },
    color: {
        type: String,
        default: "#312783",
    },
    class: {
        type: String,
        default:
            "px-4 text-xs disabled:pointer-events-none  rounded-md py-[6px] flex flex-row gap-1 items-center justify-center w-full",
    },
    disabled: { required: false, type: Boolean, default: false }
});
</script>

<template>
    <button :disabled="disabled" :type="type" :style="{
        color: disabled ? `${props.color}80` : `${props.color}`,
        backgroundColor: disabled ? `${props.color}10` : `${props.color}20`,
    }" :class="props.class">
        <slot />
    </button>
</template>
