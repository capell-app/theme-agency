<script setup>
defineProps({
    widget: {
        type: Object,
        required: true,
    },
})
</script>

<template>
    <figure class="capell-bookings-widget capell-bookings-widget-image">
        <img
            v-if="widget.data?.image?.url"
            :alt="widget.data.image.alt ?? ''"
            loading="lazy"
            :src="widget.data.image.url"
        />
        <figcaption v-if="widget.data?.caption">
            {{ widget.data.caption }}
        </figcaption>
    </figure>
</template>
