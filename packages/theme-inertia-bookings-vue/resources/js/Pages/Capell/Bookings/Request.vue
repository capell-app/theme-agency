<script setup>
import { Deferred, router, useForm } from '@inertiajs/vue3'

const props = defineProps({
    options: {
        type: Object,
        default: () => ({ services: [], staff: [], locations: [] }),
    },
    slots: {
        type: Array,
        default: () => [],
    },
    postUrl: {
        type: String,
        default: '/bookings',
    },
    timezone: {
        type: String,
        default: 'UTC',
    },
})

const form = useForm({
    service_id: '',
    staff_member_id: '',
    location_id: '',
    requested_starts_at: '',
    timezone: props.timezone,
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    notes: '',
})

function fieldId(field) {
    return `booking-${field.replaceAll('_', '-')}`
}

function errorId(field) {
    return `${fieldId(field)}-error`
}

function hasError(field) {
    return Boolean(form.errors[field])
}

function hasErrors() {
    return Object.keys(form.errors).length > 0
}

function describedBy(field, extraIds = []) {
    return (
        [...extraIds, hasError(field) ? errorId(field) : null]
            .filter(Boolean)
            .join(' ') || null
    )
}

function reloadSlots() {
    router.reload({
        only: ['slots'],
        data: {
            service_id: form.service_id,
            staff_member_id: form.staff_member_id,
            location_id: form.location_id,
            timezone: form.timezone,
        },
    })
}
</script>

<template>
    <main class="capell-inertia-booking-request">
        <form
            novalidate
            @submit.prevent="form.post(postUrl, { preserveScroll: true })"
        >
            <div
                v-if="hasErrors()"
                class="error"
                role="alert"
                tabindex="-1"
            >
                Please check the highlighted booking details and try again.
            </div>

            <label :for="fieldId('service_id')">Service</label>
            <div class="field">
                <select
                    :id="fieldId('service_id')"
                    v-model="form.service_id"
                    :aria-describedby="describedBy('service_id')"
                    :aria-invalid="hasError('service_id')"
                    @change="reloadSlots"
                >
                    <option value="">Choose a service</option>
                    <option
                        v-for="service in options.services"
                        :key="service.id"
                        :value="service.id"
                    >
                        {{ service.name }}
                    </option>
                </select>
                <p
                    v-if="form.errors.service_id"
                    :id="errorId('service_id')"
                    class="error"
                >
                    {{ form.errors.service_id }}
                </p>
            </div>

            <label :for="fieldId('staff_member_id')">Staff</label>
            <div class="field">
                <select
                    :id="fieldId('staff_member_id')"
                    v-model="form.staff_member_id"
                    :aria-describedby="describedBy('staff_member_id')"
                    :aria-invalid="hasError('staff_member_id')"
                    @change="reloadSlots"
                >
                    <option value="">Any staff member</option>
                    <option
                        v-for="staffMember in options.staff"
                        :key="staffMember.id"
                        :value="staffMember.id"
                    >
                        {{ staffMember.display_name }}
                    </option>
                </select>
                <p
                    v-if="form.errors.staff_member_id"
                    :id="errorId('staff_member_id')"
                    class="error"
                >
                    {{ form.errors.staff_member_id }}
                </p>
            </div>

            <label :for="fieldId('location_id')">Location</label>
            <div class="field">
                <select
                    :id="fieldId('location_id')"
                    v-model="form.location_id"
                    :aria-describedby="describedBy('location_id')"
                    :aria-invalid="hasError('location_id')"
                    @change="reloadSlots"
                >
                    <option value="">Choose a location</option>
                    <option
                        v-for="location in options.locations"
                        :key="location.id"
                        :value="location.id"
                    >
                        {{ location.name }}
                    </option>
                </select>
                <p
                    v-if="form.errors.location_id"
                    :id="errorId('location_id')"
                    class="error"
                >
                    {{ form.errors.location_id }}
                </p>
            </div>

            <label :for="fieldId('requested_starts_at')">Time</label>
            <div
                class="field slots"
                aria-live="polite"
            >
                <Deferred data="slots">
                    <template #fallback>
                        <p
                            class="slots-loading"
                            role="status"
                        >
                            Loading available times...
                        </p>
                        <select
                            :id="fieldId('requested_starts_at')"
                            disabled
                        >
                            <option>Choose service details first</option>
                        </select>
                    </template>

                    <select
                        :id="fieldId('requested_starts_at')"
                        v-model="form.requested_starts_at"
                        :aria-describedby="
                            describedBy('requested_starts_at', [
                                'booking-time-help',
                            ])
                        "
                        :aria-invalid="hasError('requested_starts_at')"
                    >
                        <option value="">Choose a time</option>
                        <option
                            v-for="slot in slots"
                            :key="slot.starts_at"
                            :value="slot.starts_at"
                        >
                            {{ slot.label }}
                        </option>
                    </select>
                </Deferred>
                <p id="booking-time-help">
                    Times are shown in {{ form.timezone }}.
                </p>
                <p
                    v-if="form.errors.requested_starts_at"
                    :id="errorId('requested_starts_at')"
                    class="error"
                >
                    {{ form.errors.requested_starts_at }}
                </p>
            </div>

            <label :for="fieldId('customer_name')">Name</label>
            <div class="field">
                <input
                    :id="fieldId('customer_name')"
                    v-model="form.customer_name"
                    :aria-describedby="describedBy('customer_name')"
                    :aria-invalid="hasError('customer_name')"
                    autocomplete="name"
                    type="text"
                />
                <p
                    v-if="form.errors.customer_name"
                    :id="errorId('customer_name')"
                    class="error"
                >
                    {{ form.errors.customer_name }}
                </p>
            </div>

            <label :for="fieldId('customer_email')">Email</label>
            <div class="field">
                <input
                    :id="fieldId('customer_email')"
                    v-model="form.customer_email"
                    :aria-describedby="describedBy('customer_email')"
                    :aria-invalid="hasError('customer_email')"
                    autocomplete="email"
                    type="email"
                />
                <p
                    v-if="form.errors.customer_email"
                    :id="errorId('customer_email')"
                    class="error"
                >
                    {{ form.errors.customer_email }}
                </p>
            </div>

            <label :for="fieldId('customer_phone')">Phone</label>
            <div class="field">
                <input
                    :id="fieldId('customer_phone')"
                    v-model="form.customer_phone"
                    :aria-describedby="describedBy('customer_phone')"
                    :aria-invalid="hasError('customer_phone')"
                    autocomplete="tel"
                    type="tel"
                />
                <p
                    v-if="form.errors.customer_phone"
                    :id="errorId('customer_phone')"
                    class="error"
                >
                    {{ form.errors.customer_phone }}
                </p>
            </div>

            <label :for="fieldId('notes')">Notes</label>
            <div class="field">
                <textarea
                    :id="fieldId('notes')"
                    v-model="form.notes"
                    :aria-describedby="describedBy('notes')"
                    :aria-invalid="hasError('notes')"
                />
                <p
                    v-if="form.errors.notes"
                    :id="errorId('notes')"
                    class="error"
                >
                    {{ form.errors.notes }}
                </p>
            </div>

            <button
                :disabled="form.processing"
                type="submit"
            >
                {{
                    form.processing
                        ? 'Sending request...'
                        : 'Request appointment'
                }}
            </button>
        </form>
    </main>
</template>
