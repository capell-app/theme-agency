import ContentWidget from '../../Components/Capell/Widgets/Content.jsx'
import ImageWidget from '../../Components/Capell/Widgets/Image.jsx'
import TitleWidget from '../../Components/Capell/Widgets/Title.jsx'
import { sanitizePublicHtml } from '../../Support/publicHtml.js'

const widgets = {
    'Capell/Widgets/Content': ContentWidget,
    'Capell/Widgets/Image': ImageWidget,
    'Capell/Widgets/Title': TitleWidget,
}

export default function Page({ page }) {
    return (
        <main className="capell-inertia-bookings-page">
            <section className="capell-inertia-bookings-hero">
                {page.meta?.eyebrow ? <p>{page.meta.eyebrow}</p> : null}
                <h1>{page.title}</h1>
                {typeof page.content === 'string' ? (
                    <div
                        dangerouslySetInnerHTML={{
                            __html: sanitizePublicHtml(page.content),
                        }}
                    />
                ) : null}
                <a href="/bookings">Request appointment</a>
            </section>

            {(page.layout?.containers ?? []).map((container) => (
                <section
                    className="capell-inertia-bookings-container"
                    key={container.key}
                >
                    {container.widgets.map((widget) => {
                        const WidgetComponent =
                            widgets[widget.component] ?? ContentWidget

                        return (
                            <WidgetComponent
                                key={`${widget.key}:${widget.occurrence}`}
                                widget={widget}
                            />
                        )
                    })}
                </section>
            ))}
        </main>
    )
}
