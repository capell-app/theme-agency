import { Deferred, router, useForm } from '@inertiajs/react'

function FieldError({ id, message }) {
    if (!message) {
        return null
    }

    return (
        <p
            className="error"
            id={id}
            role="alert"
        >
            {message}
        </p>
    )
}

export default function Request({
    options = { services: [], staff: [], locations: [] },
    slots = [],
    postUrl = '/bookings',
    timezone = 'UTC',
}) {
    const { data, setData, post, processing, errors, clearErrors } = useForm({
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

    const errorMessages = Object.entries(errors ?? {})
        .map(([, message]) => message)
        .filter((message) => typeof message === 'string' && message.length > 0)

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
        clearErrors(field)

        if (reload) {
            reloadSlots(nextData)
        }
    }

    function errorId(field) {
        return `${field}-error`
    }

    function describedBy(field) {
        return errors?.[field] ? errorId(field) : undefined
    }

    return (
        <main className="capell-inertia-booking-request">
            {errorMessages.length > 0 ? (
                <div
                    className="error"
                    role="alert"
                >
                    <p>Please review the highlighted booking details.</p>
                </div>
            ) : null}

            <form
                onSubmit={(event) => {
                    event.preventDefault()
                    post(postUrl)
                }}
            >
                <label>
                    Service
                    <select
                        aria-describedby={describedBy('service_id')}
                        aria-invalid={errors?.service_id ? 'true' : undefined}
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
                    <FieldError
                        id={errorId('service_id')}
                        message={errors?.service_id}
                    />
                </label>

                <label>
                    Staff
                    <select
                        aria-describedby={describedBy('staff_member_id')}
                        aria-invalid={
                            errors?.staff_member_id ? 'true' : undefined
                        }
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
                    <FieldError
                        id={errorId('staff_member_id')}
                        message={errors?.staff_member_id}
                    />
                </label>

                <label>
                    Location
                    <select
                        aria-describedby={describedBy('location_id')}
                        aria-invalid={errors?.location_id ? 'true' : undefined}
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
                    <FieldError
                        id={errorId('location_id')}
                        message={errors?.location_id}
                    />
                </label>

                <label>
                    Time
                    <div
                        aria-live="polite"
                        className="slots"
                    >
                        <Deferred
                            data="slots"
                            fallback={
                                <select
                                    aria-busy="true"
                                    disabled
                                >
                                    <option>Loading available times</option>
                                </select>
                            }
                        >
                            <select
                                aria-describedby={describedBy(
                                    'requested_starts_at',
                                )}
                                aria-invalid={
                                    errors?.requested_starts_at
                                        ? 'true'
                                        : undefined
                                }
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
                    </div>
                    <FieldError
                        id={errorId('requested_starts_at')}
                        message={errors?.requested_starts_at}
                    />
                </label>

                <label>
                    Name
                    <input
                        aria-describedby={describedBy('customer_name')}
                        aria-invalid={
                            errors?.customer_name ? 'true' : undefined
                        }
                        autoComplete="name"
                        type="text"
                        value={data.customer_name}
                        onChange={(event) =>
                            updateField('customer_name', event.target.value)
                        }
                    />
                    <FieldError
                        id={errorId('customer_name')}
                        message={errors?.customer_name}
                    />
                </label>

                <label>
                    Email
                    <input
                        aria-describedby={describedBy('customer_email')}
                        aria-invalid={
                            errors?.customer_email ? 'true' : undefined
                        }
                        autoComplete="email"
                        type="email"
                        value={data.customer_email}
                        onChange={(event) =>
                            updateField('customer_email', event.target.value)
                        }
                    />
                    <FieldError
                        id={errorId('customer_email')}
                        message={errors?.customer_email}
                    />
                </label>

                <label>
                    Phone
                    <input
                        aria-describedby={describedBy('customer_phone')}
                        aria-invalid={
                            errors?.customer_phone ? 'true' : undefined
                        }
                        autoComplete="tel"
                        type="tel"
                        value={data.customer_phone}
                        onChange={(event) =>
                            updateField('customer_phone', event.target.value)
                        }
                    />
                    <FieldError
                        id={errorId('customer_phone')}
                        message={errors?.customer_phone}
                    />
                </label>

                <label>
                    Notes
                    <textarea
                        aria-describedby={describedBy('notes')}
                        aria-invalid={errors?.notes ? 'true' : undefined}
                        value={data.notes}
                        onChange={(event) =>
                            updateField('notes', event.target.value)
                        }
                    />
                    <FieldError
                        id={errorId('notes')}
                        message={errors?.notes}
                    />
                </label>

                <button
                    disabled={processing}
                    type="submit"
                >
                    {processing ? 'Requesting appointment' : 'Request appointment'}
                </button>
            </form>
        </main>
    )
}
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
