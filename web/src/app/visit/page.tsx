import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { PageHero } from '@/components/page-sections/PageHero';
import { SectionPanel, SectionInner } from '@/components/layout/SectionPanel';
import { Prose } from '@/components/ui/Prose';
import { getPage } from '@/lib/content';

export const revalidate = 60;

export async function generateMetadata(): Promise<Metadata> {
  const page = await getPage('visit');
  return {
    title: page?.title ?? 'Visit us',
    description: page?.summary,
  };
}

export default async function VisitPage() {
  const page = await getPage('visit');
  if (!page) notFound();

  return (
    <>
      <PageHero
        eyebrow={page.eyebrow || 'Getting here'}
        title={page.title}
        summary={page.summary}
        tone="cream"
      />
      <SectionPanel bg="white" panel={false}>
        <SectionInner size="sm">
          <div className="grid gap-12 md:grid-cols-[1.2fr_1fr]">
            <Prose source={page.body} />
            <aside className="rounded-2xl border border-ink/10 bg-cream p-6">
              <h3 className="font-serif text-lg font-bold text-ink">Directly to EAIFR</h3>
              <p className="mt-3 font-serif text-[15px] leading-relaxed text-mid">
                University of Rwanda — College of Science and Technology<br />
                Avenue de l&rsquo;Armée, Kigali, Rwanda
              </p>
              <p className="mt-4 font-serif text-[15px] text-mid">
                <a
                  href="https://www.google.com/maps/place/University+of+Rwanda"
                  target="_blank"
                  rel="noopener noreferrer"
                  className="text-bright-teal underline underline-offset-2 hover:text-teal"
                >
                  Open in Google Maps →
                </a>
              </p>
            </aside>
          </div>
        </SectionInner>
      </SectionPanel>
    </>
  );
}
