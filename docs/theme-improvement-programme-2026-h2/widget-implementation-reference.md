# Capell Theme Widget Implementation Reference

**Status**: Ready for Development
**Total Widgets**: 36 signature + 3 shared primitives
**Audience**: Frontend engineers, component architects, Blade template authors

---

## QUICK REFERENCE: WIDGET FAMILIES

### Archive-Directory (24 widgets)
Each theme receives **6 widgets** with distinct identity:

| Theme | Energy | Type Signature | Example Widgets |
|-------|--------|---|---|
| **Wild-Card** | Playful, velocity | Shuffle, swipe, gamification | Featured carousel, facet wall, card shuffle |
| **Reel-Room** | Cinema, curatorial | Motion, timeline, jury | Video grid, date filter, score matrix |
| **Off-Grid** | Brutalist, sparse | Text-first, monospace | Links directory, irregular index, markers |
| **Gold-Rush** | Awards, scoreboard | Voting, counts, heatmaps | Score criteria, voting gauge, nominees |

### Foundation Pair (9 widgets)
- **Liquid-Glass** (5): Glassmorphism, backdrop-filter, depth layers, refraction metaphor
- **Foundation Default** (4): Pricing, FAQ, changelog, stats—honest utility, no flash

### Shared Primitives (3)
- **responsive-table-to-cards**: Mobile-aware table contract (CSS-only, `:has()`)
- **count-up-stat-component**: Reusable counter animation (Intersection Observer + number formatter)
- **scroll-driven-animation**: Base CSS + JS for parallax, fade-on-scroll, sticky headers

---

## PAYLOAD & RENDERING PATTERN

### Layout Builder Integration
All widgets receive data as **structured payloads from Layout Builder**:

```php
// In Blade template (e.g., featured-carousel.blade.php):
@props([
    'items' => [],           // [ {title, image, tags, href}, ... ]
    'currentIndex' => 0,
    'autoplayInterval' => 5000,
])

// Rendering:
@foreach($items as $index => $item)
    <div class="carousel-item" data-index="{{ $index }}">
        <img src="{{ $item['image'] }}" />
    </div>
@endforeach
```

### No Database Queries
- Filtering/sorting happens **payload-driven** (before render) or **client-side** (via JS state)
- URL query params passed in payload: `filterUrl`, `sortUrl` as action targets
- All data is public-readable Blade rendering; no authoring metadata leaked

### Client-Side State Management
For interactive features (carousel position, accordion expand, facet toggle):
```javascript
// Small vanilla JS (no framework required)
document.addEventListener('DOMContentLoaded', () => {
  const carousel = document.querySelector('[data-carousel]');

  carousel?.addEventListener('click', (e) => {
    if (e.target.matches('[data-next]')) {
      // Update currentIndex, re-render carousel position
    }
  });
});
```

---

## CSS TOKEN MAPPING

Every widget respects these theme tokens:

```css
:root {
  /* Colors */
  --primary-color: #2563eb;          /* brand blue */
  --accent-color: #dc2626;           /* theme-specific: wild-card=orange, etc. */
  --neutral-color: #e5e7eb;          /* borders, dividers */
  --surface-color: #ffffff;          /* card bg */
  --foreground-color: #1f2937;       /* text */

  /* Typography */
  --heading-font: "Inter", sans-serif;
  --body-font: "Inter", sans-serif;  /* off-grid uses monospace */

  /* Spacing & Layout */
  --spacing-unit: 0.25rem;           /* base 4px grid */
  --card-style: "subtle";            /* subtle|bold|raw (off-grid) */
  --layout-presentation: "structured"; /* structured|airy|dense */

  /* Motion & Interaction */
  --motion-intensity: "medium";      /* low|medium|high */
  --media-treatment: "natural";      /* natural|feature-slab|motion-preview|irregular */
  --radius: 0;                       /* zero|subtle|xl (liquid-glass) */

  /* Density */
  --heading-scale: 1.5;              /* multiplier on base size */
  --card-density: "compact";         /* compact|balanced|airy */

  /* Liquid-Glass Only */
  --overlay-treatment: "glass";      /* glass|matte */
}
```

### Usage in Widgets
```blade
<div class="carousel" style="--accent-color: var(--accent-color);">
  @foreach($items as $item)
    <div class="carousel-item" style="border-color: var(--accent-color);">
      {{ $item['title'] }}
    </div>
  @endforeach
</div>

<style>
  .carousel-item {
    border: 3px solid var(--accent-color);
    border-radius: var(--radius);
    padding: calc(var(--spacing-unit) * 4);
    background: var(--surface-color);
  }
</style>
```

---

## MOTION TIERS & IMPLEMENTATION

Every widget has **3 motion implementations** (selected by `motionIntensity` token):

