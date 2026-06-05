import ContentWidget from '../../Components/Capell/Widgets/Content.jsx'
import ImageWidget from '../../Components/Capell/Widgets/Image.jsx'
import TitleWidget from '../../Components/Capell/Widgets/Title.jsx'

const widgets = {
  'Capell/Widgets/Content': ContentWidget,
  'Capell/Widgets/Image': ImageWidget,
  'Capell/Widgets/Title': TitleWidget,
}

export default function Page({ page }) {
  return (
    <main className="capell-inertia-page">
      <section className="capell-inertia-page__content">
        {page.title ? <h1>{page.title}</h1> : null}
        {typeof page.content === 'string' ? <div dangerouslySetInnerHTML={{ __html: page.content }} /> : null}
      </section>

      {(page.layout?.containers ?? []).map((container) => (
        <section className="capell-inertia-page__container" data-container={container.key} key={container.key}>
          {container.widgets.map((widget) => {
            const WidgetComponent = widgets[widget.component] ?? ContentWidget

            return <WidgetComponent key={`${widget.key}:${widget.occurrence}`} widget={widget} />
          })}
        </section>
      ))}
    </main>
  )
}
