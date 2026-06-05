export default function Content({ widget }) {
  return (
    <article className="capell-widget capell-widget-content">
      {widget.data?.title ? <h2>{widget.data.title}</h2> : null}
      {widget.data?.content ? <div dangerouslySetInnerHTML={{ __html: widget.data.content }} /> : null}
    </article>
  )
}
