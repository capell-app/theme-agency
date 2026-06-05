import { Deferred, router, useForm } from '@inertiajs/react'

export default function Request({
    options = { services: [], staff: [], locations: [] },
    slots = [],
    postUrl = '/bookings',
    timezone = 'UTC',
}) {
    const { data, setData, post, processing } = useForm({
        service_id: '',
        staff_member_id: '',
        location_id: '',
        requested_starts_at: '',
        timezone,
        customer_name: '',
        customer_email: '',
        customer_phone: '',
        notes: '',
    })

    function reloadSlots(nextData = data) {
        router.reload({
            only: ['slots'],
            data: {
                service_id: nextData.service_id,
                staff_member_id: nextData.staff_member_id,
                location_id: nextData.location_id,
                timezone: nextData.timezone,
            },
        })
    }

    function updateField(field, value, reload = false) {
        const nextData = { ...data, [field]: value }

        setData(field, value)

        if (reload) {
            reloadSlots(nextData)
        }
    }

    return (
        <main className="capell-inertia-booking-request">
            <form
                onSubmit={(event) => {
                    event.preventDefault()
                    post(postUrl)
                }}
            >
                <label>
                    Service
                    <select
                        value={data.service_id}
                        onChange={(event) =>
                            updateField('service_id', event.target.value, true)
                        }
                    >
                        <option value="">Choose a service</option>
                        {options.services.map((service) => (
                            <option
                                key={service.id}
                                value={service.id}
                            >
                                {service.name}
                            </option>
                        ))}
                    </select>
                </label>

                <label>
                    Staff
                    <select
                        value={data.staff_member_id}
                        onChange={(event) =>
                            updateField(
                                'staff_member_id',
                                event.target.value,
                                true,
                            )
                        }
                    >
                        <option value="">Any staff member</option>
                        {options.staff.map((staffMember) => (
                            <option
                                key={staffMember.id}
                                value={staffMember.id}
                            >
                                {staffMember.display_name}
                            </option>
                        ))}
                    </select>
                </label>

                <label>
                    Location
                    <select
                        value={data.location_id}
                        onChange={(event) =>
                            updateField('location_id', event.target.value, true)
                        }
                    >
                        <option value="">Choose a location</option>
                        {options.locations.map((location) => (
                            <option
                                key={location.id}
                                value={location.id}
                            >
                                {location.name}
                            </option>
                        ))}
                    </select>
                </label>

                <label>
                    Time
                    <Deferred
                        data="slots"
                        fallback={
                            <select disabled>
                                <option>Loading available times</option>
                            </select>
                        }
                    >
                        <select
                            value={data.requested_starts_at}
                            onChange={(event) =>
                                updateField(
                                    'requested_starts_at',
                                    event.target.value,
                                )
                            }
                        >
                            <option value="">Choose a time</option>
                            {slots.map((slot) => (
                                <option
                                    key={slot.starts_at}
                                    value={slot.starts_at}
                                >
                                    {slot.label}
                                </option>
                            ))}
                        </select>
                    </Deferred>
                </label>

                <label>
                    Name
                    <input
                        autoComplete="name"
                        type="text"
                        value={data.customer_name}
                        onChange={(event) =>
                            updateField('customer_name', event.target.value)
                        }
                    />
                </label>

                <label>
                    Email
                    <input
                        autoComplete="email"
                        type="email"
                        value={data.customer_email}
                        onChange={(event) =>
                            updateField('customer_email', event.target.value)
                        }
                    />
                </label>

                <label>
                    Phone
                    <input
                        autoComplete="tel"
                        type="tel"
                        value={data.customer_phone}
                        onChange={(event) =>
                            updateField('customer_phone', event.target.value)
                        }
                    />
                </label>

                <label>
                    Notes
                    <textarea
                        value={data.notes}
                        onChange={(event) =>
                            updateField('notes', event.target.value)
                        }
                    />
                </label>

                <button
                    disabled={processing}
                    type="submit"
                >
                    Request appointment
                </button>
            </form>
        </main>
    )
}
