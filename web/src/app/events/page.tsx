import type { Metadata } from 'next';
import { PageHero } from '@/components/page-sections/PageHero';
import { SectionPanel, SectionInner } from '@/components/layout/SectionPanel';
import { EventCard } from '@/components/content/EventCard';
import { Eyebrow } from '@/components/brand/Eyebrow';
import { listEvents } from '@/lib/content';

export const revalidate = 60;

export const metadata: Metadata = {
  title: 'Events',
  description: 'Schools, conferences, workshops and seminars at EAIFR.',
};

export default async function EventsPage() {
  const events = await listEvents();
  const today = new Date().toISOString().slice(0, 10);
  const upcoming = events.filter((e) => !e.startDate || e.startDate >= today);
  const past = events.filter((e) => e.startDate && e.startDate < today);

  return (
    <>
      <PageHero
        eyebrow="Events"
        title="Schools, conferences, workshops."
        summary="EAIFR hosts and co-organises scientific events that bring together researchers and students across Africa and the world."
        tone="cream"
      />
      <SectionPanel bg="warm" panel={false}>
        <SectionInner>
          <Eyebrow>Upcoming</Eyebrow>
          <div className="mt-6">
            {upcoming.length > 0 ? (
              <div className="grid gap-5 md:grid-cols-2">
                {upcoming.map((e) => (
                  <EventCard key={e.slug} event={e} />
                ))}
              </div>
            ) : (
              <p className="font-serif text-[15px] italic text-mid">
                No upcoming events announced.
              </p>
            )}
          </div>

          {past.length > 0 && (
            <>
              <hr className="my-16 border-ink/10" />
              <Eyebrow tone="light">Past events</Eyebrow>
              <div className="mt-6 grid gap-5 md:grid-cols-2">
                {past.slice(0, 20).map((e) => (
                  <EventCard key={e.slug} event={e} />
                ))}
              </div>
            </>
          )}
        </SectionInner>
      </SectionPanel>
    </>
  );
}
