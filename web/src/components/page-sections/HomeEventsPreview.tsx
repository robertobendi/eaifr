import Image from 'next/image';
import Link from 'next/link';
import { Eyebrow } from '@/components/brand/Eyebrow';
import { GoldBar } from '@/components/brand/GoldBar';
import { EventCard } from '@/components/content/EventCard';
import { NewsCard } from '@/components/content/NewsCard';
import { listNews, listUpcomingEvents } from '@/lib/content';

export async function HomeEventsAndNews() {
  const [events, news] = await Promise.all([
    listUpcomingEvents(3),
    listNews(3),
  ]);

  return (
    <section className="panel relative isolate overflow-hidden bg-warm">
      {/* Faint brochure "events" image along the top edge */}
      <div className="pointer-events-none absolute inset-x-0 top-0 h-72 opacity-[0.12] [mask-image:linear-gradient(to_bottom,#000,transparent)]">
        <Image src="/img/events.jpg" alt="" fill sizes="100vw" className="object-cover" style={{ mixBlendMode: 'multiply' }} />
      </div>
      <div className="grain" />

      <div className="relative mx-auto max-w-7xl px-6 py-24 md:px-10 md:py-32">
        <div className="grid gap-16 lg:grid-cols-2 lg:gap-16">
          <div>
            <Eyebrow>Upcoming events</Eyebrow>
            <h2 className="display mt-5 text-[2rem] text-ink md:text-[2.5rem]">
              Conferences, schools, workshops.
            </h2>
            <GoldBar className="mt-4" />
            <div className="mt-9 flex flex-col gap-4">
              {events.length > 0 ? (
                events.map((e) => <EventCard key={e.slug} event={e} compact />)
              ) : (
                <p className="rounded-2xl border border-dashed border-ink/15 bg-white/60 p-8 font-serif text-[15px] italic text-mid">
                  No upcoming events scheduled at the moment.
                </p>
              )}
            </div>
            <Link
              href="/events"
              className="mt-7 inline-flex font-sans text-xs font-bold uppercase tracking-[0.22em] text-teal hover:underline hover:underline-offset-4"
            >
              All events →
            </Link>
          </div>

          <div>
            <Eyebrow>News &amp; highlights</Eyebrow>
            <h2 className="display mt-5 text-[2rem] text-ink md:text-[2.5rem]">
              What&rsquo;s happening at EAIFR.
            </h2>
            <GoldBar className="mt-4" />
            <div className="mt-9 flex flex-col gap-4">
              {news.length > 0 ? (
                news.map((n) => <NewsCard key={n.slug} item={n} />)
              ) : (
                <p className="rounded-2xl border border-dashed border-ink/15 bg-white/60 p-8 font-serif text-[15px] italic text-mid">
                  No news yet — check back soon.
                </p>
              )}
            </div>
            <Link
              href="/news"
              className="mt-7 inline-flex font-sans text-xs font-bold uppercase tracking-[0.22em] text-teal hover:underline hover:underline-offset-4"
            >
              All news →
            </Link>
          </div>
        </div>
      </div>
    </section>
  );
}
