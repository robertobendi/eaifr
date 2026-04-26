import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { PageHero } from '@/components/page-sections/PageHero';
import { SectionPanel, SectionInner } from '@/components/layout/SectionPanel';
import { Prose } from '@/components/ui/Prose';
import { getNewsItem, listNews } from '@/lib/content';

export const revalidate = 60;

export async function generateStaticParams() {
  const news = await listNews(100).catch(() => []);
  return news.map((n) => ({ slug: n.slug }));
}

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }): Promise<Metadata> {
  const { slug } = await params;
  const n = await getNewsItem(slug);
  return {
    title: n?.title ?? 'News',
    description: n?.excerpt,
  };
}

export default async function NewsDetailPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const n = await getNewsItem(slug);
  if (!n) notFound();

  const date = n.publishedAt
    ? new Date(n.publishedAt).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' })
    : '';

  return (
    <>
      <PageHero
        eyebrow={date ? `News · ${date}` : 'News'}
        title={n.title}
        summary={n.excerpt}
        tone="cream"
      />
      <SectionPanel bg="white" panel={false}>
        <SectionInner size="sm">
          <Prose source={n.body} />
        </SectionInner>
      </SectionPanel>
    </>
  );
}
