export default function Title({ widget }) {
  return (
    <header className="capell-bookings-widget capell-bookings-widget-title">
      {widget.data?.eyebrow ? <p className="capell-bookings-widget-eyebrow">{widget.data.eyebrow}</p> : null}
      <h2>{widget.data?.title ?? widget.data?.heading}</h2>
    </header>
  )
}