### Tier: **Low** (`motionIntensity: subtle`)
- CSS color/opacity transitions (no movement)
- Hover states only
- No animation on page load
- **Example**: Accordion chevron color change on expand

### Tier: **Medium** (`motionIntensity: balanced`)
- Slide/fade transitions (200–400ms duration)
- Staggered load animations (50ms per item)
- Respects `prefers-reduced-motion` (all animations off if set)
- **Example**: Carousel slide-in, checkbox bounce, count-up animation (1–2 sec)

### Tier: **High** (`motionIntensity: dynamic`)
- Parallax, 3D transforms, scroll-driven animations
- Complex easing (cubic-bezier)
- Real-time updates with smooth counter animations
- **Example**: Image pan on hover, page scroll parallax, live vote counter

### Implementation Template
```blade
@php
  $motionClass = match($motionIntensity ?? 'medium') {
    'low' => 'motion-subtle',
    'high' => 'motion-dynamic',
    default => 'motion-balanced',
  };
@endphp

<div class="widget {{ $motionClass }}">
  <!-- Content -->
</div>

<style>
  @media (prefers-reduced-motion: reduce) {
    .widget * {
      animation: none !important;
      transition: none !important;
    }
  }

  .widget.motion-subtle .item { transition: color 0.3s; }
  .widget.motion-balanced .item {
    animation: fadeInUp 0.4s ease-out;
    animation-fill-mode: both;
  }
  .widget.motion-dynamic .item {
    animation: parallaxShift 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
  }
</style>
```

---

## RESPONSIVE DESIGN PATTERNS

### Archive-Directory (Dense)
- **Desktop (1024px+)**: 3–4 col grid, tight spacing (2–4px gaps)
- **Tablet (768–1023px)**: 2–3 col, moderate spacing
- **Mobile (<768px)**: 1–2 col or scroll-snap carousel, stacked layout for tables

### Foundation (Spacious)
- **Desktop**: 2–3 col, generous spacing (16–24px gaps)
- **Tablet**: 1–2 col
- **Mobile**: Single column, full-width CTA buttons

### Container Queries (Modern CSS)
```css
@container (min-width: 48rem) {
  .widget-grid { grid-template-columns: repeat(3, 1fr); }
}

@container (max-width: 30rem) {
  .widget-grid { grid-template-columns: 1fr; }
}
```

---

## SHARED PRIMITIVE SPECS

### 1. Responsive Table-to-Cards Contract
**File**: `theme-foundation/resources/components/table-cards.blade.php`

**Payload**:
```php
@props([
  'headers' => ['Name', 'Email', 'Role'],
  'rows' => [
    ['Alice', 'alice@ex.com', 'Admin'],
    ['Bob', 'bob@ex.com', 'User'],
  ],
])
```

**Template**:
```blade
<div class="table-cards-container">
  <table class="table-cards">
    <thead>
      <tr>
        @foreach($headers as $h)
          <th>{{ $h }}</th>
        @endforeach
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $row)
        <tr>
          @foreach($row as $index => $cell)
            <td data-label="{{ $headers[$index] ?? '' }}">{{ $cell }}</td>
          @endforeach
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

<style>
  @media (max-width: 768px) {
    .table-cards {
      display: block;
    }
    .table-cards tr {
      display: grid;
      grid-template-columns: auto 1fr;
      gap: 1rem;
      border-bottom: 1px solid var(--neutral-color);
      padding: 1rem 0;
    }
    .table-cards th {
      display: none;
    }
    .table-cards td {
      display: contents;
    }
    .table-cards td::before {
      content: attr(data-label);
      font-weight: bold;
      grid-column: 1;
    }
  }
</style>
```

---

### 2. Count-Up Stat Component
**File**: `theme-foundation/resources/components/stat-counter.blade.php`

**Payload**:
```php
@props([
  'value' => 1000,
  'label' => 'Users',
  'unit' => '',
  'duration' => 2000, // ms
  'animateOnLoad' => true,
])
```

**Template**:
```blade
<div class="stat-counter"
     data-value="{{ $value }}"
     data-duration="{{ $duration }}"
     data-animate="{{ $animateOnLoad ? 'true' : 'false' }}">
  <div class="stat-number">0</div>
  <div class="stat-label">{{ $label }}</div>
</div>

<script>
  document.querySelectorAll('[data-value]').forEach(el => {
    const value = parseInt(el.dataset.value);
    const duration = parseInt(el.dataset.duration);
    const shouldAnimate = el.dataset.animate === 'true';

    const counterEl = el.querySelector('.stat-number');

    const observer = new IntersectionObserver((entries) => {
      if (entries[0].isIntersecting && shouldAnimate) {
        animateCounter(counterEl, 0, value, duration);
      }
    });
    observer.observe(el);
  });

  function animateCounter(el, start, end, duration) {
    const startTime = performance.now();
    const animate = (currentTime) => {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);
      const current = Math.floor(start + (end - start) * progress);

      el.textContent = new Intl.NumberFormat('en-US').format(current);

      if (progress < 1) {
        requestAnimationFrame(animate);
      }
    };
    requestAnimationFrame(animate);
  }
</script>

<style>
  .stat-counter {
    text-align: center;
  }
  .stat-number {
    font-size: calc(var(--heading-scale) * 2rem);
    font-weight: bold;
    color: var(--primary-color);
  }
  .stat-label {
    font-size: 0.875rem;
    color: var(--text-light);
  }
</style>
```

