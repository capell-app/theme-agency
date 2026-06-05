export default function Image({ widget }) {
  return (
    <figure className="capell-bookings-widget capell-bookings-widget-image">
      {widget.data?.image?.url ? <img alt={widget.data.image.alt ?? ''} loading="lazy" src={widget.data.image.url} /> : null}
      {widget.data?.caption ? <figcaption>{widget.data.caption}</figcaption> : null}
    </figure>
  )
}
