import { Eyebrow } from '@/components/brand/Eyebrow';
import { GoldBar } from '@/components/brand/GoldBar';

/**
 * A "global reach" band that surfaces the brochure's collaboration
 * dot-map as a centerpiece. Three editorial stats sit on top of the
 * map to quantify EAIFR's network. Cream background for contrast
 * against the surrounding warm/forest sections.
 */
export function HomeGlobalReach() {
  return (
    <section className="panel relative isolate overflow-hidden bg-cream">
      <div className="grain" />
      <div className="mx-auto max-w-7xl px-6 py-24 md:px-10 md:py-32">
        <div className="grid gap-16 lg:grid-cols-[1fr_1.35fr] lg:items-center lg:gap-24">
          <div>
            <Eyebrow>A global network</Eyebrow>
            <h2 className="display mt-6 text-[2.25rem] text-ink md:text-[3rem]">
              Rooted in Kigali,
              <br />
              <span className="italic text-teal">connected worldwide.</span>
            </h2>
            <GoldBar className="mt-5" />
            <p className="mt-7 max-w-lg font-serif text-[17px] leading-relaxed text-mid">
              EAIFR collaborates with universities, research centers and funding bodies on four
              continents — pairing African scientific questions with international expertise,
              infrastructure and networks.
            </p>

            <dl className="mt-10 grid grid-cols-3 gap-5 border-t border-ink/10 pt-10">
              <div>
                <dt className="eyebrow text-teal">Countries</dt>
                <dd className="display mt-2 text-[2.5rem] text-ink">20+</dd>
              </div>
              <div>
                <dt className="eyebrow text-teal">Institutions</dt>
                <dd className="display mt-2 text-[2.5rem] text-ink">40+</dd>
              </div>
              <div>
                <dt className="eyebrow text-teal">Est.</dt>
                <dd className="display mt-2 text-[2.5rem] text-ink">2018</dd>
              </div>
            </dl>
          </div>

          {/* World map visual — the brochure's dot-map */}
          <div className="relative">
            <div className="relative aspect-[16/10] w-full">
              {/* eslint-disable-next-line @next/next/no-img-element */}
              <img
                src="/svg/world-dots-teal.svg"
                alt="Collaboration map — EAIFR's international network"
                className="h-full w-full object-contain opacity-65"
              />
            </div>
            {/* A couple of gold anchor markers for Kigali-ish position */}
            <span
              className="absolute h-3 w-3 rounded-full bg-gold ring-4 ring-gold/20"
              style={{ top: '58%', left: '54%', transform: 'translate(-50%,-50%)' }}
              aria-hidden="true"
            />
            <span
              className="absolute hidden font-sans text-[10px] font-black uppercase tracking-[0.18em] text-ink/70 md:inline"
              style={{ top: '62%', left: '54%' }}
              aria-hidden="true"
            >
              Kigali
            </span>
          </div>
        </div>
      </div>
    </section>
  );
}
