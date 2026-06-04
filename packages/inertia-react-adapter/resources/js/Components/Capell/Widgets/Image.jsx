export default function Image({ widget }) {
  return (
    <figure className="capell-widget capell-widget-image">
      {widget.data?.image?.url ? <img alt={widget.data.image.alt ?? ''} src={widget.data.image.url} /> : null}
      {widget.data?.caption ? <figcaption>{widget.data.caption}</figcaption> : null}
    </figure>
  )
}
