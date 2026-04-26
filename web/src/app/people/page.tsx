import type { Metadata } from 'next';
import Link from 'next/link';
import { PageHero } from '@/components/page-sections/PageHero';
import { SectionPanel, SectionInner } from '@/components/layout/SectionPanel';
import { Eyebrow } from '@/components/brand/Eyebrow';
import { listPeople, type Person } from '@/lib/content';
import { mediaUrl } from '@/lib/cms';

export const revalidate = 60;

export const metadata: Metadata = {
  title: 'People',
  description: 'Faculty, researchers, students and staff at EAIFR.',
};

const ROLE_ORDER: Person['role'][] = ['director', 'faculty', 'postdoc', 'phd-student', 'msc-student', 'staff', 'affiliate'];

const ROLE_LABEL: Record<Person['role'], string> = {
  director: 'Director',
  faculty: 'Faculty',
  postdoc: 'Postdoctoral researchers',
  'phd-student': 'PhD students',
  'msc-student': 'MSc students',
  staff: 'Staff',
  affiliate: 'Affiliates',
};

function PersonCard({ person }: { person: Person }) {
  const photo = mediaUrl(person.photoPath);
  return (
    <Link
      href={`/people/${person.slug}`}
      className="group flex flex-col rounded-2xl border border-ink/10 bg-white p-6 transition-all hover:-translate-y-0.5 hover:border-teal/30 hover:shadow-md"
    >
      <div className="aspect-square w-full overflow-hidden rounded-xl bg-cream">
        {photo ? (
          // eslint-disable-next-line @next/next/no-img-element
          <img src={photo} alt={person.fullName} className="h-full w-full object-cover" />
        ) : (
          <div className="flex h-full items-center justify-center font-serif text-4xl text-light-ink">
            {person.fullName
              .split(' ')
              .slice(0, 2)
              .map((w) => w[0])
              .join('')}
          </div>
        )}
      </div>
      <h3 className="mt-5 font-serif text-lg font-bold text-ink group-hover:text-teal">
        {person.fullName}
      </h3>
      {person.title && <p className="mt-1 font-serif text-[14px] text-mid">{person.title}</p>}
      {person.affiliation && (
        <p className="mt-1 font-sans text-xs font-bold uppercase tracking-[0.14em] text-light-ink">
          {person.affiliation}
        </p>
      )}
    </Link>
  );
}

export default async function PeoplePage() {
  const people = await listPeople();
  const byRole = new Map<Person['role'], Person[]>();
  for (const p of people) {
    if (!byRole.has(p.role)) byRole.set(p.role, []);
    byRole.get(p.role)!.push(p);
  }

  return (
    <>
      <PageHero
        eyebrow="People"
        title="Our community."
        summary="The faculty, researchers, students and staff who make EAIFR."
        tone="cream"
      />
      <SectionPanel bg="warm" panel={false}>
        <SectionInner>
          {people.length === 0 ? (
            <p className="font-serif text-[15px] italic text-mid">Directory coming soon.</p>
          ) : (
            ROLE_ORDER.filter((r) => byRole.has(r)).map((role) => (
              <div key={role} className="mb-14">
                <Eyebrow>{ROLE_LABEL[role]}</Eyebrow>
                <div className="mt-6 grid gap-5 md:grid-cols-3 lg:grid-cols-4">
                  {byRole.get(role)!.map((p) => (
                    <PersonCard key={p.slug} person={p} />
                  ))}
                </div>
              </div>
            ))
          )}
        </SectionInner>
      </SectionPanel>
    </>
  );
}
