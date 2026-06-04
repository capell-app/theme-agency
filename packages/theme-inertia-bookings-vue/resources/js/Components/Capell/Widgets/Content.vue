<script setup>
defineProps({
    widget: {
        type: Object,
        required: true,
    },
})
</script>

<template>
    <article class="capell-bookings-widget capell-bookings-widget-content">
        <p
            v-if="widget.data?.eyebrow"
            class="capell-bookings-widget-eyebrow"
        >
            {{ widget.data.eyebrow }}
        </p>
        <h2 v-if="widget.data?.title">{{ widget.data.title }}</h2>
        <div
            v-if="widget.data?.content"
            v-html="widget.data.content"
        />
    </article>
</template>
