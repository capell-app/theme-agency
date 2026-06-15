import { sanitizePublicHtml } from '../../../Support/publicHtml.js'

export default function Content({ widget }) {
    return (
        <article className="capell-bookings-widget capell-bookings-widget-content">
            {widget.data?.eyebrow ? (
                <p className="capell-bookings-widget-eyebrow">
                    {widget.data.eyebrow}
                </p>
            ) : null}
            {widget.data?.title ? <h2>{widget.data.title}</h2> : null}
            {widget.data?.content ? (
                <div
                    dangerouslySetInnerHTML={{
                        __html: sanitizePublicHtml(widget.data.content),
                    }}
                />
            ) : null}
        </article>
    )
}
