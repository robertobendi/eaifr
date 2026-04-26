import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import Link from 'next/link';
import { PageHero } from '@/components/page-sections/PageHero';
import { SectionPanel, SectionInner } from '@/components/layout/SectionPanel';
import { Prose } from '@/components/ui/Prose';
import { getEvent, listEvents } from '@/lib/content';

export const revalidate = 60;

export async function generateStaticParams() {
  const events = await listEvents().catch(() => []);
  return events.map((e) => ({ slug: e.slug }));
}

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }): Promise<Metadata> {
  const { slug } = await params;
  const e = await getEvent(slug);
  return {
    title: e?.title ?? 'Event',
    description: e?.summary,
  };
}

function fmt(date: string | null) {
  if (!date) return null;
  return new Date(date).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
}

export default async function EventPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const e = await getEvent(slug);
  if (!e) notFound();

  return (
    <>
      <PageHero
        eyebrow={e.kind}
        title={e.title}
        summary={e.summary}
        tone="cream"
      />
      <SectionPanel bg="white" panel={false}>
        <SectionInner size="sm">
          <dl className="mb-10 grid gap-6 rounded-2xl border border-ink/10 bg-cream p-6 md:grid-cols-3">
            {e.startDate && (
              <div>
                <dt className="eyebrow">Starts</dt>
                <dd className="mt-2 font-serif text-base text-ink">{fmt(e.startDate)}</dd>
              </div>
            )}
            {e.endDate && (
              <div>
                <dt className="eyebrow">Ends</dt>
                <dd className="mt-2 font-serif text-base text-ink">{fmt(e.endDate)}</dd>
              </div>
            )}
            {e.location && (
              <div>
                <dt className="eyebrow">Location</dt>
                <dd className="mt-2 font-serif text-base text-ink">{e.location}</dd>
              </div>
            )}
          </dl>

          {e.registrationUrl && (
            <Link
              href={e.registrationUrl}
              target="_blank"
              rel="noopener noreferrer"
              className="mb-10 inline-flex items-center gap-2 rounded-full bg-teal px-6 py-3 font-sans text-sm font-bold tracking-wide text-white hover:bg-[#0b6e6e]"
            >
              Register →
            </Link>
          )}

          <Prose source={e.body} />
        </SectionInner>
      </SectionPanel>
    </>
  );
}
