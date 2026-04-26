import Image from 'next/image';
import Link from 'next/link';
import { DotGrid } from '@/components/brand/DotGrid';
import { Greeting } from '@/components/brand/Greeting';
import { Eyebrow } from '@/components/brand/Eyebrow';

/**
 * Editorial-grade hero. The brochure's cover panel translated for the web:
 *   - full-bleed Virunga mountains backdrop (quality 80 JPEG, art-directed)
 *   - layered navy/teal gradient overlay for typographic contrast
 *   - fine grain overlay for paper feel
 *   - asymmetric grid: headline left, location/status/focus metadata stacked right
 *   - page-load stagger: greeting → headline → gold bar → body → CTAs → meta
 *   - dot-grid motif in the corner (brochure p1)
 */
export function HomeHero() {
  return (
    <section className="panel relative isolate overflow-hidden bg-deep-navy text-white">
      {/* Art-directed backdrop — Virunga mountain pillars, heavily graded
         so the image reads as atmosphere rather than competing with the
         headline. Three overlay layers: base navy scrim, left-to-right
         text-zone darkening, and colored radial highlights for depth. */}
      <div className="absolute inset-0 -z-10">
        <Image
          src="/img/virunga-pillars.jpg"
          alt=""
          fill
          priority
          sizes="100vw"
          className="object-cover object-[50%_30%] opacity-40 [filter:saturate(1.15)_brightness(0.72)]"
        />
        {/* Base scrim — near-opaque top/bottom, still strong in the middle */}
        <div
          aria-hidden="true"
          className="absolute inset-0"
          style={{
            background:
              'linear-gradient(180deg, rgba(6,30,47,0.92) 0%, rgba(6,30,47,0.70) 35%, rgba(6,30,47,0.78) 70%, rgba(6,30,47,0.95) 100%)',
          }}
        />
        {/* Horizontal dark column — keeps both the headline on the left
           AND the metadata aside on the right readable; fades just
           slightly at 50-70% so the mountain image still hints through. */}
        <div
          aria-hidden="true"
          className="absolute inset-0"
          style={{
            background:
              'linear-gradient(90deg, rgba(6,30,47,0.85) 0%, rgba(6,30,47,0.55) 50%, rgba(6,30,47,0.72) 100%)',
          }}
        />
        {/* Colored accents for mood */}
        <div
          aria-hidden="true"
          className="absolute inset-0"
          style={{
            background:
              'radial-gradient(55% 45% at 88% 15%, rgba(13,128,128,0.30), transparent 65%), radial-gradient(45% 50% at 0% 100%, rgba(212,168,67,0.18), transparent 65%)',
          }}
        />
      </div>

      {/* Grain */}
      <div className="grain-dark" />

      {/* Top-right decorative dot grid */}
      <div className="absolute right-8 top-8 hidden sm:block reveal-in" style={{ ['--d' as string]: '1400ms' }}>
        <DotGrid cols={4} rows={4} tone="gold" />
      </div>
      {/* Bottom-left tiny dot accent */}
      <div className="absolute bottom-8 left-8 hidden sm:block reveal-in" style={{ ['--d' as string]: '1500ms' }}>
        <DotGrid cols={3} rows={3} tone="teal" />
      </div>

      <div className="relative mx-auto grid min-h-[640px] max-w-7xl content-end gap-12 px-6 pb-20 pt-28 md:min-h-[720px] md:grid-cols-[1.5fr_1fr] md:gap-20 md:px-10 md:pb-28 md:pt-36 lg:min-h-[780px]">
        <div>
          <div className="reveal" style={{ ['--d' as string]: '80ms' }}>
            <Eyebrow tone="gold">Est. 2018 · Kigali · UNESCO Cat. II</Eyebrow>
          </div>
          <div className="reveal" style={{ ['--d' as string]: '220ms' }}>
            <Greeting className="mt-7">Murakaza neza · Welcome</Greeting>
          </div>

          <h1
            className="display reveal mt-4 text-[2.75rem] text-white sm:text-[3.5rem] md:text-[4.6rem] lg:text-[5.25rem]"
            style={{ ['--d' as string]: '360ms' }}
          >
            Excellence in
            <br />
            <span className="italic text-gold">basic science</span>
            <br />
            for Africa.
          </h1>

          <div
            className="gold-bar animate mt-8"
            style={{ ['--d' as string]: '800ms', width: 64 }}
          />

          <p
            className="reveal mt-9 max-w-xl font-serif text-lg leading-[1.6] text-white/80 md:text-xl"
            style={{ ['--d' as string]: '900ms' }}
          >
            A young, dynamic hub for higher education and research in basic sciences — a partnership
            of the Government and University of Rwanda with the Abdus Salam International Centre
            for Theoretical Physics.
          </p>

          <div className="reveal mt-11 flex flex-wrap items-center gap-3" style={{ ['--d' as string]: '1080ms' }}>
            <Link
              href="/research"
              className="group inline-flex items-center gap-3 rounded-full bg-gold px-7 py-3.5 font-sans text-sm font-bold tracking-wide text-deep-navy transition-all hover:bg-[#e4b84f] hover:shadow-[0_10px_30px_-10px_rgba(212,168,67,0.6)]"
            >
              Our research
              <span className="transition-transform group-hover:translate-x-0.5">→</span>
            </Link>
            <Link
              href="/academics/msc-physics"
              className="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/[0.03] px-6 py-3.5 font-sans text-sm font-bold tracking-wide text-white backdrop-blur-sm hover:bg-white/10"
            >
              MSc in Physics
            </Link>
          </div>
        </div>

        {/* Asymmetric metadata column — brochure "contact-like" panel stripped to essentials */}
        <aside
          className="reveal relative hidden self-end md:block"
          style={{ ['--d' as string]: '1200ms' }}
        >
          <div className="relative border-l border-white/15 pl-8">
            <div className="absolute -left-[5px] top-0 h-2 w-2 rounded-full bg-gold" />
            <dl className="space-y-7 font-serif">
              <div>
                <dt className="eyebrow text-gold">Hosted at</dt>
                <dd className="mt-1.5 font-serif text-base leading-snug text-white/90">
                  University of Rwanda<br />
                  <span className="text-white/60">College of Science &amp; Technology · Kigali</span>
                </dd>
              </div>
              <div>
                <dt className="eyebrow text-gold">Status</dt>
                <dd className="mt-1.5 font-serif text-base leading-snug text-white/90">
                  UNESCO Category II Institute<br />
                  <span className="text-white/60">In partnership with ICTP</span>
                </dd>
              </div>
              <div>
                <dt className="eyebrow text-gold">Research programmes</dt>
                <dd className="mt-1.5 font-serif text-base italic leading-snug text-white/90">
                  Geophysics<br />
                  <span className="not-italic text-white/60">Volcanology &amp; Seismology</span><br />
                  <br className="hidden md:block" />
                  Condensed matter physics<br />
                  <span className="not-italic text-white/60">Materials &amp; Biophysics</span>
                </dd>
              </div>
            </dl>
          </div>
        </aside>
      </div>
    </section>
  );
}
