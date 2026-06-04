import { createInertiaApp } from '@inertiajs/react'
import { createRoot } from 'react-dom/client'
import Page from './Pages/Capell/Page.jsx'
import BookingRequest from './Pages/Capell/Bookings/Request.jsx'

const pages = {
  'Capell/Page': Page,
  'Capell/Bookings/Request': BookingRequest,
}

createInertiaApp({
  resolve: (name) => pages[name] ?? Page,
  setup({ el, App, props }) {
    createRoot(el).render(<App {...props} />)
  },
})
