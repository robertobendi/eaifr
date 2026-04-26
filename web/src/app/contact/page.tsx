import type { Metadata } from 'next';
import { PageHero } from '@/components/page-sections/PageHero';
import { SectionPanel, SectionInner } from '@/components/layout/SectionPanel';
import { Eyebrow } from '@/components/brand/Eyebrow';

export const metadata: Metadata = {
  title: 'Contact',
  description: 'Contact the East African Institute for Fundamental Research.',
};

export default function ContactPage() {
  return (
    <>
      <PageHero
        eyebrow="Contact us"
        title="Get in touch."
        summary="General inquiries, media requests, and partnerships can reach us by email; students applying to the MSc in Physics should use the ICTP application portal."
        tone="navy"
      />
      <SectionPanel bg="white" panel={false}>
        <SectionInner size="md">
          <div className="grid gap-10 md:grid-cols-2">
            <div>
              <Eyebrow>By email</Eyebrow>
              <p className="mt-4 font-serif text-2xl font-bold text-ink">
                <a href="mailto:info@eaifr.org" className="text-ink hover:text-teal">
                  info@eaifr.org
                </a>
              </p>
              <p className="mt-3 font-serif text-[15px] leading-relaxed text-mid">
                We aim to respond within two working days.
              </p>

              <div className="mt-10">
                <Eyebrow>Address</Eyebrow>
                <p className="mt-4 font-serif text-[15px] leading-relaxed text-mid">
                  East African Institute for Fundamental Research<br />
                  University of Rwanda · College of Science and Technology<br />
                  Avenue de l&rsquo;Armée, Kigali, Rwanda
                </p>
              </div>
            </div>

            <aside className="rounded-2xl border border-ink/10 bg-cream p-6">
              <Eyebrow>Contact form</Eyebrow>
              <p className="mt-4 font-serif text-[15px] leading-relaxed text-mid">
                A self-service contact form is on its way. In the meantime, the fastest way to
                reach the institute is directly by email at{' '}
                <a href="mailto:info@eaifr.org" className="text-bright-teal underline underline-offset-2 hover:text-teal">
                  info@eaifr.org
                </a>.
              </p>
            </aside>
          </div>
        </SectionInner>
      </SectionPanel>
    </>
  );
}