---

### 3. Scroll-Driven Animation Base
**File**: `theme-foundation/resources/css/scroll-driven.css`

**Utility Classes**:
```css
/* Fade-in on scroll-into-view */
.scroll-fade-in {
  opacity: 0;
  animation: fadeIn auto linear forwards;
  animation-timeline: view();
  animation-range: entry 0% cover 30%;
}

/* Parallax on scroll */
.scroll-parallax {
  --parallax-speed: 0.5;
  transform: translateY(calc(var(--scroll-y) * var(--parallax-speed) * 1px));
}

/* Sticky on scroll */
.scroll-sticky {
  position: sticky;
  top: 0;
  z-index: 10;
  background: var(--surface-color);
}

/* Carousel with scroll-snap */
.scroll-snap-carousel {
  display: flex;
  overflow-x: scroll;
  scroll-snap-type: x mandatory;
  scroll-behavior: smooth;
}
.scroll-snap-carousel > * {
  scroll-snap-align: center;
  scroll-snap-stop: always;
  flex-shrink: 0;
}

/* CSS Animations */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideInUp {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

@keyframes parallaxShift {
  from { transform: translateY(-10px); }
  to { transform: translateY(10px); }
}
```

**JavaScript Helper** (for `scroll-parallax`):
```javascript
let scrollY = 0;
window.addEventListener('scroll', () => {
  scrollY = window.scrollY;
  document.documentElement.style.setProperty('--scroll-y', scrollY);
});

// Alternatively, use CSS scroll-driven animations (view())
// No JS needed for modern browsers
```

---

## ACCESSIBILITY REQUIREMENTS

All widgets must:

1. **Keyboard Navigation**
   - Tab through interactive elements in logical order
   - Enter/Space activate buttons
   - Arrow keys navigate carousels/lists
   - Escape closes modals/popovers

2. **Screen Readers**
   - `role="img"` with `<title>` for SVG charts
   - `aria-label` for icon buttons
   - `aria-live="polite"` for live stat updates
   - Semantic HTML: `<button>`, `<a>`, `<fieldset>`, `<legend>`

3. **Color Contrast**
   - Text: 4.5:1 (normal) / 3:1 (large 18pt+)
   - Focus indicator: 3:1 contrast with background

4. **Motion**
   - Respect `prefers-reduced-motion: reduce`
   - No auto-play on carousels unless user explicitly enables
   - Parallax disabled on mobile/low-motion preference

---

## TESTING CHECKLIST

Per widget, verify:

- [ ] Payload renders correctly (all data types: string, array, nested object)
- [ ] All 3 motion tiers work (low, medium, high)
- [ ] Mobile layout responds correctly (1/2-col, full-width)
- [ ] Dark mode: colors update via token values
- [ ] Keyboard nav: all interactive elements tabable
- [ ] Screen reader: announces interactive elements, live updates
- [ ] `prefers-reduced-motion` respected
- [ ] Form inputs accessible (labels, error messages)
- [ ] Images have alt text (if applicable)

---

## FILE STRUCTURE

```
packages/theme-{name}/
├── resources/
│   └── views/
│       ├── sections/
│       │   ├── featured-carousel.blade.php
│       │   ├── metadata-facet-wall.blade.php
│       │   └── ... (6 widgets per theme)
│       └── livewire/
│           ├── FeaturedCarousel.php
│           └── ... (if stateful)
└── src/
    └── {ThemeName}ThemeServiceProvider.php
```

**Blade Naming**: `{kebab-case-widget-name}.blade.php`
**Component Props**: Documented via `@props([...])` at top of file
**CSS**: Scoped via BEM class naming + theme-foundation tokens

---

## EXAMPLE: Featured Carousel (Wild-Card)

**File**: `packages/theme-wild-card/resources/views/sections/featured-carousel.blade.php`

