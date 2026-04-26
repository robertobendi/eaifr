import type { Metadata } from 'next';
import Link from 'next/link';
import { PageHero } from '@/components/page-sections/PageHero';
import { SectionPanel, SectionInner } from '@/components/layout/SectionPanel';
import { Eyebrow } from '@/components/brand/Eyebrow';
import { listProgrammes } from '@/lib/content';

export const revalidate = 60;

export const metadata: Metadata = {
  title: 'Academics',
  description: "EAIFR's graduate programmes at the University of Rwanda, jointly delivered with ICTP.",
};

export default async function AcademicsPage() {
  const programmes = await listProgrammes();

  return (
    <>
      <PageHero
        eyebrow="Academics"
        title="Graduate programmes at EAIFR."
        summary="Fully funded, English-language graduate study in fundamental physics at the University of Rwanda, with scientific oversight by ICTP."
        tone="forest"
      />
      <SectionPanel bg="warm" panel={false}>
        <SectionInner>
          {programmes.length === 0 ? (
            <p className="font-serif text-[15px] italic text-mid">
              Programme listings are being set up. In the meantime, the MSc in Physics remains
              EAIFR&rsquo;s flagship offer.
            </p>
          ) : (
            <div className="grid gap-6 md:grid-cols-2">
              {programmes.map((p) => (
                <Link
                  key={p.slug}
                  href={`/academics/${p.slug}`}
                  className="group flex flex-col rounded-2xl border border-ink/10 bg-white p-8 transition-all hover:-translate-y-0.5 hover:border-teal/30 hover:shadow-md"
                >
                  <Eyebrow>Programme</Eyebrow>
                  <h3 className="mt-3 font-serif text-2xl font-bold text-ink group-hover:text-teal">
                    {p.name}
                  </h3>
                  <p className="mt-4 font-serif text-[15px] leading-relaxed text-mid">{p.summary}</p>
                  <dl className="mt-6 grid grid-cols-2 gap-y-3 font-serif text-sm">
                    {p.duration && (
                      <>
                        <dt className="eyebrow text-mid">Duration</dt>
                        <dd className="text-ink">{p.duration}</dd>
                      </>
                    )}
                    {p.applicationWindow && (
                      <>
                        <dt className="eyebrow text-mid">Apply</dt>
                        <dd className="text-ink">{p.applicationWindow}</dd>
                      </>
                    )}
                  </dl>
                  <span className="mt-auto pt-6 font-sans text-xs font-bold uppercase tracking-[0.2em] text-teal group-hover:underline">
                    View details →
                  </span>
                </Link>
              ))}
            </div>
          )}
        </SectionInner>
      </SectionPanel>
    </>
  );
}
