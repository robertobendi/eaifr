import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import Link from 'next/link';
import { PageHero } from '@/components/page-sections/PageHero';
import { SectionPanel, SectionInner } from '@/components/layout/SectionPanel';
import { Prose } from '@/components/ui/Prose';
import { Eyebrow } from '@/components/brand/Eyebrow';
import { getResearchArea, listResearchAreas } from '@/lib/content';

export const revalidate = 60;

export async function generateStaticParams() {
  const areas = await listResearchAreas().catch(() => []);
  return areas.map((a) => ({ slug: a.slug }));
}

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }): Promise<Metadata> {
  const { slug } = await params;
  const area = await getResearchArea(slug);
  return {
    title: area?.name ?? 'Research',
    description: area?.short,
  };
}

const toneMap = {
  teal: 'cream',
  navy: 'navy',
  forest: 'forest',
  rust: 'cream',
} as const;

export default async function ResearchAreaPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const area = await getResearchArea(slug);
  if (!area) notFound();

  const others = (await listResearchAreas()).filter((a) => a.slug !== area.slug);

  return (
    <>
      <PageHero
        eyebrow="Research area"
        title={area.name}
        accent={area.short}
        tone={toneMap[area.accent] as 'cream' | 'navy' | 'forest'}
      />
      <SectionPanel bg="white" panel={false}>
        <SectionInner size="sm">
          <Prose source={area.overview} />
        </SectionInner>
      </SectionPanel>

      {others.length > 0 && (
        <SectionPanel bg="warm">
          <SectionInner>
            <Eyebrow>Other research areas</Eyebrow>
            <div className="mt-8 grid gap-6 md:grid-cols-2">
              {others.map((a) => (
                <Link
                  key={a.slug}
                  href={`/research/${a.slug}`}
                  className="group rounded-2xl border border-ink/10 bg-white p-6 transition-all hover:-translate-y-0.5 hover:border-teal/30 hover:shadow-md"
                >
                  <h3 className="font-serif text-lg font-bold text-ink group-hover:text-teal">
                    {a.name}
                  </h3>
                  <p className="mt-2 font-serif text-[15px] text-mid">{a.short}</p>
                </Link>
              ))}
            </div>
          </SectionInner>
        </SectionPanel>
      )}
    </>
  );
}
