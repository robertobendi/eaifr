import type { Metadata } from 'next';
import { PageHero } from '@/components/page-sections/PageHero';
import { SectionPanel, SectionInner } from '@/components/layout/SectionPanel';
import { ResearchCard } from '@/components/content/ResearchCard';
import { listResearchAreas } from '@/lib/content';

export const revalidate = 60;

export const metadata: Metadata = {
  title: 'Research',
  description:
    'EAIFR\'s three research programmes: condensed matter physics, physics of the solid earth, and high energy, cosmology & astroparticle physics.',
};

export default async function ResearchIndexPage() {
  const areas = await listResearchAreas();

  return (
    <>
      <PageHero
        eyebrow="Research"
        title={<>Frontier science rooted in Africa.</>}
        summary="EAIFR's researchers work at the frontiers of condensed matter, solid-earth and high-energy physics — pairing African questions and collaborators with international networks."
        tone="cream"
      />
      <SectionPanel bg="warm" panel={false}>
        <SectionInner>
          {areas.length > 0 ? (
            <div className="grid gap-6 md:grid-cols-3">
              {areas.map((a) => (
                <ResearchCard key={a.slug} area={a} />
              ))}
            </div>
          ) : (
            <p className="font-serif text-[15px] italic text-mid">Research areas coming soon.</p>
          )}
        </SectionInner>
      </SectionPanel>
    </>
  );
}
