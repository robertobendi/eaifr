import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { PageHero } from '@/components/page-sections/PageHero';
import { SectionPanel, SectionInner } from '@/components/layout/SectionPanel';
import { Prose } from '@/components/ui/Prose';
import { mediaUrl } from '@/lib/cms';
import { getPerson, listPeople } from '@/lib/content';

export const revalidate = 60;

export async function generateStaticParams() {
  const people = await listPeople().catch(() => []);
  return people.map((p) => ({ slug: p.slug }));
}

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }): Promise<Metadata> {
  const { slug } = await params;
  const p = await getPerson(slug);
  return {
    title: p?.fullName ?? 'Person',
    description: p?.title || p?.affiliation || '',
  };
}

export default async function PersonPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const person = await getPerson(slug);
  if (!person) notFound();

  const photo = mediaUrl(person.photoPath);

  return (
    <>
      <PageHero
        eyebrow={person.title || 'Member'}
        title={person.fullName}
        summary={person.affiliation}
        tone="cream"
      />
      <SectionPanel bg="white" panel={false}>
        <SectionInner size="md">
          <div className="grid gap-12 md:grid-cols-[1fr_2fr]">
            <div className="rounded-2xl border border-ink/10 bg-cream p-4">
              {photo ? (
                // eslint-disable-next-line @next/next/no-img-element
                <img src={photo} alt={person.fullName} className="w-full rounded-xl object-cover" />
              ) : (
                <div className="flex aspect-square w-full items-center justify-center rounded-xl bg-warm font-serif text-6xl text-light-ink">
                  {person.fullName
                    .split(' ')
                    .slice(0, 2)
                    .map((w) => w[0])
                    .join('')}
                </div>
              )}
              {person.email && (
                <p className="mt-5 break-all font-serif text-[14px] text-mid">
                  <a href={`mailto:${person.email}`} className="text-bright-teal underline underline-offset-2 hover:text-teal">
                    {person.email}
                  </a>
                </p>
              )}
            </div>
            <div>
              <Prose source={person.bio} />
            </div>
          </div>
        </SectionInner>
      </SectionPanel>
    </>
  );
}
