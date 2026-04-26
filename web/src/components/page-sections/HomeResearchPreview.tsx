import Link from 'next/link';
import { Eyebrow } from '@/components/brand/Eyebrow';
import { GoldBar } from '@/components/brand/GoldBar';
import { DotGrid } from '@/components/brand/DotGrid';
import { ResearchCard } from '@/components/content/ResearchCard';
import { listResearchAreas } from '@/lib/content';

export async function HomeResearchPreview() {
  const areas = await listResearchAreas();

  return (
    <section className="panel relative isolate overflow-hidden bg-warm">
      <div className="grain" />
      <DotGrid cols={5} rows={5} tone="teal" className="absolute right-10 top-10 opacity-70" />

      <div className="mx-auto max-w-7xl px-6 py-24 md:px-10 md:py-32">
        <div className="mb-14 grid gap-10 md:grid-cols-[1fr_1.2fr] md:items-end md:gap-20">
          <div>
            <Eyebrow>Collaborative</Eyebrow>
            <h2 className="display mt-6 text-[3rem] text-ink md:text-[3.75rem]">Research</h2>
            <GoldBar className="mt-6" />
          </div>
          <p className="font-serif text-[17px] leading-[1.65] text-mid md:pb-3">
            A vibrant hub for local and visiting scientists, with a focus on international
            collaborations across two complementary programmes.
          </p>
        </div>

        {areas.length > 0 ? (
          <div
            className={
              areas.length === 1
                ? 'mx-auto max-w-xl'
                : areas.length === 2
                  ? 'mx-auto grid max-w-5xl gap-7 md:grid-cols-2'
                  : 'grid gap-7 md:grid-cols-3'
            }
          >
            {areas.map((area) => (
              <ResearchCard key={area.slug} area={area} />
            ))}
          </div>
        ) : (
          <p className="font-serif text-[15px] italic text-mid">Research areas coming soon.</p>
        )}

        <div className="mt-14 flex justify-center md:justify-start">
          <Link
            href="/research"
            className="group inline-flex items-center gap-3 rounded-full bg-teal px-7 py-3.5 font-sans text-sm font-bold tracking-wide text-white transition-all hover:bg-[#0b6e6e] hover:shadow-[0_10px_30px_-10px_rgba(13,128,128,0.6)]"
          >
            Learn more
            <span className="transition-transform group-hover:translate-x-0.5">→</span>
          </Link>
        </div>
      </div>
    </section>
  );
}
