import type { Metadata } from 'next';
import Link from 'next/link';
import { PageHero } from '@/components/page-sections/PageHero';
import { SectionPanel, SectionInner } from '@/components/layout/SectionPanel';
import { Prose } from '@/components/ui/Prose';
import { Eyebrow } from '@/components/brand/Eyebrow';
import { getPage, getProgramme } from '@/lib/content';

export const revalidate = 60;

export async function generateMetadata(): Promise<Metadata> {
  const page = await getPage('apply');
  return {
    title: page?.title ?? 'Apply',
    description: page?.summary,
  };
}

export default async function ApplyPage() {
  const [page, msc] = await Promise.all([getPage('apply'), getProgramme('msc-physics')]);

  return (
    <>
      <PageHero
        eyebrow={page?.eyebrow || 'Admissions'}
        title={page?.title || 'Apply to EAIFR.'}
        summary={page?.summary || 'Applications to the MSc in Physics are submitted through the ICTP application portal.'}
        tone="forest"
      />
      <SectionPanel bg="white" panel={false}>
        <SectionInner size="sm">
          <div className="mb-10 rounded-2xl border border-ink/10 bg-cream p-6">
            <Eyebrow>Open programme</Eyebrow>
            <h2 className="mt-3 font-serif text-2xl font-bold text-ink">
              {msc?.name || 'MSc in Physics'}
            </h2>
            <p className="mt-3 font-serif text-[15px] leading-relaxed text-mid">
              {msc?.summary ||
                'A two-year, English-language master\'s programme at the University of Rwanda, jointly delivered with ICTP.'}
            </p>
            <dl className="mt-5 grid grid-cols-2 gap-y-3 font-serif text-sm">
              <dt className="eyebrow text-mid">Duration</dt>
              <dd className="text-ink">{msc?.duration || '2 years'}</dd>
              <dt className="eyebrow text-mid">Application window</dt>
              <dd className="text-ink">{msc?.applicationWindow || 'Opens each spring'}</dd>
            </dl>
            <Link
              href={msc?.applyUrl || 'https://www.ictp.it/training/degree-programs'}
              target="_blank"
              rel="noopener noreferrer"
              className="mt-6 inline-flex items-center gap-2 rounded-full bg-teal px-5 py-2.5 font-sans text-sm font-bold tracking-wide text-white hover:bg-[#0b6e6e]"
            >
              Go to application portal →
            </Link>
          </div>
          {page?.body && <Prose source={page.body} />}
        </SectionInner>
      </SectionPanel>
    </>
  );
}
