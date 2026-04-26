import Image from 'next/image';
import { Eyebrow } from '@/components/brand/Eyebrow';
import { GoldBar } from '@/components/brand/GoldBar';
import { TagPill } from '@/components/content/TagPill';

/**
 * The welcome / vision panel — cream background with an italic greeting,
 * checklist-style vision items numbered 01 / 02 / 03 as a serif display
 * device, and the "What we do" tag row. Subtle Virunga park image fades
 * along the bottom edge to tie the page back to the Rwandan setting.
 */
export function HomeVision() {
  const visions = [
    'Create a community of African scientists in an international network for basic science.',
    'Address African challenges in natural hazards, energy, climate, materials and health.',
    'Deliver academic excellence — quality over quantity.',
  ];

  return (
    <section className="panel relative isolate overflow-hidden bg-cream">
      {/* Faint Virunga park backdrop, bleeding along the bottom */}
      <div className="pointer-events-none absolute inset-x-0 bottom-0 h-64 opacity-15 [mask-image:linear-gradient(to_top,#000,transparent)]">
        <Image
          src="/img/virunga-park.jpg"
          alt=""
          fill
          sizes="100vw"
          className="object-cover"
          style={{ mixBlendMode: 'multiply' }}
        />
      </div>
      <div className="grain" />

      <div className="relative mx-auto max-w-6xl px-6 py-24 md:px-10 md:py-32">
        <div className="grid gap-16 md:grid-cols-[1.15fr_1fr] md:gap-24">
          {/* Left column — welcome */}
          <div>
            <Eyebrow>Welcome · Murakaza neza</Eyebrow>
            <h2 className="display mt-6 text-[2.5rem] text-ink md:text-[3.25rem]">
              A young, dynamic hub for
              <br />
              <span className="italic text-teal">basic science in Africa.</span>
            </h2>
            <GoldBar className="mt-6" />
            <p className="mt-8 font-serif text-[17px] leading-relaxed text-mid">
              Established in 2018, EAIFR is a UNESCO Category&nbsp;II institute working in partnership
              with the Government and University of Rwanda, and the Abdus Salam International
              Centre for Theoretical Physics (ICTP).
            </p>

            <div className="mt-12">
              <Eyebrow>What we do</Eyebrow>
              <div className="mt-4 flex flex-wrap gap-2">
                <TagPill tone="teal">Research</TagPill>
                <TagPill tone="navy">Education</TagPill>
                <TagPill tone="rust">Conferences</TagPill>
                <TagPill tone="forest">Schools</TagPill>
                <TagPill tone="purple">Workshops</TagPill>
              </div>
            </div>
          </div>

          {/* Right column — numbered vision items */}
          <div>
            <Eyebrow>Our vision</Eyebrow>
            <ol className="mt-8 space-y-9">
              {visions.map((v, i) => (
                <li key={i} className="flex items-start gap-6">
                  <span
                    aria-hidden="true"
                    className="display shrink-0 text-[2.25rem] font-semibold leading-none text-gold"
                    style={{ fontVariantNumeric: 'tabular-nums' }}
                  >
                    {String(i + 1).padStart(2, '0')}
                  </span>
                  <span className="block border-t border-ink/10 pt-4 font-serif text-[17px] leading-[1.55] text-mid">
                    {v}
                  </span>
                </li>
              ))}
            </ol>
          </div>
        </div>
      </div>
    </section>
  );
}
