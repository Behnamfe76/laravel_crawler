<script setup>

const props = defineProps({
    label: { required: true, type: String },
    color: { required: true, type: String },
});

function setBadgeColor() {
    let color = '';
    switch (props.color) {
        case 'green':
            color = 'bg-green-50 text-green-700 ring-green-600/20';
            break;

        case 'red':
            color = 'bg-red-50 text-red-700 ring-red-600/20';
            break;

        case 'yellow':
            color = 'bg-yellow-50 text-yellow-700 ring-yellow-600/20';
            break;

        case 'blue':
            color = 'bg-blue-50 text-blue-700 ring-blue-600/20';
            break;

        default:
            color = 'bg-blue-50 text-blue-700 ring-blue-600/20';

    }
    return color;
}
</script>

<template>
    <span :class="setBadgeColor()"
        class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset ">
        {{ props.label }}
    </span>
</template>
