import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import Link from 'next/link';
import { PageHero } from '@/components/page-sections/PageHero';
import { SectionPanel, SectionInner } from '@/components/layout/SectionPanel';
import { Prose } from '@/components/ui/Prose';
import { getProgramme, listProgrammes } from '@/lib/content';

export const revalidate = 60;

export async function generateStaticParams() {
  const programmes = await listProgrammes().catch(() => []);
  return programmes.map((p) => ({ slug: p.slug }));
}

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }): Promise<Metadata> {
  const { slug } = await params;
  const p = await getProgramme(slug);
  return {
    title: p?.name ?? 'Programme',
    description: p?.summary,
  };
}

export default async function ProgrammePage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const p = await getProgramme(slug);
  if (!p) notFound();

  return (
    <>
      <PageHero
        eyebrow="Programme"
        title={p.name}
        summary={p.summary}
        tone="forest"
      />
      <SectionPanel bg="white" panel={false}>
        <SectionInner size="sm">
          <dl className="mb-12 grid gap-8 rounded-2xl border border-ink/10 bg-cream p-8 md:grid-cols-3">
            {p.duration && (
              <div>
                <dt className="eyebrow">Duration</dt>
                <dd className="mt-2 font-serif text-lg text-ink">{p.duration}</dd>
              </div>
            )}
            {p.applicationWindow && (
              <div>
                <dt className="eyebrow">Application window</dt>
                <dd className="mt-2 font-serif text-lg text-ink">{p.applicationWindow}</dd>
              </div>
            )}
            <div>
              <dt className="eyebrow">How to apply</dt>
              <dd className="mt-2 font-serif text-lg text-ink">
                {p.applyUrl ? (
                  <Link
                    href={p.applyUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="text-bright-teal underline underline-offset-2 hover:text-teal"
                  >
                    Visit the portal →
                  </Link>
                ) : (
                  <Link href="/apply" className="text-bright-teal underline underline-offset-2">
                    See how to apply
                  </Link>
                )}
              </dd>
            </div>
          </dl>
          <Prose source={p.body} />
        </SectionInner>
      </SectionPanel>
    </>
  );
}
