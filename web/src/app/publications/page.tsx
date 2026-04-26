import type { Metadata } from 'next';
import Link from 'next/link';
import { PageHero } from '@/components/page-sections/PageHero';
import { SectionPanel, SectionInner } from '@/components/layout/SectionPanel';
import { Eyebrow } from '@/components/brand/Eyebrow';
import { listPublications } from '@/lib/content';

export const revalidate = 60;

export const metadata: Metadata = {
  title: 'Publications',
  description: 'Peer-reviewed publications by EAIFR researchers.',
};

export default async function PublicationsPage() {
  const pubs = await listPublications();

  const byYear = new Map<number | string, typeof pubs>();
  for (const p of pubs) {
    const key = p.year ?? 'Undated';
    if (!byYear.has(key)) byYear.set(key, []);
    byYear.get(key)!.push(p);
  }
  const years = Array.from(byYear.keys()).sort((a, b) => {
    if (typeof a === 'number' && typeof b === 'number') return b - a;
    if (typeof a === 'number') return -1;
    if (typeof b === 'number') return 1;
    return 0;
  });

  return (
    <>
      <PageHero
        eyebrow="Publications"
        title="Research output."
        summary="A growing bibliography of work from EAIFR's faculty, students and collaborators."
        tone="cream"
      />
      <SectionPanel bg="warm" panel={false}>
        <SectionInner size="md">
          {pubs.length === 0 ? (
            <p className="font-serif text-[15px] italic text-mid">
              Publications are being catalogued. Check back soon.
            </p>
          ) : (
            years.map((year) => (
              <div key={String(year)} className="mb-12">
                <Eyebrow>{String(year)}</Eyebrow>
                <ul className="mt-5 divide-y divide-ink/10 rounded-2xl border border-ink/10 bg-white">
                  {byYear.get(year)!.map((p) => {
                    const link = p.url || (p.doi ? `https://doi.org/${p.doi}` : null);
                    return (
                      <li key={p.slug} className="p-6">
                        {link ? (
                          <Link
                            href={link}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="font-serif text-lg font-bold text-ink hover:text-teal"
                          >
                            {p.title}
                          </Link>
                        ) : (
                          <span className="font-serif text-lg font-bold text-ink">{p.title}</span>
                        )}
                        {p.authors && (
                          <p className="mt-2 font-serif text-[15px] text-mid">{p.authors}</p>
                        )}
                        {p.venue && (
                          <p className="mt-1 font-serif text-[14px] italic text-light-ink">{p.venue}</p>
                        )}
                      </li>
                    );
                  })}
                </ul>
              </div>
            ))
          )}
        </SectionInner>
      </SectionPanel>
    </>
  );
}
