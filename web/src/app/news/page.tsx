import type { Metadata } from 'next';
import { PageHero } from '@/components/page-sections/PageHero';
import { SectionPanel, SectionInner } from '@/components/layout/SectionPanel';
import { NewsCard } from '@/components/content/NewsCard';
import { listNews } from '@/lib/content';

export const revalidate = 60;

export const metadata: Metadata = {
  title: 'News',
  description: 'Announcements, research highlights and admissions news from EAIFR.',
};

export default async function NewsIndexPage() {
  const news = await listNews(50);

  return (
    <>
      <PageHero
        eyebrow="News & highlights"
        title="What's happening at EAIFR."
        summary="Admissions calls, research highlights and announcements from across our community."
        tone="cream"
      />
      <SectionPanel bg="warm" panel={false}>
        <SectionInner>
          {news.length > 0 ? (
            <div className="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
              {news.map((n) => (
                <NewsCard key={n.slug} item={n} />
              ))}
            </div>
          ) : (
            <p className="font-serif text-[15px] italic text-mid">No news posted yet.</p>
          )}
        </SectionInner>
      </SectionPanel>
    </>
  );
}