```blade
@props([
    'items' => [],
    'currentIndex' => 0,
    'autoplayInterval' => 5000,
    'motionIntensity' => 'medium',
])

<div class="featured-carousel"
     data-carousel
     data-autoplay="{{ $autoplayInterval }}"
     data-motion="{{ $motionIntensity }}">

  <!-- Carousel items -->
  <div class="carousel-track">
    @foreach($items as $index => $item)
      <div class="carousel-item {{ $index === $currentIndex ? 'is-active' : '' }}"
           data-index="{{ $index }}"
           style="background-image: url('{{ $item['image'] }}')">

        <div class="carousel-overlay"></div>

        <div class="carousel-content">
          <h3 class="carousel-title">{{ $item['title'] }}</h3>

          @if($item['tags'] ?? false)
            <div class="carousel-tags">
              @foreach($item['tags'] as $tag)
                <span class="tag">{{ $tag }}</span>
              @endforeach
            </div>
          @endif
        </div>

        <a href="{{ $item['href'] }}" class="carousel-link">View</a>
      </div>
    @endforeach
  </div>

  <!-- Navigation -->
  <button class="carousel-prev" data-prev aria-label="Previous">←</button>
  <button class="carousel-next" data-next aria-label="Next">→</button>

  <!-- Indicators -->
  <div class="carousel-indicators">
    @foreach(range(0, count($items) - 1) as $i)
      <button class="indicator {{ $i === $currentIndex ? 'is-active' : '' }}"
              data-index="{{ $i }}"
              aria-label="Go to item {{ $i + 1 }}"></button>
    @endforeach
  </div>
</div>

<style>
  .featured-carousel {
    position: relative;
    overflow: hidden;
  }

  .carousel-track {
    display: flex;
    scroll-snap-type: x mandatory;
    transition: transform 0.4s ease-out;
  }

  .carousel-item {
    flex: 1 0 100%;
    position: relative;
    background-size: cover;
    background-position: center;
    aspect-ratio: 16 / 9;
    scroll-snap-align: center;
  }

  .carousel-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.3);
  }

  .carousel-content {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 2rem;
    background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
    color: white;
  }

  .carousel-title {
    font-size: calc(var(--heading-scale) * 1.5rem);
    margin: 0 0 0.5rem 0;
    font-weight: 600;
  }

  .carousel-tags {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
  }

  .tag {
    font-size: 0.75rem;
    padding: 0.25rem 0.75rem;
    background: var(--accent-color);
    border-radius: 4px;
    color: white;
  }

  /* Motion: Medium (Default) */
  .featured-carousel[data-motion="medium"] .carousel-item.is-active .carousel-content {
    animation: slideUpFade 0.5s ease-out;
  }

  @keyframes slideUpFade {
    from {
      transform: translateY(20px);
      opacity: 0;
    }
    to {
      transform: translateY(0);
      opacity: 1;
    }
  }

  /* Motion: High */
  .featured-carousel[data-motion="high"] .carousel-track {
    animation: parallaxZoom 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
  }

  @keyframes parallaxZoom {
    from { transform: scale(1.05); }
    to { transform: scale(1); }
  }

  /* Responsive */
  @media (max-width: 768px) {
    .carousel-content {
      padding: 1rem;
    }
    .carousel-title {
      font-size: calc(var(--heading-scale) * 1rem);
    }
  }

  /* Accessibility */
  @media (prefers-reduced-motion: reduce) {
    .featured-carousel * {
      animation: none !important;
      transition: none !important;
    }
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const carousel = document.querySelector('[data-carousel]');
    if (!carousel) return;

    const track = carousel.querySelector('.carousel-track');
    const items = carousel.querySelectorAll('.carousel-item');
    let currentIndex = 0;

    const next = () => {
      currentIndex = (currentIndex + 1) % items.length;
      updateCarousel();
    };

    const prev = () => {
      currentIndex = (currentIndex - 1 + items.length) % items.length;
      updateCarousel();
    };

    const updateCarousel = () => {
      track.style.transform = `translateX(-${currentIndex * 100}%)`;
      items.forEach((item, i) => {
        item.classList.toggle('is-active', i === currentIndex);
      });
      carousel.querySelectorAll('.indicator').forEach((ind, i) => {
        ind.classList.toggle('is-active', i === currentIndex);
      });
    };

    carousel.querySelector('[data-next]')?.addEventListener('click', next);
    carousel.querySelector('[data-prev]')?.addEventListener('click', prev);
    carousel.querySelectorAll('[data-index]').forEach((btn) => {
      btn.addEventListener('click', () => {
        currentIndex = parseInt(btn.dataset.index);
        updateCarousel();
      });
    });
  });
</script>
```

---

## NEXT IMPLEMENTATION STEPS

1. **Scaffold** all 36 widget Blade files (with placeholder @props)
2. **Implement** shared primitives (table-cards, stat-counter, scroll-driven.css)
3. **Build** featured-carousel & metadata-facet-wall (wild-card sample) with all 3 motion tiers
4. **Test** responsive, dark mode, a11y, motion preferences
5. **Roll out** remaining widgets per phase (reel-room, off-grid, gold-rush, liquid-glass, foundation)
