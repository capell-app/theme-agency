export default function Title({ widget }) {
  return (
    <header className="capell-widget capell-widget-title">
      {widget.data?.eyebrow ? <p>{widget.data.eyebrow}</p> : null}
      <h2>{widget.data?.title ?? widget.data?.heading}</h2>
    </header>
  )
}
