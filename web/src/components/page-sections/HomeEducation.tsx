import Image from 'next/image';
import Link from 'next/link';
import { Eyebrow } from '@/components/brand/Eyebrow';
import { GoldBar } from '@/components/brand/GoldBar';
import { DotGrid } from '@/components/brand/DotGrid';
import { listProgrammes } from '@/lib/content';

/**
 * Forest-gradient Education panel — translates the brochure p2-edu
 * panel with an actual Education.png backdrop (low-opacity, luminosity-
 * blended) and asymmetric card placement.
 */
export async function HomeEducation() {
  const programmes = await listProgrammes();
  const msc = programmes.find((p) => p.slug === 'msc-physics') ?? programmes[0];

  return (
    <section
      className="panel relative isolate overflow-hidden text-white"
      style={{
        background: 'linear-gradient(180deg,#143D28 0%,#20603D 100%)',
      }}
    >
      {/* Atmospheric backdrop — the brochure's education montage */}
      <div className="absolute inset-0 -z-10">
        <Image
          src="/img/education.jpg"
          alt=""
          fill
          sizes="100vw"
          className="object-cover opacity-[0.14]"
          style={{ mixBlendMode: 'luminosity' }}
        />
      </div>

      <div className="grain-dark" />

      <DotGrid cols={4} rows={4} tone="gold" className="absolute right-8 top-8" />
      <DotGrid cols={3} rows={3} tone="cream" className="absolute bottom-8 left-8 opacity-60" />

      <div className="mx-auto grid max-w-7xl gap-14 px-6 py-24 md:grid-cols-[1.1fr_1fr] md:gap-20 md:px-10 md:py-32">
        <div>
          <Eyebrow tone="gold">Academics · Education</Eyebrow>
          <h2 className="display mt-6 text-[2.5rem] text-white md:text-[3.25rem]">
            Training the next generation of
            <br />
            <span className="italic text-gold">African physicists.</span>
          </h2>
          <GoldBar className="mt-6" />
          <p className="mt-8 max-w-xl font-serif text-lg leading-relaxed text-white/80">
            EAIFR offers graduate-level physics education at the University of Rwanda, with
            scientific oversight by ICTP. Scholarships are available for admitted African students
            — covering tuition, stipend, travel and accommodation.
          </p>

          {/* Inline stat rail */}
          <dl className="mt-12 grid max-w-lg grid-cols-3 gap-6 border-l border-white/15 pl-6">
            <div>
              <dt className="eyebrow text-gold">Duration</dt>
              <dd className="mt-2 font-serif text-2xl font-semibold text-white">{msc?.duration || '2 yrs'}</dd>
            </div>
            <div>
              <dt className="eyebrow text-gold">Language</dt>
              <dd className="mt-2 font-serif text-2xl font-semibold text-white">English</dd>
            </div>
            <div>
              <dt className="eyebrow text-gold">Support</dt>
              <dd className="mt-2 font-serif text-2xl font-semibold text-white">Funded</dd>
            </div>
          </dl>
        </div>

        {/* Programme card — slightly offset to create diagonal composition */}
        <div className="relative md:mt-10">
          <div className="relative rounded-3xl border border-white/15 bg-deep-forest/70 p-8 shadow-[0_30px_80px_-30px_rgba(0,0,0,0.6)] backdrop-blur-md ring-1 ring-white/5">
            <Eyebrow tone="gold">Current offer</Eyebrow>
            <h3 className="display mt-4 text-[1.75rem] text-white">
              {msc?.name ?? 'MSc in Physics'}
            </h3>
            <p className="mt-4 font-serif text-[15px] leading-relaxed text-white/80">
              {msc?.summary ??
                'A two-year, English-language master\'s programme at the University of Rwanda, jointly delivered with ICTP.'}
            </p>
            <dl className="mt-7 grid grid-cols-2 gap-y-4 font-serif text-[15px]">
              <div>
                <dt className="eyebrow text-white/60">Apply by</dt>
                <dd className="mt-1 text-white">{msc?.applicationWindow || 'Opens each spring'}</dd>
              </div>
              <div>
                <dt className="eyebrow text-white/60">Where</dt>
                <dd className="mt-1 text-white">Kigali</dd>
              </div>
            </dl>
            <Link
              href="/academics/msc-physics"
              className="mt-8 inline-flex items-center gap-2 rounded-full bg-gold px-6 py-3 font-sans text-sm font-bold tracking-wide text-deep-forest transition-all hover:bg-[#e4b84f] hover:shadow-[0_10px_30px_-10px_rgba(212,168,67,0.6)]"
            >
              Programme details →
            </Link>
          </div>
        </div>
      </div>
    </section>
  );
}
