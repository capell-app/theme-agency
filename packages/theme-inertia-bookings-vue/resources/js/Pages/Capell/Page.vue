<script setup>
import ContentWidget from '../../Components/Capell/Widgets/Content.vue'
import ImageWidget from '../../Components/Capell/Widgets/Image.vue'
import TitleWidget from '../../Components/Capell/Widgets/Title.vue'

defineProps({
    page: {
        type: Object,
        required: true,
    },
})

const widgets = {
    'Capell/Widgets/Content': ContentWidget,
    'Capell/Widgets/Image': ImageWidget,
    'Capell/Widgets/Title': TitleWidget,
}
</script>

<template>
    <main class="capell-inertia-bookings-page">
        <section class="capell-inertia-bookings-hero">
            <p v-if="page.meta?.eyebrow">
                {{ page.meta.eyebrow }}
            </p>
            <h1>{{ page.title }}</h1>
            <div
                v-if="typeof page.content === 'string'"
                v-html="page.content"
            />
            <a href="/bookings">Request appointment</a>
        </section>

        <section
            v-for="container in page.layout?.containers ?? []"
            :key="container.key"
            class="capell-inertia-bookings-container"
            :data-container="container.key"
        >
            <component
                :is="widgets[widget.component] ?? ContentWidget"
                v-for="widget in container.widgets"
                :key="`${widget.key}:${widget.occurrence}`"
                :widget="widget"
            />
        </section>
    </main>
</template>
