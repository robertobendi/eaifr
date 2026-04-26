import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { PageHero } from '@/components/page-sections/PageHero';
import { SectionPanel, SectionInner } from '@/components/layout/SectionPanel';
import { Prose } from '@/components/ui/Prose';
import { getPage } from '@/lib/content';

export const revalidate = 60;

export async function generateMetadata(): Promise<Metadata> {
  const page = await getPage('about');
  return {
    title: page?.title ?? 'About',
    description: page?.summary,
  };
}

export default async function AboutPage() {
  const page = await getPage('about');
  if (!page) notFound();

  return (
    <>
      <PageHero
        eyebrow={page.eyebrow || 'About us'}
        title={page.title}
        summary={page.summary}
        tone="cream"
      />
      <SectionPanel bg="white" panel={false}>
        <SectionInner size="sm">
          <Prose source={page.body} />
        </SectionInner>
      </SectionPanel>
    </>
  );
}
